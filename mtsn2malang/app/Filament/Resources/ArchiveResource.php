<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArchiveResource\Pages;
use App\Models\Archive;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arsip Dokumen';
    protected static ?string $navigationGroup = 'Layanan & PPID';
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Cover Dokumen')
                    ->schema([
                        UploadImageHelper::webpFileUpload('cover', 'Upload Cover', 'archives/covers')
                            ->helperText('Gambar sampul dokumen · Maks 5MB'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Dokumen Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ]),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Jenis Arsip')
                            ->options(self::$typeOptions)
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Judul lengkap dokumen'),

                        Forms\Components\TextInput::make('category')
                            ->label('Kategori / Sub-jenis')
                            ->maxLength(100)
                            ->placeholder('Contoh: Tahun 2024, Semester Ganjil'),

                        Forms\Components\DatePicker::make('published_date')
                            ->label('Tanggal Terbit')
                            ->required()
                            ->displayFormat('d M Y')
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('File Dokumen')
                    ->schema([
                        Forms\Components\Select::make('link_type')
                            ->label('Jenis Dokumen')
                            ->options(['file' => 'Upload File', 'link' => 'Link Eksternal'])
                            ->default('file')
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File (PDF / Gambar)')
                            ->directory('archives/files')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(20480)
                            ->downloadable()
                            ->helperText('PDF atau gambar · Maks 20MB')
                            ->visible(fn (Forms\Get $get) => $get('link_type') === 'file'),

                        Forms\Components\TextInput::make('external_link')
                            ->label('Link Eksternal')
                            ->url()
                            ->placeholder('https://drive.google.com/...')
                            ->helperText('Link Google Drive, Dropbox, atau URL dokumen lainnya.')
                            ->visible(fn (Forms\Get $get) => $get('link_type') === 'link'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')->label('Cover')->width(60)->height(40),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50)->weight('semibold'),
                Tables\Columns\TextColumn::make('type')->label('Jenis')
                    ->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'berkala' => 'Berkala', 'setiap_saat' => 'Setiap Saat',
                        'serta_merta' => 'Serta Merta', 'renstra' => 'RENSTRA',
                        'perkin' => 'PERKIN', 'lkj' => 'LKJ',
                        'laporan_pengaduan' => 'Lap. Pengaduan', 'hasil_survei' => 'Hasil Survei',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('published_date')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Jenis')->options(self::$typeOptions),
            ])
            ->defaultSort('published_date', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArchives::route('/'),
            'create' => Pages\CreateArchive::route('/create'),
            'edit'   => Pages\EditArchive::route('/{record}/edit'),
        ];
    }
}
