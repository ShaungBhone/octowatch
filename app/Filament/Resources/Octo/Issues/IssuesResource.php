<?php

namespace App\Filament\Resources\Octo\Issues;

use App\Filament\Resources\Octo\Issues\Tables\IssuesTable;
use App\Filament\Resources\Octo\Issues\Pages\ManageIssues;
use App\Models\Octo\Issues;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IssuesResource extends Resource
{
    protected static ?string $model = Issues::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function table(Table $table): Table
    {
        return IssuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageIssues::route('/'),
        ];
    }
}
