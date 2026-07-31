<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageContentResource\Pages;
use App\Models\PageContent;
use App\Support\UploadImageHelper;
use App\Filament\Resources\Concerns\HasRoleAccess;
// TiptapEditor removed — using built-in RichEditor for compatibility
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageContentResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = PageContent::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Konten Halaman';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'Konten Halaman';
    protected static ?string $pluralModelLabel = 'Konten Halaman';

    private static array $pageOptions = [
        // Tentang Kami
        'sambutan'          => 'Sambutan Kepala Madrasah',
        'selayang_pandang'  => 'Selayang Pandang',
        'visi_misi'         => 'Visi, Misi dan Tujuan',
        'struktur_madrasah' => 'Struktur Madrasah',
        'struktur_komite'   => 'Struktur Komite',
        'ikatan_alumni'     => 'Ikatan Alumni',
        // Layanan
        'maklumat'          => 'Maklumat Pelayanan',
        'kompensasi'        => 'Kompensasi Pelayanan',
        // Program
        'kelas_tahfidz'     => 'Kelas Tahfidz',
        'kelas_olimpiade'   => 'Kelas Olimpiade',
        'kelas_bilingual'   => 'Kelas Bilingual',
        'kelas_cbi'         => 'Kelas CBI/SKS',
        'adiwiyata'         => 'Adiwiyata',
        'zona_integritas'   => 'Zona Integritas',
        // PPID
        'ppid_profil'       => 'Profil PPID',
        // Ma'had
        'struktur_mahad'    => "Struktur Ma'had",
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Identitas Halaman')
                    ->schema([
                        Forms\Components\Select::make('page_key')
                            ->label('Halaman')
                            ->options(self::$pageOptions)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->native(false)
                            ->helperText('Pilih halaman yang akan diisi kontennya.'),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Halaman')
                            ->maxLength(255)
                            ->placeholder('Judul yang ditampilkan di halaman'),
                    ]),

                Forms\Components\Section::make('Gambar Utama')
                    ->schema([
                        UploadImageHelper::webpFileUpload('image', 'Upload Gambar', 'pages')
                            ->imageEditor()
                            ->helperText('Gambar header atau ilustrasi halaman · Maks 5MB · Otomatis WebP'),
                    ]),

                Forms\Components\Section::make('Data Tambahan')
                    ->description('Data JSON opsional untuk kebutuhan khusus halaman.')
                    ->schema([
                        Forms\Components\KeyValue::make('extra_data')
                            ->label('')
                            ->keyLabel('Kunci')
                            ->valueLabel('Nilai')
                            ->addActionLabel('+ Tambah Data'),
                    ])
                    ->collapsed(),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Konten Halaman')
                    ->description('Tulis konten lengkap halaman menggunakan editor di bawah.')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('')
                            ->toolbarButtons([
                                'attachFiles', 'blockquote', 'bold', 'bulletList',
                                'codeBlock', 'h2', 'h3', 'italic', 'link',
                                'orderedList', 'redo', 'strike', 'underline', 'undo',
                            ])
                            ->columnSpanFull(),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_key')
                    ->label('Halaman')
                    ->formatStateUsing(fn ($state) => self::$pageOptions[$state] ?? $state)
                    ->searchable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('title')->label('Judul')->default('-'),
                Tables\Columns\IconColumn::make('image')->label('Ada Gambar')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->image),
                Tables\Columns\TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPageContents::route('/'),
            'create' => Pages\CreatePageContent::route('/create'),
            'edit'   => Pages\EditPageContent::route('/{record}/edit'),
        ];
    }
}
