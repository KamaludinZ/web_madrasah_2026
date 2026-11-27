<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Exception;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Throwable;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\NewsResource\Pages\ListNews;
use App\Filament\Resources\NewsResource\Pages\CreateNews;
use App\Filament\Resources\NewsResource\Pages\EditNews;
use App\Filament\Resources\NewsResource\Pages;
use App\Models\Category;
use App\Models\GalleryPhoto;
use App\Models\GalleryPost;
use App\Models\News;
use App\Models\User;
use App\Services\AiService;
use App\Services\SubscriptionMailerService;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use App\Filament\Resources\Concerns\HasRoleAccess;

class NewsResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor', 'contributor', 'moderator']);
    }

    /** Role yang boleh menerbitkan/mengarsipkan berita (menyetujui kiriman kontributor). */
    public static function canPublish(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    /** Moderator (tanpa role konten lain) hanya boleh melihat berita. */
    protected static function isReadOnlyUser(): bool
    {
        return ! static::currentUserHasRole(['editor_in_chief', 'editor', 'contributor']);
    }

    /** Kontributor tanpa role editor: hanya boleh mengelola berita miliknya yang belum terbit. */
    protected static function isContributorOnly(): bool
    {
        return ! static::canPublish() && static::currentUserHasRole(['contributor']);
    }

    public static function canCreate(): bool
    {
        return ! static::isReadOnlyUser();
    }

    public static function canEdit(Model $record): bool
    {
        if (static::isReadOnlyUser()) {
            return false;
        }

        if (static::isContributorOnly()) {
            return (int) $record->user_id === (int) Auth::id()
                && in_array($record->status, ['draft', 'review'], true);
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::canPublish();
    }

    /** Opsi status sesuai hak akses pengguna saat ini. */
    public static function statusOptions(): array
    {
        $options = [
            'draft' => 'Draf',
            'review' => 'Review',
            'published' => 'Publikasi',
            'archived' => 'Arsip',
        ];

        return static::canPublish() ? $options : array_intersect_key($options, array_flip(['draft', 'review']));
    }

    /**
     * Terapkan aturan hak akses di sisi server (form di browser bisa
     * dimanipulasi): kontributor tidak bisa menerbitkan dan tidak bisa
     * mengganti penulis.
     */
    public static function enforcePermissions(array $data, ?News $record = null): array
    {
        if (! static::canPublish() && ! in_array($data['status'] ?? 'draft', ['draft', 'review'], true)) {
            $data['status'] = 'review';
        }

        if (! static::currentUserHasRole(['editor_in_chief'])) {
            $data['user_id'] = $record?->user_id ?? Auth::id();
        }

        return $data;
    }

    protected static ?string $model = News::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Berita';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Berita';
    protected static ?string $pluralModelLabel = 'Berita';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                // Main Content
                Group::make()->schema([
                    Section::make('Konten Berita')->schema([
                        TextInput::make('title')
                            ->label('Judul Berita')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, Set $set) => $set('slug', Str::slug((string) $state))),

                        TinyEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->profile('full')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('news-content')
                            ->fileAttachmentsVisibility('public')
                            // Gambar di isi berita diperkecil (≤1920px, WEBP); GIF
                            // disimpan apa adanya agar animasinya tidak hilang.
                            ->saveUploadedFileAttachmentUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string {
                                if ($file->getMimeType() !== 'image/gif') {
                                    $path = 'news-content/' . Str::uuid() . '.webp';
                                    if (\App\Support\ImageOptimizer::toWebp($file->getRealPath(), Storage::disk('public')->path($path), \App\Support\ImageOptimizer::maxDimensionFor($path))) {
                                        return $path;
                                    }
                                }

                                return $file->store('news-content', 'public');
                            })
                            ->minHeight(420)
                            ->resize(true)
                            ->toolbarMode('wrap')
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->readOnly()
                            ->dehydrated()
                            ->unique(News::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Slug dibuat otomatis dari judul berita.'),

                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat berita (maks. 500 karakter), bisa diedit manual.'),

                        Actions::make([
                            Action::make('generate_excerpt')
                                ->label('Generate Ringkasan')
                                ->icon('heroicon-o-sparkles')
                                ->color('gray')
                                ->action(function (Get $get, Set $set) {
                                    $content = trim(strip_tags((string) ($get('content') ?? '')));

                                    if ($content === '') {
                                        Notification::make()
                                            ->title('Isi berita masih kosong')
                                            ->body('Isi konten berita terlebih dahulu untuk generate ringkasan.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $excerpt = Str::limit($content, 220, '...');
                                    $set('excerpt', $excerpt);
                                    Notification::make()
                                        ->title('Ringkasan berhasil digenerate')
                                        ->success()
                                        ->send();
                                }),
                        ])->alignStart(),
                    ]),

                    Section::make('SEO & Meta')->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Optimal: 50-60 karakter'),

                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Optimal: 150-160 karakter'),

                        Actions::make([
                            Action::make('generate_seo')
                                ->label('Generate SEO dengan AI')
                                ->icon('heroicon-o-sparkles')
                                ->color('warning')
                                ->action(function (Get $get, Set $set) {
                                    try {
                                        $aiService = app(AiService::class);
                                        $seo = $aiService->generateSeo((string) $get('title'), filled($get('excerpt')) ? (string) $get('excerpt') : (string) $get('content'));
                                        $set('meta_title', $seo['meta_title']);
                                        $set('meta_description', $seo['meta_description']);
                                        if (empty($get('slug'))) {
                                            $set('slug', $seo['slug']);
                                        }
                                        Notification::make()->title('SEO berhasil digenerate!')->success()->send();
                                    } catch (Exception $e) {
                                        Notification::make()->title('Gagal generate SEO: ' . $e->getMessage())->danger()->send();
                                    }
                                }),

                            Action::make('generate_content')
                                ->label('Buat Draf dengan AI')
                                ->icon('heroicon-o-cpu-chip')
                                ->color('info')
                                ->form([
                                    Textarea::make('points')
                                        ->label('Poin-poin kegiatan')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder("Contoh:\n- Kegiatan lomba matematika\n- Diikuti 50 siswa\n- Juara 1 tingkat kota"),
                                ])
                                ->action(function (array $data, Set $set) {
                                    try {
                                        $aiService = app(AiService::class);
                                        $draft = $aiService->generateDraft($data['points']);
                                        $set('content', $draft['content']);
                                        $set('excerpt', $draft['excerpt']);
                                        Notification::make()->title('Draf berhasil dibuat!')->success()->send();
                                    } catch (Exception $e) {
                                        Notification::make()->title('Gagal membuat draf: ' . $e->getMessage())->danger()->send();
                                    }
                                }),

                            Action::make('fix_grammar')
                                ->label('Perbaiki Tata Bahasa')
                                ->icon('heroicon-o-pencil-square')
                                ->color('success')
                                ->action(function (Get $get, Set $set) {
                                    $content = $get('content');

                                    if (blank($content)) {
                                        Notification::make()
                                            ->title('Isi berita masih kosong')
                                            ->body('Isi konten berita terlebih dahulu sebelum memperbaiki tata bahasa.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $aiService = app(AiService::class);
                                        $fixed = $aiService->fixGrammar((string) $content);
                                        $set('content', $fixed);
                                        Notification::make()->title('Tata bahasa berhasil diperbaiki!')->success()->send();
                                    } catch (Exception $e) {
                                        Notification::make()->title('Gagal memperbaiki: ' . $e->getMessage())->danger()->send();
                                    }
                                }),
                        ]),
                    ]),
                ])->columnSpan(2),

                // Sidebar
                Group::make()->schema([
                    Section::make('Publikasi')->schema([
                        Select::make('user_id')
                            ->label('Penulis')
                            ->options(fn() => User::query()
                                ->where('is_active', true)
                                ->where('is_suspended', false)
                                ->whereNull('deleted_at')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->default(fn() => Auth::id())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->disabled(function (): bool {
                                $user = Auth::user();
                                if (! $user) {
                                    return true;
                                }
                                // Hanya super_admin & editor_in_chief yang bisa ganti penulis
                                return ! $user->hasAnyRole(['super_admin', 'editor_in_chief']);
                            })
                            ->dehydrated(true),

                        Select::make('status')
                            ->label('Status')
                            ->options(fn () => static::statusOptions())
                            ->default('draft')
                            ->helperText(fn () => static::canPublish() ? null : 'Pilih "Review" agar berita diperiksa dan diterbitkan oleh editor.')
                            ->required(),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->default(now())
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->format('Y-m-d H:i:s')
                            ->minutesStep(1)
                            ->closeOnDateSelection(false),

                        Toggle::make('is_featured')
                            ->label('Berita Unggulan'),

                        Toggle::make('allow_comments')
                            ->label('Izinkan Komentar')
                            ->default(true),
                    ]),

                    Section::make('Kategori & Tag')->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        TagsInput::make('tags')
                            ->label('Tag')
                            ->required()
                            ->placeholder('Ketik tag lalu tekan Enter')
                            ->helperText('Wajib diisi minimal 1 tag. Tekan Enter atau koma untuk menambah tag.')
                            ->afterStateHydrated(function (TagsInput $component, $record) {
                                if ($record) {
                                    try {
                                        $component->state($record->tags->pluck('name')->toArray());
                                    } catch (Throwable) {
                                        $component->state([]);
                                    }
                                }
                            }),

                        Placeholder::make('tag_wajib_info')
                            ->label('Tag Wajib per Halaman')
                            // Inline style: panel Filament 5 tanpa tema Tailwind khusus,
                            // jadi kelas warna utilitas tidak ikut ter-compile.
                            ->content(new HtmlString(
                                '<div style="font-size:.75rem;line-height:1.4">' .
                                '<p style="font-weight:600;margin-bottom:.5rem">Gunakan tag berikut agar berita muncul di halaman yang sesuai:</p>' .
                                '<div style="display:flex;flex-wrap:wrap;gap:.375rem">' .
                                collect([
                                    "Ma'had"            => '#7e22ce',
                                    'Adiwiyata'         => '#15803d',
                                    'Zona Integritas'   => '#1d4ed8',
                                    'PPDB'              => '#c2410c',
                                    'Unggulan'          => '#a16207',
                                    'PSB'               => '#b91c1c',
                                    "Program Ma'had"    => '#4338ca',
                                    'Prestasi Siswa'    => '#0f766e',
                                    'Prestasi Madrasah' => '#0e7490',
                                    "Prestasi Ma'had"   => '#6d28d9',
                                    'Prestasi Santri'   => '#be185d',
                                ])->map(fn ($color, $tag) =>
                                    '<span style="display:inline-flex;align-items:center;padding:.125rem .5rem;border-radius:9999px;font-weight:500;'
                                    . 'color:' . $color . ';background:color-mix(in srgb,' . $color . ' 12%,transparent);border:1px solid color-mix(in srgb,' . $color . ' 30%,transparent)">'
                                    . e($tag) . '</span>'
                                )->implode('') .
                                '</div></div>'
                            )),
                    ]),

                    Section::make('Gambar Utama')->schema([
                        UploadImageHelper::webpFileUpload('thumbnail', 'Gambar Utama', 'news/thumbnails')
                            ->imageEditor()
                            ->helperText('Format input: JPG/PNG/WEBP. Output otomatis WebP. Maks: 5MB'),

                        TextInput::make('thumbnail_caption')
                            ->label('Keterangan Gambar'),

                        TextInput::make('source')
                            ->label('Sumber Berita'),
                    ]),
                ])->columnSpan(1),
            ]),

            // ===== POSTING GALERI (full width, di bawah grid utama) =====
            Section::make('Posting Galeri')
                ->description('Opsional: tambahkan galeri foto yang akan tampil di bawah berita ini. Foto akan dikonversi ke format WebP secara otomatis.')
                ->icon('heroicon-o-photo')
                ->collapsible()
                ->schema([
                    TextInput::make('gallery_title')
                        ->label('Judul Galeri')
                        ->maxLength(255)
                        ->placeholder('Contoh: Dokumentasi Kegiatan Lomba Matematika 2026')
                        ->helperText('Isi judul galeri untuk mengaktifkan posting galeri foto.'),

                    Textarea::make('gallery_description')
                        ->label('Deskripsi Galeri (opsional)')
                        ->rows(2)
                        ->maxLength(500),

                    // Foto tersimpan: hanya tampil saat edit & ada foto
                    View::make('filament.components.gallery-manager')
                        ->viewData(fn ($record) => [
                            'galleryPost' => $record
                                ? GalleryPost::where('news_id', $record->id)->with('photos')->first()
                                : null,
                            'statePath' => 'data.gallery_photos_to_delete',
                        ])
                        ->hidden(fn ($record) => ! $record || ! GalleryPost::where('news_id', $record->id)->whereHas('photos')->exists()),

                    // Hidden: array ID foto yang akan dihapus (diisi Alpine.js via $wire.set)
                    Hidden::make('gallery_photos_to_delete')
                        ->dehydrated(false),

                    // Upload foto baru
                    FileUpload::make('gallery_photos_upload')
                        ->label(fn ($record) => $record && GalleryPost::where('news_id', $record->id)->whereHas('photos')->exists()
                            ? 'Tambah Foto Baru'
                            : 'Upload Foto Galeri')
                        ->image()
                        ->multiple()
                        ->maxFiles(30)
                        ->disk('public')
                        ->directory('gallery/temp')
                        ->visibility('public')
                        ->maxSize(5120)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                        ->helperText('Maksimal 30 foto sekaligus. Format: JPG/PNG/WEBP. Maks 5MB per foto. Semua foto akan dikonversi ke WebP.')
                        ->reorderable()
                        ->appendFiles()
                        ->panelLayout('grid')
                        ->dehydrated(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Gambar')
                    ->circular(false)
                    ->width(80)
                    ->height(50),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('success'),

                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'draft'     => 'gray',
                        'review'    => 'warning',
                        'published' => 'success',
                        'archived'  => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft'     => 'Draf',
                        'review'    => 'Review',
                        'published' => 'Publikasi',
                        'archived'  => 'Arsip',
                        default     => $state,
                    }),

                TextColumn::make('view_count')
                    ->label('Dibaca')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'review' => 'Review',
                        'published' => 'Publikasi',
                        'archived' => 'Arsip',
                    ]),
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('publish')
                    ->label('Publikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(News $record) => $record->status !== 'published' && static::canPublish())
                    ->action(function (News $record): void {
                        $record->update(['status' => 'published', 'published_at' => now()]);
                        app(SubscriptionMailerService::class)->queueIfPublished($record);
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('publish_bulk')
                        ->label('Publikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->visible(fn () => static::canPublish())
                        ->action(function ($records): void {
                            $records->each(function (News $record): void {
                                $record->update(['status' => 'published', 'published_at' => now()]);
                                app(SubscriptionMailerService::class)->queueIfPublished($record);
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Contributor hanya bisa lihat berita milik sendiri
        $user = Auth::user();
        if ($user && $user->hasAnyRole(['contributor'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
        ];
    }
}
