<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ServiceStandardResource\Pages;
use App\Models\ServiceStandard;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Standar Pelayanan';
    protected static ?string $navigationGroup = 'Layanan & PPID';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informasi Layanan')->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Pelayanan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Legalisir Ijazah'),

                        Forms\Components\RichEditor::make('complaint_handling')
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

                    Forms\Components\Section::make('Persyaratan')->schema([
                        Forms\Components\Repeater::make('requirements')
                            ->label('Daftar Persyaratan')
                            ->schema([
                                Forms\Components\Grid::make(12)->schema([
                                    Forms\Components\TextInput::make('no')
                                        ->label('No.')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('komponen')
                                        ->label('Komponen')
                                        ->required()
                                        ->columnSpan(4),

                                    Forms\Components\Textarea::make('uraian')
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


                    Forms\Components\Section::make('Prosedur')->schema([
                        Forms\Components\Repeater::make('procedures')
                            ->label('Daftar Prosedur')
                            ->schema([
                                Forms\Components\Grid::make(12)->schema([
                                    Forms\Components\TextInput::make('no')
                                        ->label('No.')
                                        ->numeric()
                                        ->required()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('komponen')
                                        ->label('Komponen')
                                        ->required()
                                        ->columnSpan(4),

                                    Forms\Components\Textarea::make('uraian')
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

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Parameter Layanan')->schema([
                        Forms\Components\TextInput::make('service_time')
                            ->label('Waktu Pelayanan')
                            ->placeholder('Contoh: 1 Hari Kerja'),

                        Forms\Components\TextInput::make('cost')
                            ->label('Biaya / Tarif')
                            ->placeholder('Contoh: Gratis'),

                        Forms\Components\TextInput::make('product')
                            ->label('Produk Layanan')
                            ->placeholder('Contoh: Dokumen legalisir'),
                    ]),

                    Forms\Components\Section::make('Pengaturan')->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil akan tampil lebih dulu.'),

                        Forms\Components\Toggle::make('is_active')
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
            Tables\Columns\TextColumn::make('name')->label('Nama Pelayanan')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('service_time')->label('Waktu'),
            Tables\Columns\TextColumn::make('cost')->label('Biaya'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListServiceStandards::route('/'), 'create' => Pages\CreateServiceStandard::route('/create'), 'edit' => Pages\EditServiceStandard::route('/{record}/edit')];
    }
}
