<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SchoolLeaderResource\Pages\ListSchoolLeaders;
use App\Filament\Resources\SchoolLeaderResource\Pages\CreateSchoolLeader;
use App\Filament\Resources\SchoolLeaderResource\Pages\EditSchoolLeader;
use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\SchoolLeaderResource\Pages;
use App\Models\SchoolLeader;
use App\Support\UploadImageHelper;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-identification';
    protected static ?string $navigationLabel  = 'Unsur Pimpinan';
    protected static string|\UnitEnum|null $navigationGroup  = 'Profil Sekolah';
    protected static ?int    $navigationSort   = 0;
    protected static ?string $modelLabel       = 'Unsur Pimpinan';
    protected static ?string $pluralModelLabel = 'Unsur Pimpinan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Group::make()->schema([

                Section::make('Foto')
                    ->description('Foto resmi pimpinan. Rasio 2:3 (portrait).')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'school-leaders')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Otomatis dikonversi ke WebP'),
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

                Section::make('Data Pimpinan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap beserta gelar'),

                        TextInput::make('position')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kepala Madrasah, Wakil Kepala Bidang Kurikulum'),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->nullable()
                            ->maxLength(30)
                            ->placeholder('Nomor Induk Pegawai (opsional)'),

                        TextInput::make('period')
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
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/news-placeholder.png')),

                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->default('-'),

                TextColumn::make('period')
                    ->label('Periode')
                    ->badge()
                    ->color('success')
                    ->default('-'),

                IconColumn::make('is_active')
                    ->label('Tampil')
                    ->boolean(),

                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSchoolLeaders::route('/'),
            'create' => CreateSchoolLeader::route('/create'),
            'edit'   => EditSchoolLeader::route('/{record}/edit'),
        ];
    }
}
