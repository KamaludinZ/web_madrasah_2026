<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\HeadmasterHistoryResource\Pages\ListHeadmasterHistories;
use App\Filament\Resources\HeadmasterHistoryResource\Pages\CreateHeadmasterHistory;
use App\Filament\Resources\HeadmasterHistoryResource\Pages\EditHeadmasterHistory;
use App\Filament\Resources\HeadmasterHistoryResource\Pages;
use App\Models\HeadmasterHistory;
use App\Support\UploadImageHelper;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Kepala';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Riwayat Kepala Madrasah';
    protected static ?string $pluralModelLabel = 'Riwayat Kepala Madrasah';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto')
                    ->description('Foto resmi kepala madrasah.')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'headmaster-history')
                            ->imageEditor()
                            ->helperText('JPG, PNG · Maks 5MB · Otomatis dikonversi ke WebP'),
                    ]),

                Section::make('Urutan')
                    ->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Urutkan dari yang pertama menjabat (angka terkecil).'),
                    ]),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Data Kepala Madrasah')
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

                        TextInput::make('period')
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
                ImageColumn::make('photo')->label('Foto')->circular()
                    ->defaultImageUrl(asset('images/news-placeholder.png')),
                TextColumn::make('name')->label('Nama Lengkap')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('nip')->label('NIP')->default('-'),
                TextColumn::make('period')->label('Periode')->badge()->color('success'),
                TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHeadmasterHistories::route('/'),
            'create' => CreateHeadmasterHistory::route('/create'),
            'edit'   => EditHeadmasterHistory::route('/{record}/edit'),
        ];
    }
}
