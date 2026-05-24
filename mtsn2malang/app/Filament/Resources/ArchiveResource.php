<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArchiveResource\Pages;
use App\Models\Archive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArchiveResource extends Resource
{
    protected static ?string $model = Archive::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arsip Dokumen';
    protected static ?string $navigationGroup = 'Layanan & PPID';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Jenis Arsip')
                ->options([
                    'berkala' => 'Informasi Berkala (PPID)',
                    'setiap_saat' => 'Informasi Setiap Saat (PPID)',
                    'serta_merta' => 'Informasi Serta Merta (PPID)',
                    'renstra' => 'RENSTRA (SAKIP)',
                    'perkin' => 'PERKIN (SAKIP)',
                    'lkj' => 'LKJ (SAKIP)',
                    'laporan_pengaduan' => 'Laporan TL Pengaduan',
                    'hasil_survei' => 'Hasil Survei',
                ])
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('title')->label('Judul')->required(),
            Forms\Components\TextInput::make('category')->label('Kategori'),
            Forms\Components\DatePicker::make('published_date')->label('Tanggal Terbit')->required(),

            Forms\Components\FileUpload::make('cover')
                ->label('Gambar Cover')
                ->image()
                ->directory('archives/covers')
                ->imageEditor(),

            Forms\Components\Select::make('link_type')
                ->label('Jenis Dokumen')
                ->options(['file' => 'Upload File', 'link' => 'Link Eksternal'])
                ->default('file')
                ->live(),

            Forms\Components\FileUpload::make('file_path')
                ->label('Upload File (PDF)')
                ->directory('archives/files')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(20480)
                ->visible(fn(Forms\Get $get) => $get('link_type') === 'file'),

            Forms\Components\TextInput::make('external_link')
                ->label('Link Eksternal')
                ->url()
                ->visible(fn(Forms\Get $get) => $get('link_type') === 'link'),

            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')->label('Cover')->width(60)->height(40),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\BadgeColumn::make('type')->label('Jenis')
                    ->formatStateUsing(fn($state) => match($state) {
                        'berkala' => 'Berkala', 'setiap_saat' => 'Setiap Saat', 'serta_merta' => 'Serta Merta',
                        'renstra' => 'RENSTRA', 'perkin' => 'PERKIN', 'lkj' => 'LKJ',
                        'laporan_pengaduan' => 'Lap. Pengaduan', 'hasil_survei' => 'Hasil Survei',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('published_date')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'berkala' => 'Berkala', 'setiap_saat' => 'Setiap Saat', 'serta_merta' => 'Serta Merta',
                    'renstra' => 'RENSTRA', 'perkin' => 'PERKIN', 'lkj' => 'LKJ',
                    'laporan_pengaduan' => 'Lap. Pengaduan', 'hasil_survei' => 'Hasil Survei',
                ]),
            ])
            ->defaultSort('published_date', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArchives::route('/'),
            'create' => Pages\CreateArchive::route('/create'),
            'edit' => Pages\EditArchive::route('/{record}/edit'),
        ];
    }
}
