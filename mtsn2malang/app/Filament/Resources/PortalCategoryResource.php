<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\PortalCategoryResource\Pages;
use App\Models\PortalCategory;
use App\Rules\SafeLink;
use App\Support\PortalIcons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Kelola kategori Tab Portal (menu admin "Portal"). Data terpisah dari
 * Portal Aplikasi lama (PortalAppResource) yang tetap dipakai Beranda.
 */
class PortalCategoryResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model            = PortalCategory::class;
    protected static ?string $navigationIcon   = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel  = 'Kategori Portal';
    protected static ?string $navigationGroup  = 'Portal';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Kategori Portal';
    protected static ?string $pluralModelLabel = 'Kategori Portal';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Kategori')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Contoh: Layanan Akademik'),

                    Forms\Components\Select::make('icon')
                        ->label('Ikon')
                        ->options(PortalIcons::htmlOptions())
                        ->in(PortalIcons::names())
                        ->allowHtml()
                        ->default(PortalIcons::DEFAULT)
                        ->required()
                        ->native(false)
                        ->searchable(),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->maxLength(300)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('main_link')
                        ->label('Link Utama (opsional)')
                        ->maxLength(255)
                        ->rule(new SafeLink())
                        ->placeholder('https://... atau /berita')
                        ->helperText('Dipakai bila kategori tidak punya link anak. Bila ada link anak aktif, klik kategori membuka popup daftar link.')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Angka kecil tampil lebih dulu. Urutan juga bisa diatur dengan menyeret baris di daftar kategori.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif (tampil di Portal)')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // HtmlString (bukan ->html()) karena sanitizer Filament membuang <svg>;
                // SVG berasal dari daftar ikon tetap PortalIcons, bukan input pengguna.
                Tables\Columns\TextColumn::make('icon')->label('Ikon')
                    ->formatStateUsing(fn (?string $state) => new \Illuminate\Support\HtmlString(PortalIcons::svg($state, 'w-6 h-6 text-primary-600'))),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->weight('semibold')
                    ->description(fn (PortalCategory $record) => \Illuminate\Support\Str::limit((string) $record->description, 60) ?: null),
                Tables\Columns\TextColumn::make('links_count')->label('Link Anak')->counts('links')->badge(),
                Tables\Columns\TextColumn::make('main_link')->label('Link Utama')->limit(35)->placeholder('—'),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            PortalCategoryResource\RelationManagers\LinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPortalCategories::route('/'),
            'create' => Pages\CreatePortalCategory::route('/create'),
            'edit'   => Pages\EditPortalCategory::route('/{record}/edit'),
        ];
    }
}
