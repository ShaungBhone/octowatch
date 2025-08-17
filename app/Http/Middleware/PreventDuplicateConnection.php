<?php

namespace App\Http\Middleware;

use App\Models\Octo\Connection;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $connection = Connection::where(
            'user_id',
            Auth::id()
        )->first();

        if ($connection && $this->isTokenValid($connection->access_token)) {
            Notification::make()
                ->title('GitHub Already Connected')
                ->body("GitHub account '{$connection->username}' is already connected and active.")
                ->warning()
                ->send();

            return redirect()->route('filament.admin.pages.dashboard');
        }

        // If connection exists but token is invalid, delete the stale connection
        if ($connection && !$this->isTokenValid($connection->access_token)) {
            $connection->delete();
        }

        return $next($request);
    }

    private function isTokenValid(string $token): bool
    {
        try {
            $user = Socialite::driver('github')->userFromToken($token);
            return $user !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
