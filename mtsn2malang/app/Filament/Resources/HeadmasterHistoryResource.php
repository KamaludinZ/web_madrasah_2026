<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeadmasterHistoryResource\Pages;
use App\Models\HeadmasterHistory;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class HeadmasterHistoryResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = HeadmasterHistory::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Kepala';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Riwayat Kepala Madrasah';
    protected static ?string $pluralModelLabel = 'Riwayat Kepala Madrasah';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto')
                    ->description('Foto resmi kepala madrasah.')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'headmaster-history')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Otomatis dikonversi ke WebP'),
                    ]),

                Forms\Components\Section::make('Urutan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Urutkan dari yang pertama menjabat (angka terkecil).'),
                    ]),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Data Kepala Madrasah')
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

                        Forms\Components\TextInput::make('period')
                            ->label('Periode Jabatan')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: 2018 - 2022')
                            ->helperText('Format bebas, misal: 2018 - 2022 atau 2023 - Sekarang'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->circular()
                    ->defaultImageUrl(asset('images/news-placeholder.png')),
                Tables\Columns\TextColumn::make('name')->label('Nama Lengkap')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('nip')->label('NIP')->default('-'),
                Tables\Columns\TextColumn::make('period')->label('Periode')->badge()->color('success'),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHeadmasterHistories::route('/'),
            'create' => Pages\CreateHeadmasterHistory::route('/create'),
            'edit'   => Pages\EditHeadmasterHistory::route('/{record}/edit'),
        ];
    }
}
