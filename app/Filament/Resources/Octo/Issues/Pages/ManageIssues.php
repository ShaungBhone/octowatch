<?php

declare(strict_types=1);

namespace App\Filament\Resources\Octo\Issues\Pages;

use App\Filament\Resources\Octo\Issues\IssuesResource;
use App\Services\Octo\CoordinatorService;
use Exception;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

final class ManageIssues extends ManageRecords
{
    protected static string $resource = IssuesResource::class;

    public function syncIssues(): void
    {
        try {
            $service = CoordinatorService::forUser(Auth::user());

            $service->syncAllIssuesAsync();

            Notification::make()
                ->title('Sync Started')
                ->body('Issue sync has been queued and will run in the background. You will see results shortly.')
                ->success()
                ->send();

            $this->redirect(self::getUrl());
        } catch (Exception $e) {
            Notification::make()
                ->title('Sync Failed')
                ->body('Failed to queue issue sync: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncIssuesWithComments(): void
    {
        try {
            $service = CoordinatorService::forUser(Auth::user());

            $service->syncIssuesAndCommentsAsync();

            Notification::make()
                ->title('Sync Started')
                ->body('Issue and comment sync has been queued and will run in the background. You will see results shortly.')
                ->success()
                ->send();

            $this->redirect(self::getUrl());
        } catch (Exception $e) {
            Notification::make()
                ->title('Sync Failed')
                ->body('Failed to queue issue and comment sync: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncAll(): void
    {
        try {
            $service = CoordinatorService::forUser(Auth::user());

            $service->syncAllAsync();

            Notification::make()
                ->title('Complete Sync Started')
                ->body('Full synchronization (repositories, issues, and comments) has been queued and will run in the background. This may take several minutes.')
                ->success()
                ->send();

            $this->redirect(self::getUrl());
        } catch (Exception $e) {
            Notification::make()
                ->title('Sync Failed')
                ->body('Failed to queue complete sync: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Actions\Action::make('sync_issues')
                    ->label('Sync Issues')
                    ->icon('heroicon-o-arrow-path')
                    ->action('syncIssues'),

                Actions\Action::make('sync_issues_with_comments')
                    ->label('Sync Issues + Comments')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->action('syncIssuesWithComments'),

                Actions\Action::make('sync_all')
                    ->label('Sync Everything')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->action('syncAll'),
            ])
                ->label('Sync')
                ->button()
                ->icon('heroicon-o-arrow-path'),
        ];
    }
}
