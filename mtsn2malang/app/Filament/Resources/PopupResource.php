<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\PopupResource\Pages;
use App\Models\Popup;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PopupResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model           = Popup::class;
    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Popup & Pengumuman';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel      = 'Popup';
    protected static ?string $pluralModelLabel = 'Popup & Pengumuman';

    public static function canAccess(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor', 'moderator']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Group::make()->schema([

                // ── Section: Pengaturan Umum ──────────────────────────────
                Forms\Components\Section::make('Pengaturan Popup')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Popup')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Pengumuman PPDB 2025/2026')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Tipe Popup')
                            ->options([
                                'text'   => '📝 Teks / Pengumuman (Rich Editor)',
                                'slider' => '🖼️ Slider Gambar',
                            ])
                            ->default('text')
                            ->required()
                            ->live()
                            ->helperText('Tipe "Teks" untuk pengumuman/informasi. Tipe "Slider" untuk galeri gambar promosi.')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('Aktifkan Popup')
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger')
                                ->inline(false),

                            Forms\Components\Toggle::make('show_once')
                                ->label('Tampil Sekali per Sesi')
                                ->default(true)
                                ->onColor('info')
                                ->offColor('warning')
                                ->helperText('Jika aktif, popup tidak muncul lagi setelah pengunjung menutupnya (dalam satu sesi browser).')
                                ->inline(false),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Mulai Tampil')
                                ->nullable()
                                ->helperText('Kosongkan = langsung aktif'),

                            Forms\Components\DatePicker::make('end_date')
                                ->label('Berhenti Tampil')
                                ->nullable()
                                ->helperText('Kosongkan = tidak ada batas'),

                            Forms\Components\TextInput::make('order')
                                ->label('Urutan')
                                ->numeric()
                                ->default(0)
                                ->helperText('Angka kecil tampil lebih dulu'),
                        ]),
                    ]),

                // ── Section: Item Popup ───────────────────────────────────
                Forms\Components\Section::make('Konten Popup')
                    ->description(fn (Get $get) => match ($get('type')) {
                        'slider' => 'Tambahkan satu atau lebih gambar untuk ditampilkan sebagai slider.',
                        'text'   => 'Tulis konten pengumuman menggunakan rich editor.',
                        default  => 'Pilih tipe popup terlebih dahulu.',
                    })
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->orderColumn('order')
                            ->schema([

                                // ── Tipe item (hidden, auto-set berdasarkan tipe popup) ──
                                Forms\Components\Hidden::make('item_type')
                                    ->default(fn (Get $get) => $get('../../type') === 'slider' ? 'image' : 'text'),

                                // ── Gambar (hanya untuk slider) ──────────────────────────
                                UploadImageHelper::webpFileUpload('image', 'Gambar', 'popups', false)
                                    ->helperText('Rekomendasi: 1200×675 px (rasio 16:9). Otomatis dikonversi ke WebP.')
                                    ->visible(fn (Get $get) => $get('../../type') === 'slider'),

                                Forms\Components\TextInput::make('image_link')
                                    ->label('Link Klik Gambar (opsional)')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->helperText('URL tujuan saat gambar diklik. Kosongkan jika tidak ada.')
                                    ->visible(fn (Get $get) => $get('../../type') === 'slider'),

                                Forms\Components\TextInput::make('caption')
                                    ->label('Keterangan Gambar (opsional)')
                                    ->maxLength(255)
                                    ->placeholder('Keterangan singkat gambar...')
                                    ->visible(fn (Get $get) => $get('../../type') === 'slider'),

                                // ── Konten teks (hanya untuk text) ───────────────────────
                                Forms\Components\RichEditor::make('content')
                                    ->label('Isi Pengumuman')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline', 'strike',
                                        'h2', 'h3',
                                        'bulletList', 'orderedList',
                                        'link',
                                        'blockquote',
                                        'redo', 'undo',
                                    ])
                                    ->columnSpanFull()
                                    ->visible(fn (Get $get) => $get('../../type') === 'text'),

                            ])
                            ->addActionLabel(fn (Get $get) => $get('type') === 'slider' ? '+ Tambah Gambar' : '+ Tambah Konten')
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->maxItems(fn (Get $get) => $get('type') === 'text' ? 1 : 20)
                            ->columnSpanFull(),
                    ]),

            ])->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->start_date || $record->end_date
                        ? 'Berlaku: ' . ($record->start_date?->format('d/m/Y') ?? '∞') . ' – ' . ($record->end_date?->format('d/m/Y') ?? '∞')
                        : 'Berlaku: Selalu'
                    ),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'info'    => 'text',
                        'warning' => 'slider',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'text'   => '📝 Teks',
                        'slider' => '🖼️ Slider',
                        default  => $state,
                    }),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('show_once')
                    ->label('Sekali/Sesi')
                    ->boolean()
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('danger'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'text'   => 'Teks / Pengumuman',
                        'slider' => 'Slider Gambar',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada popup')
            ->emptyStateDescription('Buat popup pertama untuk menampilkan pengumuman atau slider gambar di halaman publik.')
            ->emptyStateIcon('heroicon-o-megaphone');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPopups::route('/'),
            'create' => Pages\CreatePopup::route('/create'),
            'edit'   => Pages\EditPopup::route('/{record}/edit'),
        ];
    }
}
