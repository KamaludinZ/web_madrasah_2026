<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\GtkResource\Pages\ListGtk;
use App\Filament\Resources\GtkResource\Pages\CreateGtk;
use App\Filament\Resources\GtkResource\Pages\EditGtk;
use App\Filament\Resources\GtkResource\Pages;
use App\Models\Gtk;
use App\Support\UploadImageHelper;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Daftar GTK';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'GTK';
    protected static ?string $pluralModelLabel = 'Guru & Tenaga Kependidikan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto')
                    ->description('Foto resmi dengan latar belakang polos. Rasio 2:3 (portrait).')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'gtk')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Rasio 2:3 (portrait 4×6)'),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih awal.'),

                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Data Pribadi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap beserta gelar'),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(30)
                            ->placeholder('Nomor Induk Pegawai'),

                        TextInput::make('education')
                            ->label('Pendidikan Terakhir')
                            ->maxLength(100)
                            ->placeholder('Contoh: S1 Pendidikan Matematika'),
                    ]),

                Section::make('Jabatan & Tugas')
                    ->schema([
                        Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'guru'   => 'Guru',
                                'tendik' => 'Tenaga Kependidikan',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('position')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Guru Matematika, Kepala TU'),

                        TextInput::make('subject')
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
                ImageColumn::make('photo')->label('Foto')->width(48)->height(64),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('position')->label('Jabatan')->searchable(),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'guru' ? 'Guru' : 'Tendik'),
                TextColumn::make('subject')->label('Mapel')->default('-'),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
                TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['guru' => 'Guru', 'tendik' => 'Tenaga Kependidikan']),
            ])
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListGtk::route('/'),
            'create' => CreateGtk::route('/create'),
            'edit'   => EditGtk::route('/{record}/edit'),
        ];
    }
}
