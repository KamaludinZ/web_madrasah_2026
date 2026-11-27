<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ArchiveResource\Pages\ListArchives;
use App\Filament\Resources\ArchiveResource\Pages\CreateArchive;
use App\Filament\Resources\ArchiveResource\Pages\EditArchive;
use App\Filament\Resources\ArchiveResource\Pages;
use App\Models\Archive;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class ArchiveResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Archive::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arsip Dokumen';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan & PPID';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'Arsip';
    protected static ?string $pluralModelLabel = 'Arsip Dokumen';

    private static array $typeOptions = [
        'berkala'          => 'Informasi Berkala (PPID)',
        'setiap_saat'      => 'Informasi Setiap Saat (PPID)',
        'serta_merta'      => 'Informasi Serta Merta (PPID)',
        'renstra'          => 'RENSTRA (SAKIP)',
        'perkin'           => 'PERKIN (SAKIP)',
        'lkj'              => 'LKJ (SAKIP)',
        'laporan_pengaduan' => 'Laporan TL Pengaduan',
        'hasil_survei'     => 'Hasil Survei',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Cover Dokumen')
                    ->schema([
                        UploadImageHelper::webpFileUpload('cover', 'Upload Cover', 'archives/covers')
                            ->helperText('Gambar sampul dokumen · Maks 5MB'),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Dokumen Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ]),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Informasi Dokumen')
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Arsip')
                            ->options(self::$typeOptions)
                            ->required()
                            ->searchable()
                            ->native(false),

                        TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Judul lengkap dokumen'),

                        TextInput::make('category')
                            ->label('Kategori / Sub-jenis')
                            ->maxLength(100)
                            ->placeholder('Contoh: Tahun 2024, Semester Ganjil'),

                        DatePicker::make('published_date')
                            ->label('Tanggal Terbit')
                            ->required()
                            ->displayFormat('d M Y')
                            ->native(false),
                    ])->columns(2),

                Section::make('File Dokumen')
                    ->schema([
                        Select::make('link_type')
                            ->label('Jenis Dokumen')
                            ->options(['file' => 'Upload File', 'link' => 'Link Eksternal'])
                            ->default('file')
                            ->required()
                            ->native(false)
                            ->live(),

                        FileUpload::make('file_path')
                            ->label('Upload File (PDF / Gambar)')
                            ->directory('archives/files')
                            // Daftar eksplisit: semua jenis gambar (image/ wildcard) juga menerima SVG yang bisa berisi skrip.
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->maxSize(20480)
                            ->downloadable()
                            ->helperText('PDF atau gambar · Maks 20MB')
                            ->visible(fn (Get $get) => $get('link_type') === 'file'),

                        TextInput::make('external_link')
                            ->label('Link Eksternal')
                            ->url()
                            ->placeholder('https://drive.google.com/...')
                            ->helperText('Link Google Drive, Dropbox, atau URL dokumen lainnya.')
                            ->visible(fn (Get $get) => $get('link_type') === 'link'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')->label('Cover')->width(60)->height(40),
                TextColumn::make('title')->label('Judul')->searchable()->limit(50)->weight('semibold'),
                TextColumn::make('type')->label('Jenis')
                    ->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'berkala' => 'Berkala', 'setiap_saat' => 'Setiap Saat',
                        'serta_merta' => 'Serta Merta', 'renstra' => 'RENSTRA',
                        'perkin' => 'PERKIN', 'lkj' => 'LKJ',
                        'laporan_pengaduan' => 'Lap. Pengaduan', 'hasil_survei' => 'Hasil Survei',
                        default => $state,
                    }),
                TextColumn::make('published_date')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(self::$typeOptions),
            ])
            ->defaultSort('published_date', 'desc')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListArchives::route('/'),
            'create' => CreateArchive::route('/create'),
            'edit'   => EditArchive::route('/{record}/edit'),
        ];
    }
}
