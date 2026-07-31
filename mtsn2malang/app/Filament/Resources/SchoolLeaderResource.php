<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\SchoolLeaderResource\Pages;
use App\Models\SchoolLeader;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolLeaderResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model            = SchoolLeader::class;
    protected static ?string $navigationIcon   = 'heroicon-o-identification';
    protected static ?string $navigationLabel  = 'Unsur Pimpinan';
    protected static ?string $navigationGroup  = 'Profil Sekolah';
    protected static ?int    $navigationSort   = 0;
    protected static ?string $modelLabel       = 'Unsur Pimpinan';
    protected static ?string $pluralModelLabel = 'Unsur Pimpinan';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto')
                    ->description('Foto resmi pimpinan. Rasio 2:3 (portrait).')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'school-leaders')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Otomatis dikonversi ke WebP'),
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

                Forms\Components\Section::make('Data Pimpinan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap beserta gelar'),

                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kepala Madrasah, Wakil Kepala Bidang Kurikulum'),

                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->nullable()
                            ->maxLength(30)
                            ->placeholder('Nomor Induk Pegawai (opsional)'),

                        Forms\Components\TextInput::make('period')
                            ->label('Periode')
                            ->nullable()
                            ->maxLength(100)
                            ->placeholder('Contoh: 2023 - Sekarang')
                            ->helperText('Format bebas, misal: 2023 - Sekarang atau 2022 - 2025'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/news-placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->default('-'),

                Tables\Columns\TextColumn::make('period')
                    ->label('Periode')
                    ->badge()
                    ->color('success')
                    ->default('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Tampil')
                    ->boolean(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSchoolLeaders::route('/'),
            'create' => Pages\CreateSchoolLeader::route('/create'),
            'edit'   => Pages\EditSchoolLeader::route('/{record}/edit'),
        ];
    }
}
