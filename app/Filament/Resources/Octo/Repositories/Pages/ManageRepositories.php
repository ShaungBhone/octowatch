<?php

namespace App\Filament\Resources\Octo\Repositories\Pages;

use App\Filament\Resources\Octo\Repositories\RepositoriesResource;
use App\Services\Octo\CoordinatorService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageRepositories extends ManageRecords
{
    protected static string $resource = RepositoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_repositories')
                ->label('Sync Repositories')
                ->icon('heroicon-o-arrow-path')
                ->action('syncRepositories')
                ->requiresConfirmation()
                ->modalHeading('Sync GitHub Repositories')
                ->modalDescription('This will fetch all your repositories from GitHub and update the database.')
                ->modalSubmitActionLabel('Sync'),
            Actions\Action::make('sync_repositories_with_comments')
                ->label('Sync Repositories + Comments')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->action('syncRepositoriesWithComments')
                ->requiresConfirmation()
                ->modalHeading('Sync GitHub Repositories and Comments')
                ->modalDescription('This will fetch all your repositories and comments from GitHub and update the database.')
                ->modalSubmitActionLabel('Sync All'),
        ];
    }

    public function syncRepositories(): void
    {
        try {
            $service = CoordinatorService::forUser(Auth::user());

            // Use async version for better performance
            $service->syncRepositoriesAsync();

            Notification::make()
                ->title('Sync Started')
                ->body('Repository sync has been queued and will run in the background. You will see results shortly.')
                ->success()
                ->send();

            // Refresh the table
            $this->redirect(static::getUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sync Failed')
                ->body('Failed to queue repository sync: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncRepositoriesWithComments(): void
    {
        try {
            $service = CoordinatorService::forUser(Auth::user());

            // Use async version for better performance
            $service->syncRepositoriesWithCommentsAsync();

            Notification::make()
                ->title('Sync Started')
                ->body('Repository and comment sync has been queued and will run in the background. You will see results shortly.')
                ->success()
                ->send();

            // Refresh the table
            $this->redirect(static::getUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sync Failed')
                ->body('Failed to queue repository and comment sync: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
