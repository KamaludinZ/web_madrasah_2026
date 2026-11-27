<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\CommentResource\Pages\ListComments;
use Throwable;
use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Models\Comment;
use App\Models\News;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model = Comment::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Moderasi Komentar';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Komentar';
    protected static ?string $pluralModelLabel = 'Komentar';

    public static function canAccess(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor', 'moderator']);
    }

    // Tidak ada form Create — komentar hanya dibuat dari frontend
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('guest_name')
                ->label('Nama Pengunjung')
                ->disabled()
                ->visible(fn ($record) => filled($record?->guest_name)),
            TextInput::make('guest_email')
                ->label('Email Pengunjung (tidak ditampilkan publik)')
                ->disabled()
                ->visible(fn ($record) => filled($record?->guest_email)),
            Textarea::make('content')
                ->label('Isi Komentar')
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending'  => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending'  => 'Menunggu',
                        default    => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('content')
                    ->label('Komentar')
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->content)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('author_name')
                    ->label('Penulis')
                    ->getStateUsing(fn ($record) =>
                        $record->user?->name ?? ($record->guest_name ?: 'Anonim')
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                              ->orWhere('guest_name', 'like', "%{$search}%");
                        });
                    })
                    ->icon('heroicon-m-user')
                    ->iconColor('gray'),

                TextColumn::make('news.title')
                    ->label('Berita')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->news?->title)
                    ->url(fn ($record) => $record->news
                        ? route('berita.show', $record->news->slug)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('d M Y H:i')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->placeholder('Semua Status'),

                SelectFilter::make('news_id')
                    ->label('Berita')
                    ->options(fn () => News::orderBy('title')->pluck('title', 'id')->toArray())
                    ->searchable()
                    ->placeholder('Semua Berita'),

                TrashedFilter::make(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Komentar?')
                    ->modalDescription('Komentar ini akan ditampilkan di halaman berita.')
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);
                        Notification::make()
                            ->title('Komentar disetujui')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Komentar?')
                    ->modalDescription('Komentar ini tidak akan ditampilkan di halaman berita.')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()
                            ->title('Komentar ditolak')
                            ->danger()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus'),

                RestoreAction::make()
                    ->label('Pulihkan'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'approved']);
                            Notification::make()
                                ->title($records->count() . ' komentar disetujui')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulk_reject')
                        ->label('Tolak Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'rejected']);
                            Notification::make()
                                ->title($records->count() . ' komentar ditolak')
                                ->danger()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),

                    RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada komentar')
            ->emptyStateDescription('Komentar dari pengunjung akan muncul di sini untuk dimoderasi.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = Comment::where('status', 'pending')->count();
            return $count > 0 ? (string) $count : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
