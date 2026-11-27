<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\EditAction;
use App\Filament\Resources\ComplaintResource\Pages\ListComplaints;
use App\Filament\Resources\ComplaintResource\Pages\EditComplaint;
use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Whistleblowing & Pengaduan';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Pengaduan')->schema([
                TextInput::make('ticket_number')->label('No. Tiket')->disabled(),
                Select::make('type')->label('Jenis')->options(['whistleblowing' => 'Whistleblowing', 'masyarakat' => 'Pengaduan'])->disabled(),
                TextInput::make('name')->label('Nama Pelapor')->disabled(),
                TextInput::make('email')->label('Email')->disabled(),
                TextInput::make('phone')->label('Telepon')->disabled(),
                TextInput::make('subject')->label('Subjek')->disabled(),
                Textarea::make('content')->label('Isi Pengaduan')->disabled()->rows(5),
            ]),
            Section::make('Tindak Lanjut')->schema([
                Select::make('status')->label('Status')
                    ->options(['pending' => 'Pending', 'process' => 'Diproses', 'resolved' => 'Selesai', 'rejected' => 'Ditolak'])
                    ->required(),
                Textarea::make('response')->label('Tanggapan')->rows(4),
                DateTimePicker::make('responded_at')
                    ->label('Tanggal Tanggapan')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->format('Y-m-d H:i:s')
                    ->suffixAction(
                        Action::make('set_now')
                            ->label('Now')
                            ->icon('heroicon-m-clock')
                            ->action(fn(Set $set) => $set('responded_at', Carbon::now()))
                    ),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')->label('No. Tiket')->searchable(),
                TextColumn::make('type')->label('Jenis')->badge()
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
                TextColumn::make('name')->label('Pelapor')->default('Anonim'),
                TextColumn::make('subject')->label('Subjek')->limit(40),
                TextColumn::make('status')->label('Status')
                    ->badge(),
                TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'masyarakat'     => 'Pengaduan',
                        'whistleblowing' => 'Whistleblowing',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'process' => 'Diproses',
                        'resolved' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),
                Filter::make('created_at_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
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
            ->actions([EditAction::make()->label('Tanggapi')])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplaints::route('/'),
            'edit' => EditComplaint::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool { return false; }
}
