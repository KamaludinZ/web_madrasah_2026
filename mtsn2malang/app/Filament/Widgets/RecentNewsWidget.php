<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Models\News;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentNewsWidget extends BaseWidget
{
    protected static ?string $heading = 'Berita Terbaru';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => News::with(['category', 'author'])->latest()->limit(10))
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('success'),
                TextColumn::make('author.name')
                    ->label('Penulis'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('view_count')
                    ->label('Dibaca')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y'),
            ])
            ->actions([
                Action::make('edit')
                    ->url(fn(News $record) => route('filament.admin.resources.news.edit', $record))
                    ->icon('heroicon-m-pencil'),
            ]);
    }
}
