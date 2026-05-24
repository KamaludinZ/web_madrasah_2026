<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageContentResource\Pages;
use App\Models\PageContent;
use Awcodes\FilamentTiptapEditor\TiptapEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageContentResource extends Resource
{
    protected static ?string $model = PageContent::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Konten Halaman';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('page_key')
                ->label('Halaman')
                ->options([
                    'sambutan' => 'Sambutan Kepala Madrasah',
                    'selayang_pandang' => 'Selayang Pandang',
                    'visi_misi' => 'Visi, Misi dan Tujuan',
                    'struktur_madrasah' => 'Struktur Madrasah',
                    'struktur_komite' => 'Struktur Komite',
                    'ikatan_alumni' => 'Ikatan Alumni',
                    'maklumat' => 'Maklumat Pelayanan',
                    'kompensasi' => 'Kompensasi Pelayanan',
                    'kelas_tahfidz' => 'Kelas Tahfidz',
                    'kelas_olimpiade' => 'Kelas Olimpiade',
                    'kelas_bilingual' => 'Kelas Bilingual',
                    'kelas_cbi' => 'Kelas CBI/SKS',
                    'adiwiyata' => 'Adiwiyata',
                    'struktur_mahad' => "Struktur Ma'had",
                ])
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('title')->label('Judul Halaman'),

            Forms\Components\FileUpload::make('image')
                ->label('Gambar Utama')
                ->image()
                ->directory('pages')
                ->imageEditor(),

            TiptapEditor::make('content')
                ->label('Konten')
                ->columnSpanFull(),

            Forms\Components\KeyValue::make('extra_data')
                ->label('Data Tambahan (JSON)')
                ->keyLabel('Kunci')
                ->valueLabel('Nilai'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_key')->label('Halaman')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Judul'),
                Tables\Columns\TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageContents::route('/'),
            'create' => Pages\CreatePageContent::route('/create'),
            'edit' => Pages\EditPageContent::route('/{record}/edit'),
        ];
    }
}
