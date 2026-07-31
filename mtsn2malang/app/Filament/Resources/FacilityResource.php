<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon  = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Sarana & Prasarana';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Sarana Prasarana';
    protected static ?string $pluralModelLabel = 'Sarana & Prasarana';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto Fasilitas')
                    ->schema([
                        UploadImageHelper::webpFileUpload('image', 'Upload Foto', 'facilities', true)
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
                            ->label('Tampilkan')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Informasi Fasilitas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Fasilitas')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Laboratorium IPA, Perpustakaan, Masjid'),

                        Forms\Components\Select::make('category')
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

                        Forms\Components\Textarea::make('description')
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
                Tables\Columns\ImageColumn::make('image')->label('Foto')->width(80)->height(60),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge()->color('info'),
                Tables\Columns\IconColumn::make('is_active')->label('Tampil')->boolean(),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options([
                    'akademik' => 'Akademik', 'olahraga' => 'Olahraga',
                    'ibadah' => 'Ibadah', 'mahad' => "Ma'had",
                    'penunjang' => 'Penunjang', 'lainnya' => 'Lainnya',
                ]),
            ])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit'   => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
