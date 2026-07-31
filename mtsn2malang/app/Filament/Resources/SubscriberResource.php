<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class SubscriberResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model           = Subscriber::class;
    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Subscriber';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 99;
    protected static ?string $modelLabel      = 'Subscriber';
    protected static ?string $pluralModelLabel = 'Subscriber Email';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Subscriber')
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->label('Alamat Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('email@domain.com'),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama (Opsional)')
                        ->maxLength(100)
                        ->placeholder('Nama subscriber'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Subscriber Aktif')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger')
                        ->helperText('Nonaktifkan untuk berhenti mengirim email ke subscriber ini.')
                        ->inline(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->copyable()->copyMessage('Email disalin'),
                Tables\Columns\TextColumn::make('name')->label('Nama')->default('-')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Bergabung')->date('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status')->trueLabel('Aktif')->falseLabel('Nonaktif'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'edit'   => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }
}
