<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PortalQuickAccessResource\Pages\ListPortalQuickAccesses;
use App\Filament\Resources\PortalQuickAccessResource\Pages\CreatePortalQuickAccess;
use App\Filament\Resources\PortalQuickAccessResource\Pages\EditPortalQuickAccess;
use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\PortalQuickAccessResource\Pages;
use App\Models\PortalQuickAccess;
use App\Rules\SafeLink;
use App\Support\PortalIcons;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Kelola tombol Akses Cepat di bagian paling atas Tab Portal (menu admin
 * "Portal").
 */
class PortalQuickAccessResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model            = PortalQuickAccess::class;
    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-bolt';
    protected static ?string $navigationLabel  = 'Akses Cepat';
    protected static string|\UnitEnum|null $navigationGroup  = 'Portal';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Akses Cepat';
    protected static ?string $pluralModelLabel = 'Akses Cepat';
    protected static ?string $slug             = 'portal-quick-access';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tombol Akses Cepat')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Tombol')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('Contoh: Info PPDB'),

                    Select::make('icon')
                        ->label('Ikon')
                        ->options(PortalIcons::htmlOptions())
                        ->in(PortalIcons::names())
                        ->allowHtml()
                        ->default(PortalIcons::DEFAULT)
                        ->required()
                        ->native(false)
                        ->searchable(),

                    TextInput::make('url')
                        ->label('Link Tujuan')
                        ->required()
                        ->maxLength(255)
                        ->rule(new SafeLink())
                        ->placeholder('https://... atau /layanan/ppdb')
                        ->columnSpanFull(),

                    TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Angka kecil tampil lebih dulu. Bisa juga diatur dengan menyeret baris di daftar.'),

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
                // HtmlString: SVG dari daftar ikon tetap (sanitizer ->html() membuang <svg>).
                TextColumn::make('icon')->label('Ikon')
                    ->formatStateUsing(fn (?string $state) => new HtmlString(PortalIcons::svg($state, 'w-6 h-6 text-primary-600'))),
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                TextColumn::make('url')->label('Tujuan')->limit(40)
                    ->url(fn (PortalQuickAccess $record) => $record->url)->openUrlInNewTab(),
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

    public static function getPages(): array
    {
        return [
            'index'  => ListPortalQuickAccesses::route('/'),
            'create' => CreatePortalQuickAccess::route('/create'),
            'edit'   => EditPortalQuickAccess::route('/{record}/edit'),
        ];
    }
}
