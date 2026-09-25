<?php

namespace App\Filament\Resources;

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
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
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

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
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

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
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
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Berita';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Berita';
    protected static ?string $pluralModelLabel = 'Berita';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                // Main Content
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Konten Berita')->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Berita')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('slug', Str::slug((string) $state))),

                        TiptapEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->profile('none')
                            ->tools([
                                'heading', 'bullet-list', 'ordered-list', 'blockquote', 'hr', '|',
                                'bold', 'italic', 'underline', 'strike', 'color', 'highlight', '|',
                                'align-left', 'align-center', 'align-right', 'align-justify', '|',
                                'link', 'media', 'table', '|', 'code-block', 'source',
                            ])
                            ->output(TiptapOutput::Html)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->readOnly()
                            ->dehydrated()
                            ->unique(News::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Slug dibuat otomatis dari judul berita.'),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat berita (maks. 500 karakter), bisa diedit manual.'),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_excerpt')
                                ->label('Generate Ringkasan')
                                ->icon('heroicon-o-sparkles')
                                ->color('gray')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
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

                    Forms\Components\Section::make('SEO & Meta')->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Optimal: 50-60 karakter'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Optimal: 150-160 karakter'),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_seo')
                                ->label('Generate SEO dengan AI')
                                ->icon('heroicon-o-sparkles')
                                ->color('warning')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    try {
                                        $aiService = app(AiService::class);
                                        $seo = $aiService->generateSeo($get('title'), $get('excerpt') ?? $get('content'));
                                        $set('meta_title', $seo['meta_title']);
                                        $set('meta_description', $seo['meta_description']);
                                        if (empty($get('slug'))) {
                                            $set('slug', $seo['slug']);
                                        }
                                        Notification::make()->title('SEO berhasil digenerate!')->success()->send();
                                    } catch (\Exception $e) {
                                        Notification::make()->title('Gagal generate SEO: ' . $e->getMessage())->danger()->send();
                                    }
                                }),

                            Forms\Components\Actions\Action::make('generate_content')
                                ->label('Buat Draf dengan AI')
                                ->icon('heroicon-o-cpu-chip')
                                ->color('info')
                                ->form([
                                    Forms\Components\Textarea::make('points')
                                        ->label('Poin-poin kegiatan')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder("Contoh:\n- Kegiatan lomba matematika\n- Diikuti 50 siswa\n- Juara 1 tingkat kota"),
                                ])
                                ->action(function (array $data, Forms\Set $set) {
                                    try {
                                        $aiService = app(AiService::class);
                                        $draft = $aiService->generateDraft($data['points']);
                                        $set('content', $draft['content']);
                                        $set('excerpt', $draft['excerpt']);
                                        Notification::make()->title('Draf berhasil dibuat!')->success()->send();
                                    } catch (\Exception $e) {
                                        Notification::make()->title('Gagal membuat draf: ' . $e->getMessage())->danger()->send();
                                    }
                                }),

                            Forms\Components\Actions\Action::make('fix_grammar')
                                ->label('Perbaiki Tata Bahasa')
                                ->icon('heroicon-o-pencil-square')
                                ->color('success')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
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
                                    } catch (\Exception $e) {
                                        Notification::make()->title('Gagal memperbaiki: ' . $e->getMessage())->danger()->send();
                                    }
                                }),
                        ]),
                    ]),
                ])->columnSpan(2),

                // Sidebar
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Publikasi')->schema([
                        Forms\Components\Select::make('user_id')
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

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(fn () => static::statusOptions())
                            ->default('draft')
                            ->helperText(fn () => static::canPublish() ? null : 'Pilih "Review" agar berita diperiksa dan diterbitkan oleh editor.')
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->default(now())
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->format('Y-m-d H:i:s')
                            ->minutesStep(1)
                            ->closeOnDateSelection(false),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Berita Unggulan'),

                        Forms\Components\Toggle::make('allow_comments')
                            ->label('Izinkan Komentar')
                            ->default(true),
                    ]),

                    Forms\Components\Section::make('Kategori & Tag')->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Tag')
                            ->required()
                            ->placeholder('Ketik tag lalu tekan Enter')
                            ->helperText('Wajib diisi minimal 1 tag. Tekan Enter atau koma untuk menambah tag.')
                            ->afterStateHydrated(function (Forms\Components\TagsInput $component, $record) {
                                if ($record) {
                                    try {
                                        $component->state($record->tags->pluck('name')->toArray());
                                    } catch (\Throwable) {
                                        $component->state([]);
                                    }
                                }
                            }),

                        Forms\Components\Placeholder::make('tag_wajib_info')
                            ->label('Tag Wajib per Halaman')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">' .
                                '<p class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Gunakan tag berikut agar berita muncul di halaman yang sesuai:</p>' .
                                '<div class="flex flex-wrap gap-1.5">' .
                                collect([
                                    "Ma'had"            => 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
                                    'Adiwiyata'         => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                    'Zona Integritas'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                    'PPDB'              => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                                    'Unggulan'          => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                    'PSB'               => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                    "Program Ma'had"    => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
                                    'Prestasi Siswa'    => 'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300',
                                    'Prestasi Madrasah' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300',
                                    "Prestasi Ma'had"   => 'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300',
                                    'Prestasi Santri'   => 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300',
                                ])->map(fn($cls, $tag) =>
                                    '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' . $cls . '">' . $tag . '</span>'
                                )->implode('') .
                                '</div></div>'
                            )),
                    ]),

                    Forms\Components\Section::make('Gambar Utama')->schema([
                        UploadImageHelper::webpFileUpload('thumbnail', 'Gambar Utama', 'news/thumbnails')
                            ->imageEditor()
                            ->helperText('Format input: JPG/PNG/WEBP. Output otomatis WebP. Maks: 5MB'),

                        Forms\Components\TextInput::make('thumbnail_caption')
                            ->label('Keterangan Gambar'),

                        Forms\Components\TextInput::make('source')
                            ->label('Sumber Berita'),
                    ]),
                ])->columnSpan(1),
            ]),

            // ===== POSTING GALERI (full width, di bawah grid utama) =====
            Forms\Components\Section::make('Posting Galeri')
                ->description('Opsional: tambahkan galeri foto yang akan tampil di bawah berita ini. Foto akan dikonversi ke format WebP secara otomatis.')
                ->icon('heroicon-o-photo')
                ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('gallery_title')
                        ->label('Judul Galeri')
                        ->maxLength(255)
                        ->placeholder('Contoh: Dokumentasi Kegiatan Lomba Matematika 2026')
                        ->helperText('Isi judul galeri untuk mengaktifkan posting galeri foto.'),

                    Forms\Components\Textarea::make('gallery_description')
                        ->label('Deskripsi Galeri (opsional)')
                        ->rows(2)
                        ->maxLength(500),

                    // Foto tersimpan: hanya tampil saat edit & ada foto
                    Forms\Components\View::make('filament.components.gallery-manager')
                        ->label('Foto Tersimpan')
                        ->viewData(fn ($record) => [
                            'galleryPost' => $record
                                ? GalleryPost::where('news_id', $record->id)->with('photos')->first()
                                : null,
                            'statePath' => 'data.gallery_photos_to_delete',
                        ])
                        ->hidden(fn ($record) => ! $record || ! GalleryPost::where('news_id', $record->id)->whereHas('photos')->exists()),

                    // Hidden: array ID foto yang akan dihapus (diisi Alpine.js via $wire.set)
                    Forms\Components\Hidden::make('gallery_photos_to_delete')
                        ->dehydrated(false),

                    // Upload foto baru
                    Forms\Components\FileUpload::make('gallery_photos_upload')
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
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Gambar')
                    ->circular(false)
                    ->width(80)
                    ->height(50),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Penulis')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
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

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Dibaca')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'review' => 'Review',
                        'published' => 'Publikasi',
                        'archived' => 'Arsip',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(fn () => \App\Models\Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label('Publikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(News $record) => $record->status !== 'published' && static::canPublish())
                    ->action(function (News $record): void {
                        $record->update(['status' => 'published', 'published_at' => now()]);
                        app(SubscriptionMailerService::class)->sendNewsPublished($record);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish_bulk')
                        ->label('Publikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->visible(fn () => static::canPublish())
                        ->action(function ($records): void {
                            $records->each(function (News $record): void {
                                $record->update(['status' => 'published', 'published_at' => now()]);
                                app(SubscriptionMailerService::class)->sendNewsPublished($record);
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
