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
use App\Filament\Resources\ExtracurricularResource\Pages\ListExtracurriculars;
use App\Filament\Resources\ExtracurricularResource\Pages\CreateExtracurricular;
use App\Filament\Resources\ExtracurricularResource\Pages\EditExtracurricular;
use App\Filament\Resources\ExtracurricularResource\Pages;
use App\Models\Extracurricular;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class ExtracurricularResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Extracurricular::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Ekstrakurikuler';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Ekstrakurikuler';
    protected static ?string $pluralModelLabel = 'Ekstrakurikuler';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto Kegiatan')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'extracurriculars')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Otomatis dikonversi ke WebP'),
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

                Section::make('Informasi Ekstrakurikuler')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Ekstrakurikuler')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Pramuka, Basket, Rohis'),

                        TextInput::make('coach')
                            ->label('Pembina / Pelatih')
                            ->maxLength(150)
                            ->placeholder('Nama pembina atau pelatih'),

                        TextInput::make('schedule')
                            ->label('Jadwal Latihan')
                            ->maxLength(150)
                            ->placeholder('Contoh: Setiap Jumat, 14.00 - 16.00 WIB'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder('Deskripsi singkat tentang kegiatan ekstrakurikuler ini...')
                            ->columnSpanFull(),
                    ])->columns(2),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->label('Foto')->width(60)->height(60),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('coach')->label('Pembina')->default('-'),
                TextColumn::make('schedule')->label('Jadwal')->default('-'),
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
            'index'  => ListExtracurriculars::route('/'),
            'create' => CreateExtracurricular::route('/create'),
            'edit'   => EditExtracurricular::route('/{record}/edit'),
        ];
    }
}
