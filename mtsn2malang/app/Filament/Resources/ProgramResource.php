<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ProgramResource\Pages\ListPrograms;
use App\Filament\Resources\ProgramResource\Pages\CreateProgram;
use App\Filament\Resources\ProgramResource\Pages\EditProgram;
use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class ProgramResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model = Program::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Program';
    protected static string|\UnitEnum|null $navigationGroup = 'Program';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Program')
                ->description('Atur konten yang akan ditampilkan pada halaman Program Madrasah atau Ma\'had.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label('Jenis Program')
                                ->options([
                                    'unggulan' => 'Program Unggulan (Madrasah)',
                                    'mahad' => "Program Ma'had",
                                ])
                                ->required()
                                ->native(false),

                            TextInput::make('order')
                                ->label('Urutan Tampil')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->helperText('Angka lebih kecil akan tampil lebih dahulu.'),
                        ]),

                    TextInput::make('name')
                        ->label('Nama Program')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Tahfidz Intensif Ma\'had'),

                    Textarea::make('description')
                        ->label('Deskripsi Singkat')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder('Tuliskan ringkasan manfaat dan fokus utama program ini.'),

                    TextInput::make('link')
                        ->label('Tautan Detail (Opsional)')
                        ->url()
                        ->nullable()
                        ->maxLength(255)
                        ->placeholder('https://contoh.com/detail-program')
                        ->helperText('Jika diisi, tombol "Lihat Detail" akan muncul di kartu program.'),
                ])
                ->columns(1),

            Section::make('Media & Status')
                ->schema([
                    UploadImageHelper::webpFileUpload('cover', 'Gambar Cover', 'programs', false)
                        ->helperText('Opsional. Jika kosong, sistem menggunakan gambar default.'),

                    Toggle::make('is_active')
                        ->label('Aktif ditampilkan')
                        ->default(true),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('cover')
                    ->label('Cover')
                    ->square(),

                TextColumn::make('name')
                    ->label('Nama Program')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'unggulan' => 'Madrasah',
                        'mahad' => "Ma'had",
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unggulan' => 'success',
                        'mahad'    => 'info',
                        default    => 'gray',
                    }),

                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
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
            'index' => ListPrograms::route('/'),
            'create' => CreateProgram::route('/create'),
            'edit' => EditProgram::route('/{record}/edit'),
        ];
    }
}
