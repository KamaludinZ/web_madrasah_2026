<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SaranResource\Pages\ListSarans;
use App\Filament\Resources\SaranResource\Pages\EditSaran;
use Throwable;
use App\Filament\Resources\Concerns\HasRoleAccess;
use App\Filament\Resources\SaranResource\Pages;
use App\Models\Saran;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'Saran';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaduan & Survei';
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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Saran')
                ->schema([
                    TextInput::make('ticket_number')
                        ->label('No. Tiket')
                        ->disabled(),
                    TextInput::make('name')
                        ->label('Nama Pengirim')
                        ->disabled(),
                    TextInput::make('email')
                        ->label('Email')
                        ->placeholder('-')
                        ->disabled(),
                    TextInput::make('phone')
                        ->label('No. Telepon / WhatsApp')
                        ->placeholder('-')
                        ->disabled()
                        ->suffixAction(
                            Action::make('hubungi_wa')
                                ->label('Chat WhatsApp')
                                ->icon('heroicon-m-chat-bubble-left-right')
                                ->visible(fn ($record) => filled(static::whatsappNumber($record?->phone)))
                                ->url(fn ($record) => 'https://wa.me/' . static::whatsappNumber($record?->phone), shouldOpenInNewTab: true)
                        ),
                    TextInput::make('subject')
                        ->label('Subjek')
                        ->disabled()
                        ->columnSpanFull(),
                    Textarea::make('content')
                        ->label('Isi Saran')
                        ->disabled()
                        ->rows(6)
                        ->columnSpanFull(),
                ])->columns(3),

            Section::make('Tindak Lanjut')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'  => 'Belum Ditinjau',
                            'process'  => 'Sedang Ditinjau',
                            'resolved' => 'Sudah Ditindaklanjuti',
                            'rejected' => 'Tidak Relevan',
                        ])
                        ->required(),
                    DateTimePicker::make('responded_at')
                        ->label('Tanggal Tindak Lanjut')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('d/m/Y H:i')
                        ->format('Y-m-d H:i:s')
                        ->suffixAction(
                            Action::make('set_now')
                                ->label('Sekarang')
                                ->icon('heroicon-m-clock')
                                ->action(fn (Set $set) => $set('responded_at', Carbon::now()))
                        ),
                    Textarea::make('public_response')
                        ->label('Tanggapan untuk Pengirim')
                        ->rows(4)
                        ->maxLength(5000)
                        ->placeholder('Tuliskan tanggapan yang akan dibaca pengirim saran...')
                        ->helperText('Ditampilkan kepada pengirim saat melacak nomor tiket di halaman Saran.')
                        ->columnSpanFull(),
                    Textarea::make('response')
                        ->label('Catatan Internal')
                        ->rows(4)
                        ->placeholder('Catatan tindak lanjut internal...')
                        ->helperText('Hanya terlihat oleh petugas di panel admin — tidak ditampilkan kepada pengirim.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('name')
                    ->label('Pengirim')
                    ->getStateUsing(fn ($record) => $record->name ?: 'Anonim')
                    ->searchable()
                    ->description(fn ($record) => collect([$record->email, $record->phone])->filter()->implode(' · ')),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->subject),

                TextColumn::make('content')
                    ->label('Isi Saran')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->content)
                    ->wrap(),

                BadgeColumn::make('status')
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

                TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('d M Y H:i')),
            ])
            ->actions([
                Action::make('proses')
                    ->label('Tinjau')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'process', 'responded_at' => Carbon::now()]);
                        Notification::make()->title('Status diubah: Sedang Ditinjau')->info()->send();
                    }),
                EditAction::make()->label('Detail / Tindak Lanjut'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('tandai_selesai')
                        ->label('Tandai Ditindaklanjuti')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'resolved', 'responded_at' => Carbon::now()]);
                            Notification::make()->title($records->count() . ' saran ditandai selesai')->success()->send();
                        }),
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada saran')
            ->emptyStateDescription('Saran dari masyarakat akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-light-bulb');
    }

    /** Ubah nomor lokal (08xx) menjadi format internasional untuk tautan wa.me. */
    public static function whatsappNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        return str_starts_with($digits, '0') ? '62' . substr($digits, 1) : $digits;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSarans::route('/'),
            'edit'  => EditSaran::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = static::getEloquentQuery()->where('status', 'pending')->count();
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
