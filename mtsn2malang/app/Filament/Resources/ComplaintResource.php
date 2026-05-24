<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Pengaduan';
    protected static ?string $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengaduan')->schema([
                Forms\Components\TextInput::make('ticket_number')->label('No. Tiket')->disabled(),
                Forms\Components\Select::make('type')->label('Jenis')->options(['whistleblowing' => 'Whistleblowing', 'masyarakat' => 'Pengaduan Masyarakat'])->disabled(),
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
                Forms\Components\DateTimePicker::make('responded_at')->label('Tanggal Tanggapan'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->searchable(),
                Tables\Columns\BadgeColumn::make('type')->label('Jenis')->colors(['warning' => 'whistleblowing', 'info' => 'masyarakat']),
                Tables\Columns\TextColumn::make('name')->label('Pelapor')->default('Anonim'),
                Tables\Columns\TextColumn::make('subject')->label('Subjek')->limit(40),
                Tables\Columns\BadgeColumn::make('status')->label('Status')
                    ->colors(['secondary' => 'pending', 'warning' => 'process', 'success' => 'resolved', 'danger' => 'rejected']),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
