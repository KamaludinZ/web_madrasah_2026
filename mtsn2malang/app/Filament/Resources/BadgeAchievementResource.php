<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeAchievementResource\Pages;
use App\Models\BadgeAchievement;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BadgeAchievementResource extends Resource
{
    protected static ?string $model = BadgeAchievement::class;
    protected static ?string $navigationIcon  = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Badge Prestasi';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel       = 'Badge Prestasi';
    protected static ?string $pluralModelLabel = 'Badge Prestasi';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'editor_in_chief', 'editor']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Badge')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Penghargaan / Badge')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Sekolah Adiwiyata Nasional 2024'),

                Forms\Components\FileUpload::make('logo')
                    ->label('Logo / Ikon Badge')
                    ->image()
                    ->disk('public')
                    ->directory('badges')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('Upload logo badge. Format: JPG/PNG/WebP/SVG. Maks 2MB. Disarankan ukuran persegi (1:1).'),

                Forms\Components\TextInput::make('order')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka lebih kecil tampil lebih dulu.'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif / Tampilkan di Footer')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->width(60)->height(60)
                    ->defaultImageUrl(asset('images/logo.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Badge')
                    ->searchable()->sortable()->wrap(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBadgeAchievements::route('/'),
            'create' => Pages\CreateBadgeAchievement::route('/create'),
            'edit'   => Pages\EditBadgeAchievement::route('/{record}/edit'),
        ];
    }
}
