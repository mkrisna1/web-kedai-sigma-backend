<?php

namespace App\Providers;

use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(ViewServiceProvider::class);

        if (! $this->app->bound('view')) {
            $this->app->singleton('view', fn () => new class implements ViewFactoryContract
            {
                public function exists($view): bool
                {
                    return false;
                }

                public function file($path, $data = [], $mergeData = []): ViewContract
                {
                    return $this->emptyView($path, $data, $mergeData);
                }

                public function make($view, $data = [], $mergeData = []): ViewContract
                {
                    return $this->emptyView($view, $data, $mergeData);
                }

                public function share($key, $value = null): mixed
                {
                    return $value;
                }

                public function composer($views, $callback): array
                {
                    return [];
                }

                public function creator($views, $callback): array
                {
                    return [];
                }

                public function addNamespace($namespace, $hints): static
                {
                    return $this;
                }

                public function replaceNamespace($namespace, $hints): static
                {
                    return $this;
                }

                private function emptyView(string $name, $data = [], array $mergeData = []): ViewContract
                {
                    return new class($name, array_merge((array) $mergeData, (array) $data)) implements ViewContract
                    {
                        public function __construct(
                            private string $name,
                            private array $data = [],
                        ) {
                        }

                        public function render(): string
                        {
                            return '';
                        }

                        public function name(): string
                        {
                            return $this->name;
                        }

                        public function with($key, $value = null): static
                        {
                            if (is_array($key)) {
                                $this->data = array_merge($this->data, $key);
                            } else {
                                $this->data[$key] = $value;
                            }

                            return $this;
                        }

                        public function getData(): array
                        {
                            return $this->data;
                        }
                    };
                }
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
