<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SocialMediaContentResource\Pages\ListSocialMediaContents;
use App\Filament\Resources\SocialMediaContentResource\Pages\CreateSocialMediaContent;
use App\Filament\Resources\SocialMediaContentResource\Pages\EditSocialMediaContent;
use App\Filament\Resources\SocialMediaContentResource\Pages;
use App\Models\Setting;
use App\Models\SocialMediaContent;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class SocialMediaContentResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model = SocialMediaContent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Konten Sosial Media';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten Sosial Media')
                ->schema([
                    Select::make('platform')
                        ->label('Platform')
                        ->options([
                            'youtube' => 'YouTube',
                            'instagram' => 'Instagram',
                            'tiktok' => 'TikTok',
                        ])
                        ->required(),

                    TextInput::make('title')
                        ->label('Judul Konten')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('post_url')
                        ->label('Link Post')
                        ->url()
                        ->required()
                        ->maxLength(255),

                    TextInput::make('embed_url')
                        ->label('Link Embed (Opsional)')
                        ->url()
                        ->maxLength(255),

                    UploadImageHelper::webpFileUpload('thumbnail', 'Thumbnail (Opsional)', 'social-media', false)
                        ->helperText('Jika thumbnail konten kosong, sistem akan menggunakan thumbnail default sesuai platform.'),

                    TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    CheckboxList::make('page_visibility')
                        ->label('Tampilkan pada halaman')
                        ->options([
                            'madrasah' => 'Madrasah',
                            'mahad' => "Ma'had",
                        ])
                        ->columns(2)
                        ->default(['madrasah'])
                        ->required()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (CheckboxList $component, ?SocialMediaContent $record) {
                            if (!$record) {
                                return;
                            }

                            $state = [];

                            if ($record->show_on_madrasah) {
                                $state[] = 'madrasah';
                            }

                            if ($record->show_on_mahad) {
                                $state[] = 'mahad';
                            }

                            $component->state($state);
                        })
                        ->afterStateUpdated(function ($state, Set $set) {
                            $values = is_array($state) ? $state : [];
                            $set('show_on_madrasah', in_array('madrasah', $values, true));
                            $set('show_on_mahad', in_array('mahad', $values, true));
                        }),

                    Hidden::make('show_on_madrasah')
                        ->default(true),

                    Hidden::make('show_on_mahad')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->defaultImageUrl(fn($record) => match ($record->platform) {
                        'youtube' => Setting::get('default_social_thumbnail_youtube') ? asset('storage/' . Setting::get('default_social_thumbnail_youtube')) : asset('images/program-placeholder.jpg'),
                        'instagram' => Setting::get('default_social_thumbnail_instagram') ? asset('storage/' . Setting::get('default_social_thumbnail_instagram')) : asset('images/program-placeholder.jpg'),
                        'tiktok' => Setting::get('default_social_thumbnail_tiktok') ? asset('storage/' . Setting::get('default_social_thumbnail_tiktok')) : asset('images/program-placeholder.jpg'),
                        default => asset('images/program-placeholder.jpg'),
                    })
                    ->square(),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'youtube'   => 'danger',
                        'instagram' => 'info',
                        'tiktok'    => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'youtube'   => 'YouTube',
                        'instagram' => 'Instagram',
                        'tiktok'    => 'TikTok',
                        default     => ucfirst($state),
                    }),

                TextColumn::make('page_target')
                    ->label('Halaman')
                    ->getStateUsing(function (SocialMediaContent $record): string {
                        $targets = [];

                        if ($record->show_on_madrasah) {
                            $targets[] = 'Madrasah';
                        }

                        if ($record->show_on_mahad) {
                            $targets[] = "Ma'had";
                        }

                        return empty($targets) ? '-' : implode(', ', $targets);
                    }),

                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                    ]),
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
            'index' => ListSocialMediaContents::route('/'),
            'create' => CreateSocialMediaContent::route('/create'),
            'edit' => EditSocialMediaContent::route('/{record}/edit'),
        ];
    }
}
