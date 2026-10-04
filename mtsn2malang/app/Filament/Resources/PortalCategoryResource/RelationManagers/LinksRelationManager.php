<?php

namespace App\Filament\Resources\PortalCategoryResource\RelationManagers;

use App\Rules\SafeLink;
use App\Support\PortalIcons;
use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama Link')
                ->required()
                ->maxLength(100),

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
                ->placeholder('https://... atau /ppid/profil')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label('Deskripsi')
                ->rows(2)
                ->maxLength(300)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan Tampil')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText('Angka kecil tampil lebih dulu di popup. Bisa juga diatur dengan menyeret baris.'),

            Forms\Components\Toggle::make('is_active')
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
                Tables\Columns\TextColumn::make('icon')->label('Ikon')
                    ->formatStateUsing(fn (?string $state) => new \Illuminate\Support\HtmlString(PortalIcons::svg($state, 'w-5 h-5 text-primary-600'))),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->weight('semibold')
                    ->description(fn ($record) => $record->description ? \Illuminate\Support\Str::limit($record->description, 60) : null),
                Tables\Columns\TextColumn::make('url')->label('Tujuan')->limit(40)
                    ->url(fn ($record) => $record->url)->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
