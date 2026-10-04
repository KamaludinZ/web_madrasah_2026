<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\PortalQuickAccessResource\Pages;
use App\Models\PortalQuickAccess;
use App\Rules\SafeLink;
use App\Support\PortalIcons;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon   = 'heroicon-o-bolt';
    protected static ?string $navigationLabel  = 'Akses Cepat';
    protected static ?string $navigationGroup  = 'Portal';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Akses Cepat';
    protected static ?string $pluralModelLabel = 'Akses Cepat';
    protected static ?string $slug             = 'portal-quick-access';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tombol Akses Cepat')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Tombol')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('Contoh: Info PPDB'),

                    Forms\Components\Select::make('icon')
                        ->label('Ikon')
                        ->options(PortalIcons::htmlOptions())
                        ->in(PortalIcons::names())
                        ->allowHtml()
                        ->default(PortalIcons::DEFAULT)
                        ->required()
                        ->native(false)
                        ->searchable(),

                    Forms\Components\TextInput::make('url')
                        ->label('Link Tujuan')
                        ->required()
                        ->maxLength(255)
                        ->rule(new SafeLink())
                        ->placeholder('https://... atau /layanan/ppdb')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->helperText('Angka kecil tampil lebih dulu. Bisa juga diatur dengan menyeret baris di daftar.'),

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
                // HtmlString: SVG dari daftar ikon tetap (sanitizer ->html() membuang <svg>).
                Tables\Columns\TextColumn::make('icon')->label('Ikon')
                    ->formatStateUsing(fn (?string $state) => new \Illuminate\Support\HtmlString(PortalIcons::svg($state, 'w-6 h-6 text-primary-600'))),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('url')->label('Tujuan')->limit(40)
                    ->url(fn (PortalQuickAccess $record) => $record->url)->openUrlInNewTab(),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPortalQuickAccesses::route('/'),
            'create' => Pages\CreatePortalQuickAccess::route('/create'),
            'edit'   => Pages\EditPortalQuickAccess::route('/{record}/edit'),
        ];
    }
}
