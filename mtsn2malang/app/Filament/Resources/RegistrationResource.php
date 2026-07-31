<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'PPDB & PSB';
    protected static ?string $navigationGroup = 'Program';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Pendaftaran';
    protected static ?string $pluralModelLabel = 'PPDB & PSB';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Jenis & Status')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Jenis Pendaftaran')
                        ->options([
                            'ppdb' => 'PPDB — Penerimaan Peserta Didik Baru',
                            'psb'  => "PSB — Penerimaan Santri Baru Ma'had",
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Pendaftaran Aktif')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('Aktifkan agar informasi ini tampil di halaman publik.')
                        ->inline(false),
                ])->columns(2),

            Forms\Components\Section::make('Jadwal Pendaftaran')
                ->description('Tambahkan tahapan jadwal pendaftaran secara berurutan.')
                ->schema([
                    Forms\Components\Repeater::make('schedule')
                        ->label('')
                        ->schema([
                            Forms\Components\TextInput::make('kegiatan')
                                ->label('Kegiatan')
                                ->required()
                                ->placeholder('Contoh: Pendaftaran Online'),

                            Forms\Components\TextInput::make('tanggal')
                                ->label('Tanggal / Periode')
                                ->required()
                                ->placeholder('Contoh: 1 - 15 Juli 2025'),

                            Forms\Components\TextInput::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('Keterangan tambahan (opsional)'),
                        ])
                        ->columns(3)
                        ->addActionLabel('+ Tambah Jadwal')
                        ->reorderable()
                        ->collapsible(),
                ]),

            Forms\Components\Section::make('Persyaratan')
                ->schema([
                    Forms\Components\RichEditor::make('general_requirements')
                        ->label('Persyaratan Umum')
                        ->toolbarButtons(['bold', 'bulletList', 'orderedList', 'italic', 'link']),

                    Forms\Components\RichEditor::make('specific_requirements')
                        ->label('Persyaratan Khusus')
                        ->toolbarButtons(['bold', 'bulletList', 'orderedList', 'italic', 'link']),
                ])->columns(2),

            Forms\Components\Section::make('Brosur & Link Pendaftaran')
                ->schema([
                    Forms\Components\FileUpload::make('brochure_file')
                        ->label('Upload Brosur (PDF / Gambar)')
                        ->directory('registrations')
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(10240)
                        ->downloadable()
                        ->helperText('PDF atau gambar · Maks 10MB'),

                    Forms\Components\TextInput::make('brochure_link')
                        ->label('Link Brosur (Alternatif)')
                        ->url()
                        ->placeholder('https://drive.google.com/...')
                        ->helperText('Jika brosur tersedia di Google Drive atau link lain.'),

                    Forms\Components\TextInput::make('registration_link')
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
                Tables\Columns\TextColumn::make('type')->label('Jenis')
                    ->badge()
                    ->color(fn ($state): string => match($state) {
                        'ppdb' => 'success',
                        'psb'  => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state === 'ppdb' ? 'PPDB' : 'PSB'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit'   => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
