<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\KategoriService;

class KategoriController extends Controller
{
    protected KategoriService $KategoriService;

    public function __construct(KategoriService $kategoriService)
    {
        $this->KategoriService = $kategoriService;
    }

    public function index()
    {
        $kategoris = $this->KategoriService->getAll();

        return response()->json([
            'success' => true,
            'data' => $kategoris,
        ]);
    }
}