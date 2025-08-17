<?php

namespace App\Filament\Resources\Octo\Repositories\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepositoriesTable
{
    public static function configure(Table $table): Table
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
                ])
            ]);
    }
}
