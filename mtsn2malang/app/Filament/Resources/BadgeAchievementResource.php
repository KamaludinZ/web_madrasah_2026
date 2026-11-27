<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BadgeAchievementResource\Pages\ListBadgeAchievements;
use App\Filament\Resources\BadgeAchievementResource\Pages\CreateBadgeAchievement;
use App\Filament\Resources\BadgeAchievementResource\Pages\EditBadgeAchievement;
use App\Filament\Resources\BadgeAchievementResource\Pages;
use App\Models\BadgeAchievement;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BadgeAchievementResource extends Resource
{
    protected static ?string $model = BadgeAchievement::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Badge Prestasi';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel       = 'Badge Prestasi';
    protected static ?string $pluralModelLabel = 'Badge Prestasi';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'editor_in_chief', 'editor']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Badge')->schema([
                TextInput::make('name')
                    ->label('Nama Penghargaan / Badge')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Sekolah Adiwiyata Nasional 2024'),

                FileUpload::make('logo')
                    ->label('Logo / Ikon Badge')
                    ->image()
                    ->disk('public')
                    ->directory('badges')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('Upload logo badge. Format: JPG/PNG/WebP/SVG. Maks 2MB. Disarankan ukuran persegi (1:1).'),

                TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka lebih kecil tampil lebih dulu.'),

                Toggle::make('is_active')
                    ->label('Aktif / Tampilkan di Footer')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->width(60)->height(60)
                    ->defaultImageUrl(asset('images/logo.png')),

                TextColumn::make('name')
                    ->label('Nama Badge')
                    ->searchable()->sortable()->wrap(),

                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBadgeAchievements::route('/'),
            'create' => CreateBadgeAchievement::route('/create'),
            'edit'   => EditBadgeAchievement::route('/{record}/edit'),
        ];
    }
}
