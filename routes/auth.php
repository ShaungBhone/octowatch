<?php

use App\Http\Controllers\ConnectViaOctoController;
use Illuminate\Support\Facades\Route;

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');

Route::middleware([
    'auth',
    'connected'
])->group(function () {
    Route::get('auth/redirect', [ConnectViaOctoController::class, 'create'])
        ->name('octo.connect');

    Route::get('auth/callback', [ConnectViaOctoController::class, 'store']);

    // Route::post('/auth/github/disconnect', [GitHubOAuthController::class, 'disconnect'])
    //     ->name('github.oauth.disconnect');
});
