<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AlumniCoordinatorResource\Pages\ListAlumniCoordinators;
use App\Filament\Resources\AlumniCoordinatorResource\Pages\CreateAlumniCoordinator;
use App\Filament\Resources\AlumniCoordinatorResource\Pages\EditAlumniCoordinator;
use App\Filament\Resources\AlumniCoordinatorResource\Pages;
use App\Models\AlumniCoordinator;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class AlumniCoordinatorResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = AlumniCoordinator::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Koordinator Alumni';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan & PPID';
    protected static ?int    $navigationSort  = 6;
    protected static ?string $modelLabel      = 'Koordinator Alumni';
    protected static ?string $pluralModelLabel = 'Koordinator Alumni';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto')
                    ->schema([
                        UploadImageHelper::webpFileUpload('foto', 'Upload Foto', 'alumni-coordinators')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->helperText('Foto profil · Rasio 1:1 · Maks 5MB'),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Data Koordinator')
                    ->schema([
                        TextInput::make('nama_koordinator')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Nama lengkap koordinator alumni'),

                        TextInput::make('angkatan')
                            ->label('Angkatan / Tahun Lulus')
                            ->maxLength(10)
                            ->placeholder('Contoh: 2015'),

                        TextInput::make('kontak')
                            ->label('Kontak (WhatsApp / Telepon)')
                            ->maxLength(50)
                            ->placeholder('08xxxxxxxxxx'),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi / Keterangan')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Jabatan, wilayah koordinasi, atau keterangan lainnya...')
                            ->columnSpanFull(),
                    ])->columns(2),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')->label('Foto')->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->nama_koordinator ?? 'A') . '&background=1a7a3c&color=fff'),
                TextColumn::make('nama_koordinator')->label('Nama')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('angkatan')->label('Angkatan')->badge()->color('info')->default('-'),
                TextColumn::make('kontak')->label('Kontak')->default('-')->copyable(),
                TextColumn::make('deskripsi')->label('Keterangan')->limit(40)->default('-'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAlumniCoordinators::route('/'),
            'create' => CreateAlumniCoordinator::route('/create'),
            'edit'   => EditAlumniCoordinator::route('/{record}/edit'),
        ];
    }
}
