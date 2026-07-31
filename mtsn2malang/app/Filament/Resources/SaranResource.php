<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\SaranResource\Pages;
use App\Models\Saran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SaranResource extends Resource
{
    use HasRoleAccess;

    protected static ?string $model           = Saran::class;
    protected static ?string $navigationIcon  = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'Saran';
    protected static ?string $navigationGroup = 'Pengaduan & Survei';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Saran';
    protected static ?string $pluralModelLabel = 'Saran';
    protected static ?string $slug            = 'sarans';

    public static function canAccess(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'moderator']);
    }

    // Scope query hanya ke type='saran'
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'saran');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Saran')
                ->schema([
                    Forms\Components\TextInput::make('ticket_number')
                        ->label('No. Tiket')
                        ->disabled(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Pengirim')
                        ->disabled(),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->disabled(),
                    Forms\Components\TextInput::make('subject')
                        ->label('Subjek')
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('content')
                        ->label('Isi Saran')
                        ->disabled()
                        ->rows(6)
                        ->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Tindak Lanjut')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'  => 'Belum Ditinjau',
                            'process'  => 'Sedang Ditinjau',
                            'resolved' => 'Sudah Ditindaklanjuti',
                            'rejected' => 'Tidak Relevan',
                        ])
                        ->required(),
                    Forms\Components\DateTimePicker::make('responded_at')
                        ->label('Tanggal Tindak Lanjut')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('d/m/Y H:i')
                        ->format('Y-m-d H:i:s')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('set_now')
                                ->label('Sekarang')
                                ->icon('heroicon-m-clock')
                                ->action(fn (Forms\Set $set) => $set('responded_at', Carbon::now()))
                        ),
                    Forms\Components\Textarea::make('response')
                        ->label('Catatan / Tanggapan Internal')
                        ->rows(4)
                        ->placeholder('Catatan tindak lanjut internal...')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Pengirim')
                    ->getStateUsing(fn ($record) => $record->name ?: 'Anonim')
                    ->searchable()
                    ->description(fn ($record) => $record->email ?: ''),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->subject),

                Tables\Columns\TextColumn::make('content')
                    ->label('Isi Saran')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->content)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'process',
                        'success' => 'resolved',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'  => 'Belum Ditinjau',
                        'process'  => 'Sedang Ditinjau',
                        'resolved' => 'Ditindaklanjuti',
                        'rejected' => 'Tidak Relevan',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('d M Y H:i')),
            ])
            ->actions([
                Tables\Actions\Action::make('proses')
                    ->label('Tinjau')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'process', 'responded_at' => Carbon::now()]);
                        Notification::make()->title('Status diubah: Sedang Ditinjau')->info()->send();
                    }),
                Tables\Actions\EditAction::make()->label('Detail / Tindak Lanjut'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('tandai_selesai')
                        ->label('Tandai Ditindaklanjuti')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'resolved', 'responded_at' => Carbon::now()]);
                            Notification::make()->title($records->count() . ' saran ditandai selesai')->success()->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada saran')
            ->emptyStateDescription('Saran dari masyarakat akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-light-bulb');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSarans::route('/'),
            'edit'  => Pages\EditSaran::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = static::getEloquentQuery()->where('status', 'pending')->count();
            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
