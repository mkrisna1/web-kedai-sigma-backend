<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PaymentGatewayService
{
    public function isConfigured(): bool
    {
        return $this->isXenditConfigured() || $this->isMidtransConfigured();
    }

    public function createGoPayPayment(Pesanan $pesanan): Pesanan
    {
        if ($this->isXenditConfigured()) {
            return $this->createXenditQrisPayment($pesanan);
        }

        if (! $this->isMidtransConfigured()) {
            return $this->createStaticQrisPayment($pesanan);
        }

        $pesanan->loadMissing(['detail_pesanans.produk', 'meja']);

        $reference = $this->buildReference($pesanan);
        $expiresAt = now()->addMinutes(max($this->expiryMinutes(), 1));
        try {
            $response = $this->midtransRequest()
                ->post($this->apiBaseUrl().'/v2/charge', [
                'payment_type' => 'gopay',
                'transaction_details' => [
                    'order_id' => $reference,
                    'gross_amount' => (int) round((float) $pesanan->total_harga),
                ],
                'item_details' => $this->buildItemDetails($pesanan),
                'gopay' => [
                    'enable_callback' => true,
                    'callback_url' => config('services.midtrans.finish_url'),
                ],
                'custom_expiry' => [
                    'order_time' => now()->format('Y-m-d H:i:s O'),
                    'expiry_duration' => max($this->expiryMinutes(), 1),
                    'unit' => 'minute',
                ],
            ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'metode_pembayaran' => 'Gateway GoPay/Midtrans tidak bisa dihubungi.',
            ]);
        }

        if (! $response->successful()) {
            $message = $response->json('status_message')
                ?: $response->json('message')
                ?: 'Transaksi GoPay belum bisa dibuat.';

            throw ValidationException::withMessages([
                'metode_pembayaran' => $message,
            ]);
        }

        $payload = $response->json();
        $updates = [
            'status_pembayaran' => 'belum_bayar',
            'metode_pembayaran' => 'qris',
            'payment_provider' => 'midtrans_gopay',
            'payment_reference' => $payload['order_id'] ?? $reference,
            'payment_transaction_id' => $payload['transaction_id'] ?? null,
            'payment_status' => $payload['transaction_status'] ?? 'pending',
            'payment_qr_url' => $this->findActionUrl($payload['actions'] ?? [], 'generate-qr-code'),
            'payment_deeplink_url' => $this->findActionUrl($payload['actions'] ?? [], 'deeplink-redirect'),
            'payment_expired_at' => $expiresAt,
            'payment_status_checked_at' => now(),
        ];

        $pesanan->update($this->onlyExistingPaymentColumns($updates));

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function createXenditQrisPayment(Pesanan $pesanan): Pesanan
    {
        $pesanan->loadMissing(['detail_pesanans.produk', 'meja']);

        $reference = $this->buildReference($pesanan);
        $expiresAt = now()->addMinutes(max($this->xenditExpiryMinutes(), 1));

        try {
            $response = $this->xenditRequest()
                ->asForm()
                ->post('https://api.xendit.co/qr_codes', [
                    'external_id' => $reference,
                    'type' => 'DYNAMIC',
                    'amount' => (int) round((float) $pesanan->total_harga),
                    'callback_url' => $this->xenditCallbackUrl(),
                    'description' => 'Kedai Sigma '.$pesanan->getKey(),
                ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'metode_pembayaran' => 'Gateway Xendit tidak bisa dihubungi.',
            ]);
        }

        if (! $response->successful()) {
            $validationErrors = collect($response->json('errors') ?? [])
                ->pluck('message')
                ->filter()
                ->implode(' ');
            $message = $response->json('message')
                ?: $response->json('error_code')
                ?: 'Transaksi QRIS Xendit belum bisa dibuat.';

            throw ValidationException::withMessages([
                'metode_pembayaran' => trim($message.' '.$validationErrors),
            ]);
        }

        $payload = $response->json();
        $qrString = $payload['qr_string'] ?? null;
        $updates = [
            'status_pembayaran' => 'belum_bayar',
            'metode_pembayaran' => 'qris',
            'payment_provider' => 'xendit_qris',
            'payment_reference' => $payload['external_id'] ?? $reference,
            'payment_transaction_id' => $payload['id'] ?? null,
            'payment_status' => strtolower((string) ($payload['status'] ?? 'pending')),
            'payment_qr_url' => $qrString ? $this->buildQrImageUrl($qrString) : null,
            'payment_expired_at' => $expiresAt,
            'payment_status_checked_at' => now(),
        ];

        $pesanan->update($this->onlyExistingPaymentColumns($updates));

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function createStaticQrisPayment(Pesanan $pesanan): Pesanan
    {
        $reference = $this->buildReference($pesanan);
        $updates = [
            'status_pembayaran' => 'belum_bayar',
            'metode_pembayaran' => 'qris',
            'payment_provider' => 'static_qris',
            'payment_reference' => $reference,
            'payment_status' => 'pending_manual',
            'payment_expired_at' => now()->addMinutes(max($this->expiryMinutes(), 10)),
            'payment_status_checked_at' => now(),
        ];

        $pesanan->update($this->onlyExistingPaymentColumns($updates));

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function syncPaymentStatus(Pesanan $pesanan): Pesanan
    {
        if (
            ! $this->isConfigured() ||
            ! $this->hasColumn('payment_reference') ||
            blank($pesanan->payment_reference)
        ) {
            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        }

        if (($pesanan->payment_provider ?? null) === 'xendit_qris') {
            return $this->syncXenditPaymentStatus($pesanan);
        }

        try {
            $response = $this->midtransRequest()
                ->get($this->apiBaseUrl().'/v2/'.rawurlencode($pesanan->payment_reference).'/status');
        } catch (ConnectionException) {
            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        }

        if ($response->successful()) {
            $this->applyMidtransStatus($pesanan, $response->json());
        }

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function handleXenditWebhook(array $payload): ?Pesanan
    {
        $data = $payload['data'] ?? $payload;
        $reference = $data['external_id']
            ?? $data['reference_id']
            ?? $data['qr_code']['external_id']
            ?? $data['qr_code']['reference_id']
            ?? null;
        $transactionId = $data['id'] ?? $data['payment_id'] ?? null;

        $pesanan = null;

        if ($reference && $this->hasColumn('payment_reference')) {
            $pesanan = Pesanan::query()->where('payment_reference', $reference)->first();
        }

        if (! $pesanan && $transactionId && $this->hasColumn('payment_transaction_id')) {
            $pesanan = Pesanan::query()->where('payment_transaction_id', $transactionId)->first();
        }

        if (! $pesanan) {
            return null;
        }

        $this->applyXenditStatus($pesanan, $payload);

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function handleNotification(array $payload): ?Pesanan
    {
        $orderId = $payload['order_id'] ?? null;

        if (! $orderId || ! $this->isValidSignature($payload)) {
            throw ValidationException::withMessages([
                'signature_key' => 'Signature Midtrans tidak valid.',
            ]);
        }

        $pesanan = $this->hasColumn('payment_reference')
            ? Pesanan::query()->where('payment_reference', $orderId)->first()
            : null;

        if (! $pesanan && preg_match('/^SIGMA-(\d+)-/', $orderId, $matches)) {
            $pesanan = Pesanan::find((int) $matches[1]);
        }

        if (! $pesanan) {
            return null;
        }

        $this->applyMidtransStatus($pesanan, $payload);

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    private function applyMidtransStatus(Pesanan $pesanan, array $payload): void
    {
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $isPaid = in_array($transactionStatus, ['settlement', 'capture'], true)
            && $fraudStatus !== 'deny';
        $isFailed = in_array($transactionStatus, ['expire', 'cancel', 'deny', 'failure'], true);

        $updates = [
            'payment_status' => $transactionStatus,
            'payment_status_checked_at' => now(),
        ];

        if ($this->hasColumn('payment_transaction_id') && isset($payload['transaction_id'])) {
            $updates['payment_transaction_id'] = $payload['transaction_id'];
        }

        if ($isPaid) {
            $updates['status_pembayaran'] = 'lunas';
        }

        if ($isFailed) {
            $updates['status_pembayaran'] = 'belum_bayar';
            $updates['status_pesanan'] = 'dibatalkan';
        }

        $pesanan->update($this->onlyExistingPaymentColumns($updates));
    }

    private function syncXenditPaymentStatus(Pesanan $pesanan): Pesanan
    {
        if (! $this->isXenditConfigured() || blank($pesanan->payment_transaction_id)) {
            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        }

        try {
            $response = $this->xenditRequest()
                ->get('https://api.xendit.co/qr_codes/'.rawurlencode($pesanan->payment_transaction_id));
        } catch (ConnectionException) {
            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        }

        if ($response->successful()) {
            $payload = $response->json();

            $pesanan->update($this->onlyExistingPaymentColumns([
                'payment_status' => strtolower((string) ($payload['status'] ?? $pesanan->payment_status)),
                'payment_status_checked_at' => now(),
            ]));
        }

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    private function applyXenditStatus(Pesanan $pesanan, array $payload): void
    {
        $data = $payload['data'] ?? $payload;
        $event = strtolower((string) ($payload['event'] ?? $payload['event_type'] ?? ''));
        $status = strtolower((string) ($data['status'] ?? $data['payment_status'] ?? ''));
        $isPaid = str_contains($event, 'succeeded') ||
            str_contains($event, 'paid') ||
            in_array($status, ['succeeded', 'completed', 'paid', 'settled'], true);
        $isFailed = str_contains($event, 'expired') ||
            str_contains($event, 'failed') ||
            in_array($status, ['expired', 'failed', 'cancelled', 'canceled'], true);

        $updates = [
            'payment_status' => $status ?: ($isPaid ? 'succeeded' : $pesanan->payment_status),
            'payment_status_checked_at' => now(),
        ];

        if ($isPaid) {
            $updates['status_pembayaran'] = 'lunas';
        }

        if ($isFailed) {
            $updates['status_pembayaran'] = 'belum_bayar';
            $updates['status_pesanan'] = 'dibatalkan';
        }

        $pesanan->update($this->onlyExistingPaymentColumns($updates));
    }

    private function buildReference(Pesanan $pesanan): string
    {
        return sprintf('SIGMA-%s-%s', $pesanan->getKey(), now()->format('YmdHis'));
    }

    private function buildItemDetails(Pesanan $pesanan): array
    {
        return $pesanan->detail_pesanans->map(function ($detail) {
            $quantity = max((int) $detail->jumlah_item, 1);
            $subtotal = (float) $detail->subtotal;

            return [
                'id' => (string) ($detail->id_produk ?? $detail->getKey()),
                'price' => (int) round($subtotal / $quantity),
                'quantity' => $quantity,
                'name' => mb_substr($detail->produk->nama_produk ?? 'Menu Kedai Sigma', 0, 50),
            ];
        })->values()->all();
    }

    private function midtransRequest()
    {
        return Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function xenditRequest()
    {
        return Http::withBasicAuth(config('services.xendit.secret_key'), '')
            ->acceptJson()
            ->timeout(20);
    }

    private function apiBaseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    private function expiryMinutes(): int
    {
        return (int) config('services.midtrans.expiry_minutes', 10);
    }

    private function xenditExpiryMinutes(): int
    {
        return (int) config('services.xendit.expiry_minutes', 10);
    }

    private function isMidtransConfigured(): bool
    {
        return filled(config('services.midtrans.server_key'));
    }

    private function isXenditConfigured(): bool
    {
        return filled(config('services.xendit.secret_key'));
    }

    private function buildQrImageUrl(string $qrString): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&data='.rawurlencode($qrString);
    }

    private function xenditCallbackUrl(): string
    {
        return rtrim(config('app.url'), '/').'/api/payment/xendit/webhook';
    }

    private function findActionUrl(array $actions, string $name): ?string
    {
        foreach ($actions as $action) {
            if (($action['name'] ?? null) === $name) {
                return $action['url'] ?? null;
            }
        }

        return null;
    }

    private function isValidSignature(array $payload): bool
    {
        $signature = $payload['signature_key'] ?? null;
        $serverKey = config('services.midtrans.server_key');

        if (! $signature || ! $serverKey) {
            return false;
        }

        $raw = ($payload['order_id'] ?? '')
            .($payload['status_code'] ?? '')
            .($payload['gross_amount'] ?? '')
            .$serverKey;

        return hash_equals(hash('sha512', $raw), $signature);
    }

    private function onlyExistingPaymentColumns(array $updates): array
    {
        return collect($updates)
            ->filter(fn ($value, $key) => $this->hasColumn($key))
            ->all();
    }

    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('pesanans', $column);
    }
}
