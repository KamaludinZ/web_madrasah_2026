<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlumniCoordinatorResource\Pages;
use App\Models\AlumniCoordinator;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Koordinator Alumni';
    protected static ?string $navigationGroup = 'Layanan & PPID';
    protected static ?int    $navigationSort  = 6;
    protected static ?string $modelLabel      = 'Koordinator Alumni';
    protected static ?string $pluralModelLabel = 'Koordinator Alumni';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto')
                    ->schema([
                        UploadImageHelper::webpFileUpload('foto', 'Upload Foto', 'alumni-coordinators')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->helperText('Foto profil · Rasio 1:1 · Maks 5MB'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Data Koordinator')
                    ->schema([
                        Forms\Components\TextInput::make('nama_koordinator')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Nama lengkap koordinator alumni'),

                        Forms\Components\TextInput::make('angkatan')
                            ->label('Angkatan / Tahun Lulus')
                            ->maxLength(10)
                            ->placeholder('Contoh: 2015'),

                        Forms\Components\TextInput::make('kontak')
                            ->label('Kontak (WhatsApp / Telepon)')
                            ->maxLength(50)
                            ->placeholder('08xxxxxxxxxx'),

                        Forms\Components\Textarea::make('deskripsi')
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
                Tables\Columns\ImageColumn::make('foto')->label('Foto')->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->nama_koordinator ?? 'A') . '&background=1a7a3c&color=fff'),
                Tables\Columns\TextColumn::make('nama_koordinator')->label('Nama')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('angkatan')->label('Angkatan')->badge()->color('info')->default('-'),
                Tables\Columns\TextColumn::make('kontak')->label('Kontak')->default('-')->copyable(),
                Tables\Columns\TextColumn::make('deskripsi')->label('Keterangan')->limit(40)->default('-'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlumniCoordinators::route('/'),
            'create' => Pages\CreateAlumniCoordinator::route('/create'),
            'edit'   => Pages\EditAlumniCoordinator::route('/{record}/edit'),
        ];
    }
}
