<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Filament\Resources\Concerns\HasRoleAccess;

class CategoryResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Category::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategori';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Kategori')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn ($state) => Str::slug((string) $state))
                        ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                        ->helperText('Huruf kecil, angka, dan tanda hubung (-). Contoh: berita-sekolah'),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Tampilan & Urutan')
                ->schema([
                    Forms\Components\ColorPicker::make('color')
                        ->label('Warna Badge')
                        ->default('#1a7a3c')
                        ->helperText('Warna yang ditampilkan pada badge kategori.'),

                    Forms\Components\TextInput::make('order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0)
                        ->helperText('Angka lebih kecil tampil lebih awal.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Kategori Aktif')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->inline(false),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')->label('Warna'),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray'),
                Tables\Columns\TextColumn::make('news_count')->label('Berita')->counts('news')->badge()->color('info'),
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
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
