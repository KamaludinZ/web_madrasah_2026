<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ServiceStandardResource\Pages\ListServiceStandards;
use App\Filament\Resources\ServiceStandardResource\Pages\CreateServiceStandard;
use App\Filament\Resources\ServiceStandardResource\Pages\EditServiceStandard;
use App\Filament\Resources\ServiceStandardResource\Pages;
use App\Models\ServiceStandard;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class ServiceStandardResource extends Resource {
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model = ServiceStandard::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Standar Pelayanan';
    protected static string|\UnitEnum|null $navigationGroup = 'Layanan & PPID';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema {
        return $schema->components([
            Grid::make(3)->schema([
                Group::make()->schema([
                    Section::make('Informasi Layanan')->schema([
                        TextInput::make('name')
                            ->label('Nama Pelayanan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Legalisir Ijazah'),

                        RichEditor::make('complaint_handling')
                            ->label('Pengelolaan Pengaduan')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList',
                                'h2', 'h3',
                                'link', 'blockquote',
                                'undo', 'redo',
                            ])
                            ->helperText('Isi mekanisme penanganan komplain secara ringkas dan jelas.'),
                    ])->columns(1),

                    Section::make('Persyaratan')->schema([
                        Repeater::make('requirements')
                            ->label('Daftar Persyaratan')
                            ->schema([
                                Grid::make(12)->schema([
                                    TextInput::make('no')
                                        ->label('No.')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('komponen')
                                        ->label('Komponen')
                                        ->required()
                                        ->columnSpan(4),

                                    Textarea::make('uraian')
                                        ->label('Uraian')
                                        ->rows(2)
                                        ->required()
                                        ->columnSpan(6),
                                ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => filled($state['komponen'] ?? null)
                                ? 'Persyaratan: ' . $state['komponen']
                                : 'Persyaratan Baru')
                            ->defaultItems(1)
                            ->collapsed()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->addActionLabel('Tambah Persyaratan'),
                    ]),


                    Section::make('Prosedur')->schema([
                        Repeater::make('procedures')
                            ->label('Daftar Prosedur')
                            ->schema([
                                Grid::make(12)->schema([
                                    TextInput::make('no')
                                        ->label('No.')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('komponen')
                                        ->label('Komponen')
                                        ->required()
                                        ->columnSpan(4),

                                    Textarea::make('uraian')
                                        ->label('Uraian')
                                        ->rows(2)
                                        ->required()
                                        ->columnSpan(6),
                                ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => filled($state['komponen'] ?? null)
                                ? 'Prosedur: ' . $state['komponen']
                                : 'Prosedur Baru')
                            ->defaultItems(1)
                            ->collapsed()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->addActionLabel('Tambah Prosedur'),
                    ]),

                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Parameter Layanan')->schema([
                        TextInput::make('service_time')
                            ->label('Waktu Pelayanan')
                            ->placeholder('Contoh: 1 Hari Kerja'),

                        TextInput::make('cost')
                            ->label('Biaya / Tarif')
                            ->placeholder('Contoh: Gratis'),

                        TextInput::make('product')
                            ->label('Produk Layanan')
                            ->placeholder('Contoh: Dokumen legalisir'),
                    ]),

                    Section::make('Pengaturan')->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil akan tampil lebih dulu.'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Aktifkan agar layanan tampil di halaman publik.'),
                    ]),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            TextColumn::make('name')->label('Nama Pelayanan')->searchable()->sortable(),
            TextColumn::make('service_time')->label('Waktu'),
            TextColumn::make('cost')->label('Biaya'),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([EditAction::make(), DeleteAction::make()])
        ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => ListServiceStandards::route('/'), 'create' => CreateServiceStandard::route('/create'), 'edit' => EditServiceStandard::route('/{record}/edit')];
    }
}
