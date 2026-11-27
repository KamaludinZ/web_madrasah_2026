<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use App\Filament\Resources\RegistrationResource\Pages\ListRegistrations;
use App\Filament\Resources\RegistrationResource\Pages\CreateRegistration;
use App\Filament\Resources\RegistrationResource\Pages\EditRegistration;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class RegistrationResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model           = Registration::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'PPDB & PSB';
    protected static string|\UnitEnum|null $navigationGroup = 'Program';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Pendaftaran';
    protected static ?string $pluralModelLabel = 'PPDB & PSB';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Jenis & Status')
                ->schema([
                    Select::make('type')
                        ->label('Jenis Pendaftaran')
                        ->options([
                            'ppdb' => 'PPDB — Penerimaan Peserta Didik Baru',
                            'psb'  => "PSB — Penerimaan Santri Baru Ma'had",
                        ])
                        ->required()
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('Pendaftaran Aktif')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('Aktifkan agar informasi ini tampil di halaman publik.')
                        ->inline(false),
                ])->columns(2),

            Section::make('Jadwal Pendaftaran')
                ->description('Tambahkan tahapan jadwal pendaftaran secara berurutan.')
                ->schema([
                    Repeater::make('schedule')
                        ->label('')
                        ->schema([
                            TextInput::make('kegiatan')
                                ->label('Kegiatan')
                                ->required()
                                ->placeholder('Contoh: Pendaftaran Online'),

                            TextInput::make('tanggal')
                                ->label('Tanggal / Periode')
                                ->required()
                                ->placeholder('Contoh: 1 - 15 Juli 2025'),

                            TextInput::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('Keterangan tambahan (opsional)'),
                        ])
                        ->columns(3)
                        ->addActionLabel('+ Tambah Jadwal')
                        ->reorderable()
                        ->collapsible(),
                ]),

            Section::make('Persyaratan')
                ->schema([
                    RichEditor::make('general_requirements')
                        ->label('Persyaratan Umum')
                        ->toolbarButtons(['bold', 'bulletList', 'orderedList', 'italic', 'link']),

                    RichEditor::make('specific_requirements')
                        ->label('Persyaratan Khusus')
                        ->toolbarButtons(['bold', 'bulletList', 'orderedList', 'italic', 'link']),
                ])->columns(2),

            Section::make('Brosur & Link Pendaftaran')
                ->schema([
                    FileUpload::make('brochure_file')
                        ->label('Upload Brosur (PDF / Gambar)')
                        ->directory('registrations')
                        // Daftar eksplisit: semua jenis gambar (image/ wildcard) juga menerima SVG yang bisa berisi skrip.
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->helperText('PDF atau gambar · Maks 10MB'),

                    TextInput::make('brochure_link')
                        ->label('Link Brosur (Alternatif)')
                        ->url()
                        ->placeholder('https://drive.google.com/...')
                        ->helperText('Jika brosur tersedia di Google Drive atau link lain.'),

                    TextInput::make('registration_link')
                        ->label('Link Pendaftaran Online')
                        ->url()
                        ->placeholder('https://ppdb.kemenag.go.id/...')
                        ->helperText('URL formulir pendaftaran online.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Jenis')
                    ->badge()
                    ->color(fn ($state): string => match($state) {
                        'ppdb' => 'success',
                        'psb'  => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state === 'ppdb' ? 'PPDB' : 'PSB'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRegistrations::route('/'),
            'create' => CreateRegistration::route('/create'),
            'edit'   => EditRegistration::route('/{record}/edit'),
        ];
    }
}
