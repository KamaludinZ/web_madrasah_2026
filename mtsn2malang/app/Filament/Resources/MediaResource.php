<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Models\GalleryPost;
use Filament\Resources\Resource;

class MediaResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor', 'contributor', 'moderator']);
    }

    protected static ?string $model = GalleryPost::class;

    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-photo';
    protected static ?string $navigationLabel  = 'Media';
    protected static string|\UnitEnum|null $navigationGroup  = 'Konten';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Media';
    protected static ?string $pluralModelLabel = 'Media';

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
        ];
    }
}
