<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtracurricularResource\Pages;
use App\Models\Extracurricular;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon  = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Ekstrakurikuler';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Ekstrakurikuler';
    protected static ?string $pluralModelLabel = 'Ekstrakurikuler';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto Kegiatan')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'extracurriculars')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Otomatis dikonversi ke WebP'),
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

                Forms\Components\Section::make('Informasi Ekstrakurikuler')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Ekstrakurikuler')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Pramuka, Basket, Rohis'),

                        Forms\Components\TextInput::make('coach')
                            ->label('Pembina / Pelatih')
                            ->maxLength(150)
                            ->placeholder('Nama pembina atau pelatih'),

                        Forms\Components\TextInput::make('schedule')
                            ->label('Jadwal Latihan')
                            ->maxLength(150)
                            ->placeholder('Contoh: Setiap Jumat, 14.00 - 16.00 WIB'),

                        Forms\Components\Textarea::make('description')
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
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->width(60)->height(60),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('coach')->label('Pembina')->default('-'),
                Tables\Columns\TextColumn::make('schedule')->label('Jadwal')->default('-'),
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
            'index'  => Pages\ListExtracurriculars::route('/'),
            'create' => Pages\CreateExtracurricular::route('/create'),
            'edit'   => Pages\EditExtracurricular::route('/{record}/edit'),
        ];
    }
}
