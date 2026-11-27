<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use App\Filament\Resources\PageContentResource\Pages\ListPageContents;
use App\Filament\Resources\PageContentResource\Pages\CreatePageContent;
use App\Filament\Resources\PageContentResource\Pages\EditPageContent;
use App\Filament\Resources\PageContentResource\Pages;
use App\Models\PageContent;
use App\Support\UploadImageHelper;
use App\Filament\Resources\Concerns\HasRoleAccess;
// TiptapEditor removed — using built-in RichEditor for compatibility
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Konten Halaman';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
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
        'program_unggulan'  => 'Program Unggulan',
        // PPID
        'ppid_profil'       => 'Profil PPID',
        // Ma'had
        'struktur_mahad'    => "Struktur Ma'had",
        // Pengaduan
        'whistleblowing'    => 'Whistleblowing',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Identitas Halaman')
                    ->schema([
                        Select::make('page_key')
                            ->label('Halaman')
                            ->options(self::$pageOptions)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->native(false)
                            ->helperText('Pilih halaman yang akan diisi kontennya.'),

                        TextInput::make('title')
                            ->label('Judul Halaman')
                            ->maxLength(255)
                            ->placeholder('Judul yang ditampilkan di halaman'),
                    ]),

                Section::make('Gambar Utama')
                    ->schema([
                        UploadImageHelper::webpFileUpload('image', 'Upload Gambar', 'pages')
                            ->imageEditor()
                            ->helperText('Gambar header atau ilustrasi halaman · Maks 5MB · Otomatis WebP'),
                    ]),

                Section::make('Data Tambahan')
                    ->description('Data JSON opsional untuk kebutuhan khusus halaman.')
                    ->schema([
                        KeyValue::make('extra_data')
                            ->label('')
                            ->keyLabel('Kunci')
                            ->valueLabel('Nilai')
                            ->addActionLabel('+ Tambah Data'),
                    ])
                    ->collapsed(),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Konten Halaman')
                    ->description('Tulis konten lengkap halaman menggunakan editor di bawah.')
                    ->schema([
                        RichEditor::make('content')
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
                TextColumn::make('page_key')
                    ->label('Halaman')
                    ->formatStateUsing(fn ($state) => self::$pageOptions[$state] ?? $state)
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('title')->label('Judul')->default('-'),
                IconColumn::make('image')->label('Ada Gambar')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->image),
                TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPageContents::route('/'),
            'create' => CreatePageContent::route('/create'),
            'edit'   => EditPageContent::route('/{record}/edit'),
        ];
    }
}
