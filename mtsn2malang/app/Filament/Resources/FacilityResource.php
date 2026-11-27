<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\FacilityResource\Pages\ListFacilities;
use App\Filament\Resources\FacilityResource\Pages\CreateFacility;
use App\Filament\Resources\FacilityResource\Pages\EditFacility;
use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class FacilityResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Facility::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Sarana & Prasarana';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Sarana Prasarana';
    protected static ?string $pluralModelLabel = 'Sarana & Prasarana';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto Fasilitas')
                    ->schema([
                        UploadImageHelper::webpFileUpload('image', 'Upload Foto', 'facilities', true)
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
                            ->label('Tampilkan')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Informasi Fasilitas')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Fasilitas')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Laboratorium IPA, Perpustakaan, Masjid'),

                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'akademik'   => 'Akademik',
                                'olahraga'   => 'Olahraga',
                                'ibadah'     => 'Ibadah',
                                'mahad'      => "Ma'had",
                                'penunjang'  => 'Penunjang',
                                'lainnya'    => 'Lainnya',
                            ])
                            ->native(false)
                            ->placeholder('Pilih kategori'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder('Deskripsi singkat tentang fasilitas ini...')
                            ->columnSpanFull(),
                    ])->columns(2),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Foto')->width(80)->height(60),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('category')->label('Kategori')->badge()->color('info'),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
                TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->options([
                    'akademik' => 'Akademik', 'olahraga' => 'Olahraga',
                    'ibadah' => 'Ibadah', 'mahad' => "Ma'had",
                    'penunjang' => 'Penunjang', 'lainnya' => 'Lainnya',
                ]),
            ])
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFacilities::route('/'),
            'create' => CreateFacility::route('/create'),
            'edit'   => EditFacility::route('/{record}/edit'),
        ];
    }
}
