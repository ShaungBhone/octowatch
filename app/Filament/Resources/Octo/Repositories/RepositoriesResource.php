<?php

declare(strict_types=1);

namespace App\Filament\Resources\Octo\Repositories;

use App\Models\Octo\Repository;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RepositoriesResource extends Resource
{
    protected static ?string $model = Repository::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static ?string $navigationLabel = 'Repositories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->limit(10),
                TextColumn::make('stargazers_count')
                    ->label('Stars')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('forks_count')
                    ->label('Forks')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('open_issues_count')
                    ->label('Issues')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('watchers_count')
                    ->label('Watchers')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('private')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at_github')
                    ->label('Updated at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->deferFilters(false)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRepositories::route('/'),
        ];
    }
}
