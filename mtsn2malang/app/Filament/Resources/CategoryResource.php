<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategori';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn ($state) => Str::slug((string) $state))
                        ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                        ->helperText('Huruf kecil, angka, dan tanda hubung (-). Contoh: berita-sekolah'),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Tampilan & Urutan')
                ->schema([
                    ColorPicker::make('color')
                        ->label('Warna Badge')
                        ->default('#1a7a3c')
                        ->helperText('Warna yang ditampilkan pada badge kategori.'),

                    TextInput::make('order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0)
                        ->helperText('Angka lebih kecil tampil lebih awal.'),

                    Toggle::make('is_active')
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
                ColorColumn::make('color')->label('Warna'),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('slug')->label('Slug')->color('gray'),
                TextColumn::make('news_count')->label('Berita')->counts('news')->badge()->color('info'),
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
            'index'  => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit'   => EditCategory::route('/{record}/edit'),
        ];
    }
}
