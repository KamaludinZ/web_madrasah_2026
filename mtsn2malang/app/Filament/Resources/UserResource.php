<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Support\RoleDisplay;
use Throwable;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Filament\Resources\Concerns\HasRoleAccess;

class UserResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole([]);
    }

    protected static ?string $model          = User::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';

    // ─── Form ────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Kolom kiri: Foto Profil ──────────────────────────────────────
            Group::make()->schema([

                Section::make('Foto Profil')
                    ->description('Upload foto profil pengguna. Disarankan foto persegi.')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('')
                            ->image()
                            ->directory('avatars')
                            ->disk('public')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('JPG, PNG, WebP · Maks 2MB · Rasio 1:1'),
                    ]),

                Section::make('Status Akun')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Nonaktifkan untuk mencegah pengguna login.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),

                        Toggle::make('is_suspended')
                            ->label('Akun Ditangguhkan')
                            ->helperText('Aktifkan untuk menangguhkan akun sementara.')
                            ->default(false)
                            ->onColor('danger')
                            ->offColor('success')
                            ->inline(false),
                    ]),

            ])->columnSpan(1),

            // ── Kolom kanan: Data Pengguna ───────────────────────────────────
            Group::make()->schema([

                Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap pengguna'),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('email@domain.com'),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context) => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->placeholder(fn (string $context) => $context === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : 'Minimal 8 karakter')
                            ->helperText(fn (string $context) => $context === 'edit' ? 'Kosongkan jika tidak ingin mengubah password.' : null),
                    ]),

                Section::make('Role & Akses')
                    ->description('Tentukan peran pengguna dalam sistem.')
                    ->schema([
                        Select::make('roles')
                            ->label('Role')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->options(function () {
                                try {
                                    return Role::orderBy('name')
                                        ->pluck('name', 'id')
                                        ->mapWithKeys(fn ($name, $id) => [
                                            $id => RoleDisplay::label($name),
                                        ]);
                                } catch (Throwable) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->placeholder('Pilih satu atau lebih role')
                            ->helperText('Pengguna dapat memiliki lebih dari satu role.'),
                    ]),

                Section::make('Bio')
                    ->schema([
                        Textarea::make('bio')
                            ->label('Bio / Deskripsi Singkat')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Deskripsi singkat tentang pengguna ini...')
                            ->helperText('Maks 500 karakter. Ditampilkan di profil penulis berita.'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    // ─── Table ───────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Foto')
                    ->rounded()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'U') . '&background=1a7a3c&color=fff&size=80'),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin'),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (?string $state) => RoleDisplay::color($state))
                    ->formatStateUsing(fn (?string $state) => RoleDisplay::label($state)),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_suspended')
                    ->label('Ditangguhkan')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),

                TernaryFilter::make('is_suspended')
                    ->label('Ditangguhkan')
                    ->trueLabel('Ditangguhkan')
                    ->falseLabel('Normal'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ─── Pages ───────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
