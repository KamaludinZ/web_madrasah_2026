<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BannerResource\Pages\ListBanners;
use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use App\Support\UploadImageHelper;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Banner/Slider';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Banner';
    protected static ?string $pluralModelLabel = 'Banner / Slider';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Gambar Banner')
                    ->description('Rekomendasi: 1920×1080 px, rasio 16:9.')
                    ->schema([
                        UploadImageHelper::webpFileUpload('image', 'Upload Gambar', 'banners', true)
                            ->helperText('SVG, WEBP, JPG, PNG · Maks 5MB · Rasio 16:9 · Otomatis dikonversi ke WebP'),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih awal.'),

                        Toggle::make('is_active')
                            ->label('Banner Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Konten Banner')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Judul utama banner'),

                        TextInput::make('subtitle')
                            ->label('Sub Judul')
                            ->maxLength(255)
                            ->placeholder('Teks pendukung di bawah judul'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Deskripsi singkat yang ditampilkan di banner'),

                        TextInput::make('link')
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
                ImageColumn::make('image')->label('Gambar')->width(120)->height(60),
                TextColumn::make('title')->label('Judul')->searchable()->weight('semibold'),
                TextColumn::make('subtitle')->label('Sub Judul')->color('gray')->limit(40),
                TextColumn::make('order')->label('Urutan')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit'   => EditBanner::route('/{record}/edit'),
        ];
    }
}
