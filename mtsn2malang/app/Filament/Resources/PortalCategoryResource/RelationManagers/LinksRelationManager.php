<?php

namespace App\Filament\Resources\PortalCategoryResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Rules\SafeLink;
use App\Support\PortalIcons;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Link anak di dalam kategori Portal — tampil sebagai popup daftar link saat
 * kategori diklik di Tab Portal. Dikelola di halaman edit kategori.
 */
class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $title = 'Link di Kategori Ini';

    protected static ?string $modelLabel = 'Link';

    protected static ?string $pluralModelLabel = 'Link';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Link')
                ->required()
                ->maxLength(100),

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
                ->placeholder('https://... atau /ppid/profil')
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(2)
                ->maxLength(300)
                ->columnSpanFull(),

            TextInput::make('sort_order')
                ->label('Urutan Tampil')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText('Angka kecil tampil lebih dulu di popup. Bisa juga diatur dengan menyeret baris.'),

            Toggle::make('is_active')
                ->label('Aktif (tampil di popup)')
                ->default(true)
                ->onColor('success')
                ->offColor('danger')
                ->inline(false),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // HtmlString: SVG dari daftar ikon tetap (sanitizer ->html() membuang <svg>).
                TextColumn::make('icon')->label('Ikon')
                    ->formatStateUsing(fn (?string $state) => new HtmlString(PortalIcons::svg($state, 'w-5 h-5 text-primary-600'))),
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold')
                    ->description(fn ($record) => $record->description ? Str::limit($record->description, 60) : null),
                TextColumn::make('url')->label('Tujuan')->limit(40)
                    ->url(fn ($record) => $record->url)->openUrlInNewTab(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
