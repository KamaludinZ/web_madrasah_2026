<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class BannerResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model           = Banner::class;
    protected static ?string $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Banner/Slider';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Banner';
    protected static ?string $pluralModelLabel = 'Banner / Slider';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Gambar Banner')
                    ->description('Rekomendasi: 1920×1080 px, rasio 16:9.')
                    ->schema([
                        UploadImageHelper::webpFileUpload('image', 'Upload Gambar', 'banners', true)
                            ->helperText('SVG, WEBP, JPG, PNG · Maks 5MB · Rasio 16:9 · Otomatis dikonversi ke WebP'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih awal.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Banner Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Konten Banner')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Judul utama banner'),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Sub Judul')
                            ->maxLength(255)
                            ->placeholder('Teks pendukung di bawah judul'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Deskripsi singkat yang ditampilkan di banner'),

                        Forms\Components\TextInput::make('link')
                            ->label('Link Tujuan')
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('URL yang dituju saat banner diklik. Kosongkan jika tidak ada.'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Gambar')->width(120)->height(60),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('subtitle')->label('Sub Judul')->color('gray')->limit(40),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit'   => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
