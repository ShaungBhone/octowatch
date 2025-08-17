<?php

declare(strict_types=1);

namespace App\Filament\Resources\Octo\Issues\Tables;

use App\Models\Octo\Comment;
use App\Models\Octo\Issues;
use App\Services\Octo\CommentService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

final class IssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('repository.name')
                            ->formatStateUsing(fn (
                                $state,
                                $record
                            ): string => "{$state} #{$record->number}")
                            ->searchable(),
                        TextColumn::make('state')
                            ->badge()
                            ->alignEnd()
                            ->color(fn (string $state): string => match ($state) {
                                'open' => 'success',
                                'closed' => 'danger',
                            }),
                    ]),
                    TextColumn::make('title')
                        ->searchable()
                        ->weight(FontWeight::Bold),
                    TextColumn::make('body')
                        ->size(TextSize::ExtraSmall)
                        ->words(10, end: ' ...'),
                ])->space(1),
            ])
            ->recordActions([
                Action::make('view_comments')
                    ->label('Comments')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Textarea::make('comment_body')
                            ->label('Add Comment')
                            ->placeholder('Write your comment...')
                            ->rows(3),
                    ])
                    ->modalHeading(
                        fn (Issues $record): string => "{$record->repository->name} #{$record->number}"
                    )
                    ->modalContent(function (Issues $record) {
                        $comments = Comment::where(
                            'repository_id',
                            $record->octo_repository_id
                        )
                            ->where('type', 'issue')
                            ->orderBy('created_at_github')
                            ->get();

                        return view(
                            'comments-modal',
                            ['comments' => $comments, 'record' => $record]
                        );
                    })
                    ->modalSubmitActionLabel('Add Comment')
                    ->action(function (array $data, Issues $record): void {
                        $user = Auth::user();
                        $commentService = CommentService::forUser($user);

                        try {
                            $githubComment = $commentService->postIssueComment(
                                $record->repository->full_name,
                                $record->number,
                                $data['comment_body']
                            );

                            Comment::create([
                                'repository_id' => $record->octo_repository_id,
                                'octo_id' => (string) $githubComment['id'],
                                'body' => $githubComment['body'],
                                'type' => 'issue',
                                'author_login' => $githubComment['user']['login'],
                                'author_avatar_url' => $githubComment['user']['avatar_url'] ?? null,
                                'html_url' => $githubComment['html_url'] ?? null,
                                'created_at_github' => $githubComment['created_at'] ? \Carbon\Carbon::parse($githubComment['created_at']) : now(),
                                'updated_at_github' => $githubComment['updated_at'] ? \Carbon\Carbon::parse($githubComment['updated_at']) : now(),
                            ]);

                        } catch (Exception) {
                            $githubUsername = $record->repository->connection->username ?? 'Unknown';

                            Comment::create([
                                'repository_id' => $record->octo_repository_id,
                                'body' => $data['comment_body'],
                                'type' => 'issue',
                                'author_login' => $githubUsername,
                                'created_at_github' => now(),
                                'updated_at_github' => now(),
                            ]);
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('repository')
                    ->relationship('repository', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->paginationPageOptions([12, 24, 48])
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->deferFilters(false);
    }
}
