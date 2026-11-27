<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PortalCategoryResource\RelationManagers\LinksRelationManager;
use App\Filament\Resources\PortalCategoryResource\Pages\ListPortalCategories;
use App\Filament\Resources\PortalCategoryResource\Pages\CreatePortalCategory;
use App\Filament\Resources\PortalCategoryResource\Pages\EditPortalCategory;
use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\PortalCategoryResource\Pages;
use App\Models\PortalCategory;
use App\Rules\SafeLink;
use App\Support\PortalIcons;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel  = 'Kategori Portal';
    protected static string|\UnitEnum|null $navigationGroup  = 'Portal';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Kategori Portal';
    protected static ?string $pluralModelLabel = 'Kategori Portal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Contoh: Layanan Akademik'),

                    Select::make('icon')
                        ->label('Ikon')
                        ->options(PortalIcons::htmlOptions())
                        ->in(PortalIcons::names())
                        ->allowHtml()
                        ->default(PortalIcons::DEFAULT)
                        ->required()
                        ->native(false)
                        ->searchable(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->maxLength(300)
                        ->columnSpanFull(),

                    TextInput::make('main_link')
                        ->label('Link Utama (opsional)')
                        ->maxLength(255)
                        ->rule(new SafeLink())
                        ->placeholder('https://... atau /berita')
                        ->helperText('Dipakai bila kategori tidak punya link anak. Bila ada link anak aktif, klik kategori membuka popup daftar link.')
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Tampilan')
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Angka kecil tampil lebih dulu. Urutan juga bisa diatur dengan menyeret baris di daftar kategori.'),

                    Toggle::make('is_active')
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
                TextColumn::make('icon')->label('Ikon')
                    ->formatStateUsing(fn (?string $state) => new HtmlString(PortalIcons::svg($state, 'w-6 h-6 text-primary-600'))),
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold')
                    ->description(fn (PortalCategory $record) => Str::limit((string) $record->description, 60) ?: null),
                TextColumn::make('links_count')->label('Link Anak')->counts('links')->badge(),
                TextColumn::make('main_link')->label('Link Utama')->limit(35)->placeholder('—'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            LinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPortalCategories::route('/'),
            'create' => CreatePortalCategory::route('/create'),
            'edit'   => EditPortalCategory::route('/{record}/edit'),
        ];
    }
}
