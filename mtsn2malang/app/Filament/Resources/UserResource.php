<?php
namespace App\Filament\Resources;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource {
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\FileUpload::make('avatar')->label('Foto')->image()->directory('avatars')->circular(),
            Forms\Components\TextInput::make('name')->label('Nama Lengkap')->required(),
            Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')->label('Password')->password()->dehydrateStateUsing(fn($state) => Hash::make($state))->dehydrated(fn($state) => filled($state))->required(fn(string $context) => $context === 'create'),
            Forms\Components\Select::make('roles')->label('Role')->multiple()->relationship('roles', 'name')->preload(),
            Forms\Components\Textarea::make('bio')->label('Bio')->rows(2),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            Forms\Components\Toggle::make('is_suspended')->label('Ditangguhkan')->default(false),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('avatar')->label('Foto')->circular()->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=1a7a3c&color=fff'),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
            Tables\Columns\TextColumn::make('roles.name')->label('Role')->badge(),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\IconColumn::make('is_suspended')->label('Ditangguhkan')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->label('Bergabung')->date('d M Y')->sortable(),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListUsers::route('/'), 'create' => Pages\CreateUser::route('/create'), 'edit' => Pages\EditUser::route('/{record}/edit')];
    }
}
