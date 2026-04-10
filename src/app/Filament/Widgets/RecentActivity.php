<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivity extends TableWidget
{
    protected static ?string $heading = 'Recent Groups';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Group::query()->with('owner')->latest())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->label('Owner'),
                TextColumn::make('memberships_count')
                    ->label('Members')
                    ->counts('memberships'),
                TextColumn::make('duties_count')
                    ->label('Duties')
                    ->counts('duties'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc');
    }
}
