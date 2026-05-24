<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\Category;
use App\Models\News;
use App\Services\AiService;
use Awcodes\FilamentTiptapEditor\TiptapEditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
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
                            ->afterStateUpdated(fn($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->unique(News::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat berita (maks. 500 karakter)'),

                        TiptapEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->columnSpanFull(),
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
                                    try {
                                        $aiService = app(AiService::class);
                                        $fixed = $aiService->fixGrammar($get('content'));
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
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draf',
                                'review' => 'Review',
                                'published' => 'Publikasi',
                                'archived' => 'Arsip',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->default(now()),

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
                            ->preload(),

                        Forms\Components\SpatieTagsInput::make('tags')
                            ->label('Tag')
                            ->helperText('Tekan Enter untuk menambah tag'),
                    ]),

                    Forms\Components\Section::make('Gambar Utama')->schema([
                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('Gambar Utama')
                            ->image()
                            ->imageEditor()
                            ->directory('news/thumbnails')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('Format: JPG, PNG, WebP. Maks: 5MB'),

                        Forms\Components\TextInput::make('thumbnail_caption')
                            ->label('Keterangan Gambar'),

                        Forms\Components\TextInput::make('source')
                            ->label('Sumber Berita'),
                    ]),
                ])->columnSpan(1),
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

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'review',
                        'success' => 'published',
                        'danger' => 'archived',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft' => 'Draf',
                        'review' => 'Review',
                        'published' => 'Publikasi',
                        'archived' => 'Arsip',
                        default => $state,
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
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label('Publikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(News $record) => $record->status !== 'published')
                    ->action(fn(News $record) => $record->update(['status' => 'published', 'published_at' => now()])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish_bulk')
                        ->label('Publikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['status' => 'published', 'published_at' => now()])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Role-based filtering
        $user = auth()->user();
        if ($user->hasRole('journalist') || $user->hasRole('contributor')) {
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
