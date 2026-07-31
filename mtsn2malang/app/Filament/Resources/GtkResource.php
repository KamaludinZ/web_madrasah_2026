<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GtkResource\Pages;
use App\Models\Gtk;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class GtkResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Gtk::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Daftar GTK';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'GTK';
    protected static ?string $pluralModelLabel = 'Guru & Tenaga Kependidikan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto')
                    ->description('Foto resmi dengan latar belakang polos. Rasio 2:3 (portrait).')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'gtk')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Rasio 2:3 (portrait 4×6)'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih awal.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap beserta gelar'),

                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(30)
                            ->placeholder('Nomor Induk Pegawai'),

                        Forms\Components\TextInput::make('education')
                            ->label('Pendidikan Terakhir')
                            ->maxLength(100)
                            ->placeholder('Contoh: S1 Pendidikan Matematika'),
                    ]),

                Forms\Components\Section::make('Jabatan & Tugas')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'guru'   => 'Guru',
                                'tendik' => 'Tenaga Kependidikan',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Guru Matematika, Kepala TU'),

                        Forms\Components\TextInput::make('subject')
                            ->label('Mata Pelajaran')
                            ->maxLength(150)
                            ->placeholder('Kosongkan jika bukan guru mapel')
                            ->helperText('Khusus untuk guru mata pelajaran.'),
                    ])->columns(2),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->width(48)->height(64),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('position')->label('Jabatan')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Jenis')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'guru' ? 'Guru' : 'Tendik'),
                Tables\Columns\TextColumn::make('subject')->label('Mapel')->default('-'),
                Tables\Columns\IconColumn::make('is_active')->label('Tampil')->boolean(),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(['guru' => 'Guru', 'tendik' => 'Tenaga Kependidikan']),
            ])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGtk::route('/'),
            'create' => Pages\CreateGtk::route('/create'),
            'edit'   => Pages\EditGtk::route('/{record}/edit'),
        ];
    }
}
