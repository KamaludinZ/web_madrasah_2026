<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Filament\Resources\Concerns\HasRoleAccess;


class ComplaintResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'moderator']);
    }

    // Hanya tampilkan whistleblowing & masyarakat — exclude type='saran'
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('type', ['whistleblowing', 'masyarakat']);
    }

    protected static ?string $model = Complaint::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Whistleblowing & Pengaduan';
    protected static ?string $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengaduan')->schema([
                Forms\Components\TextInput::make('ticket_number')->label('No. Tiket')->disabled(),
                Forms\Components\Select::make('type')->label('Jenis')->options(['whistleblowing' => 'Whistleblowing', 'masyarakat' => 'Pengaduan'])->disabled(),
                Forms\Components\TextInput::make('name')->label('Nama Pelapor')->disabled(),
                Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('phone')->label('Telepon')->disabled(),
                Forms\Components\TextInput::make('subject')->label('Subjek')->disabled(),
                Forms\Components\Textarea::make('content')->label('Isi Pengaduan')->disabled()->rows(5),
            ]),
            Forms\Components\Section::make('Tindak Lanjut')->schema([
                Forms\Components\Select::make('status')->label('Status')
                    ->options(['pending' => 'Pending', 'process' => 'Diproses', 'resolved' => 'Selesai', 'rejected' => 'Ditolak'])
                    ->required(),
                Forms\Components\Textarea::make('response')->label('Tanggapan')->rows(4),
                Forms\Components\DateTimePicker::make('responded_at')
                    ->label('Tanggal Tanggapan')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->format('Y-m-d H:i:s')
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('set_now')
                            ->label('Now')
                            ->icon('heroicon-m-clock')
                            ->action(fn(Forms\Set $set) => $set('responded_at', Carbon::now()))
                    ),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Jenis')->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'masyarakat'     => 'Pengaduan',
                        'whistleblowing' => 'Whistleblowing',
                        default          => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'masyarakat'     => 'warning',
                        'whistleblowing' => 'danger',
                        default          => 'gray',
                    }),
                Tables\Columns\TextColumn::make('name')->label('Pelapor')->default('Anonim'),
                Tables\Columns\TextColumn::make('subject')->label('Subjek')->limit(40),
                Tables\Columns\TextColumn::make('status')->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'masyarakat'     => 'Pengaduan',
                        'whistleblowing' => 'Whistleblowing',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'process' => 'Diproses',
                        'resolved' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('created_at_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['from'])) {
                            $query->whereDate('created_at', '>=', $data['from']);
                        }

                        if (!empty($data['until'])) {
                            $query->whereDate('created_at', '<=', $data['until']);
                        }

                        return $query;
                    }),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('Tanggapi')])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool { return false; }
}
