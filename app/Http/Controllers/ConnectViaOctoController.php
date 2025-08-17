<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Octo\Connection;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final class ConnectViaOctoController extends Controller
{
    public function create()
    {
        return Socialite::driver('github')
            ->scopes(['repo', 'read:org', 'read:user'])
            ->redirect();
    }

    public function store()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            // Check if we already have an account for that GitHub email address
            if (\App\Models\User::where(
                [
                    'email' => $githubUser->getEmail(),
                    'github_id' => null,
                ]
            )->exists()) {
                return redirect(route('filament.admin.pages.dashboard'))
                    ->withErrors([
                        'email' => 'An account for this email already exists.',
                    ]);
            }

            // Create the connection
            Connection::create([
                'user_id' => Auth::id(),
                'github_id' => $githubUser->getId(),
                'access_token' => $githubUser->token,
                'refresh_token' => $githubUser->refreshToken,
                'username' => $githubUser->getNickname(),
                'avatar_url' => $githubUser->getAvatar(),
                'github_email' => $githubUser->getEmail(),
            ]);

            Notification::make()
                ->title('GitHub Connected Successfully')
                ->success()
                ->send();

            return redirect()->route('filament.admin.pages.dashboard');
        } catch (Exception $e) {
            Notification::make()
                ->title('GitHub Connection Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return redirect()->route('filament.admin.pages.dashboard');
        }
    }

    public function destroy(string $id): void
    {
        //
    }
}
