<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialMediaContentResource\Pages;
use App\Models\Setting;
use App\Models\SocialMediaContent;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Konten Sosial Media';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Konten Sosial Media')
                ->schema([
                    Forms\Components\Select::make('platform')
                        ->label('Platform')
                        ->options([
                            'youtube' => 'YouTube',
                            'instagram' => 'Instagram',
                            'tiktok' => 'TikTok',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('Judul Konten')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('post_url')
                        ->label('Link Post')
                        ->url()
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('embed_url')
                        ->label('Link Embed (Opsional)')
                        ->url()
                        ->maxLength(255),

                    UploadImageHelper::webpFileUpload('thumbnail', 'Thumbnail (Opsional)', 'social-media', false)
                        ->helperText('Jika thumbnail konten kosong, sistem akan menggunakan thumbnail default sesuai platform.'),

                    Forms\Components\TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\CheckboxList::make('page_visibility')
                        ->label('Tampilkan pada halaman')
                        ->options([
                            'madrasah' => 'Madrasah',
                            'mahad' => "Ma'had",
                        ])
                        ->columns(2)
                        ->default(['madrasah'])
                        ->required()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?SocialMediaContent $record) {
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
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $values = is_array($state) ? $state : [];
                            $set('show_on_madrasah', in_array('madrasah', $values, true));
                            $set('show_on_mahad', in_array('mahad', $values, true));
                        }),

                    Forms\Components\Hidden::make('show_on_madrasah')
                        ->default(true),

                    Forms\Components\Hidden::make('show_on_mahad')
                        ->default(false),

                    Forms\Components\Toggle::make('is_active')
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
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->defaultImageUrl(fn($record) => match ($record->platform) {
                        'youtube' => Setting::get('default_social_thumbnail_youtube') ? asset('storage/' . Setting::get('default_social_thumbnail_youtube')) : asset('images/program-placeholder.jpg'),
                        'instagram' => Setting::get('default_social_thumbnail_instagram') ? asset('storage/' . Setting::get('default_social_thumbnail_instagram')) : asset('images/program-placeholder.jpg'),
                        'tiktok' => Setting::get('default_social_thumbnail_tiktok') ? asset('storage/' . Setting::get('default_social_thumbnail_tiktok')) : asset('images/program-placeholder.jpg'),
                        default => asset('images/program-placeholder.jpg'),
                    })
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('platform')
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

                Tables\Columns\TextColumn::make('page_target')
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

                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                    ]),
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
            'index' => Pages\ListSocialMediaContents::route('/'),
            'create' => Pages\CreateSocialMediaContent::route('/create'),
            'edit' => Pages\EditSocialMediaContent::route('/{record}/edit'),
        ];
    }
}
