<?php

namespace App\Filament\Resources\CommentResource\Pages;

use App\Filament\Resources\CommentResource;
use App\Models\Comment;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected function getTableQuery(): Builder
    {
        return CommentResource::getEloquentQuery();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all'      => Tab::make('Semua')
                ->icon('heroicon-m-queue-list'),
            'pending'  => Tab::make('Menunggu')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(Comment::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'approved' => Tab::make('Disetujui')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge(Comment::where('status', 'approved')->count())
                ->badgeColor('success'),
            'rejected' => Tab::make('Ditolak')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge(Comment::where('status', 'rejected')->count())
                ->badgeColor('danger'),
        ];
    }
}
