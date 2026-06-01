<?php

use App\Providers\AppServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\View\ViewServiceProvider;

return [
    AppServiceProvider::class,
    FilesystemServiceProvider::class,
    ViewServiceProvider::class,
];
