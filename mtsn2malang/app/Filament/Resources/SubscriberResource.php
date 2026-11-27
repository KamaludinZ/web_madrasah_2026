<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SubscriberResource\Pages\ListSubscribers;
use App\Filament\Resources\SubscriberResource\Pages\CreateSubscriber;
use App\Filament\Resources\SubscriberResource\Pages\EditSubscriber;
use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Subscriber';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 99;
    protected static ?string $modelLabel      = 'Subscriber';
    protected static ?string $pluralModelLabel = 'Subscriber Email';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Subscriber')
                ->schema([
                    TextInput::make('email')
                        ->label('Alamat Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('email@domain.com'),

                    TextInput::make('name')
                        ->label('Nama (Opsional)')
                        ->maxLength(100)
                        ->placeholder('Nama subscriber'),

                    Toggle::make('is_active')
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
                TextColumn::make('email')->label('Email')->searchable()->copyable()->copyMessage('Email disalin'),
                TextColumn::make('name')->label('Nama')->default('-')->searchable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('created_at')->label('Bergabung')->date('d M Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status')->trueLabel('Aktif')->falseLabel('Nonaktif'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSubscribers::route('/'),
            'create' => CreateSubscriber::route('/create'),
            'edit'   => EditSubscriber::route('/{record}/edit'),
        ];
    }
}
