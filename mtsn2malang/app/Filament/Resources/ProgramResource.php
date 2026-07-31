<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Program';
    protected static ?string $navigationGroup = 'Program';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Program')
                ->description('Atur konten yang akan ditampilkan pada halaman Program Madrasah atau Ma\'had.')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('Jenis Program')
                                ->options([
                                    'unggulan' => 'Program Unggulan (Madrasah)',
                                    'mahad' => "Program Ma'had",
                                ])
                                ->required()
                                ->native(false),

                            Forms\Components\TextInput::make('order')
                                ->label('Urutan Tampil')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->helperText('Angka lebih kecil akan tampil lebih dahulu.'),
                        ]),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Program')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Tahfidz Intensif Ma\'had'),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Singkat')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder('Tuliskan ringkasan manfaat dan fokus utama program ini.'),

                    Forms\Components\TextInput::make('link')
                        ->label('Tautan Detail (Opsional)')
                        ->url()
                        ->nullable()
                        ->maxLength(255)
                        ->placeholder('https://contoh.com/detail-program')
                        ->helperText('Jika diisi, tombol "Lihat Detail" akan muncul di kartu program.'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Media & Status')
                ->schema([
                    UploadImageHelper::webpFileUpload('cover', 'Gambar Cover', 'programs', false)
                        ->helperText('Opsional. Jika kosong, sistem menggunakan gambar default.'),

                    Forms\Components\Toggle::make('is_active')
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
                Tables\Columns\ImageColumn::make('cover')
                    ->label('Cover')
                    ->square(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Program')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('type')
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

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
