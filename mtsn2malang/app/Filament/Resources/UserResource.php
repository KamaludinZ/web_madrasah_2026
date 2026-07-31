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
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';

    // ─── Form ────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Kolom kiri: Foto Profil ──────────────────────────────────────
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto Profil')
                    ->description('Upload foto profil pengguna. Disarankan foto persegi.')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
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

                Forms\Components\Section::make('Status Akun')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Nonaktifkan untuk mencegah pengguna login.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),

                        Forms\Components\Toggle::make('is_suspended')
                            ->label('Akun Ditangguhkan')
                            ->helperText('Aktifkan untuk menangguhkan akun sementara.')
                            ->default(false)
                            ->onColor('danger')
                            ->offColor('success')
                            ->inline(false),
                    ]),

            ])->columnSpan(1),

            // ── Kolom kanan: Data Pengguna ───────────────────────────────────
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap pengguna'),

                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('email@domain.com'),

                        Forms\Components\TextInput::make('password')
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

                Forms\Components\Section::make('Role & Akses')
                    ->description('Tentukan peran pengguna dalam sistem.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->options(function () {
                                try {
                                    $labels = [
                                        'super_admin'     => 'Super Administrator',
                                        'editor_in_chief' => 'Editor in Chief',
                                        'editor'          => 'Editor',
                                        'contributor'     => 'Kontributor',
                                        'moderator'       => 'Moderator',
                                    ];
                                    return Role::orderBy('name')
                                        ->pluck('name', 'id')
                                        ->mapWithKeys(fn ($name, $id) => [
                                            $id => $labels[$name] ?? ucfirst(str_replace('_', ' ', $name)),
                                        ]);
                                } catch (\Throwable) {
                                    return [];
                                }
                            })
                            ->searchable()
                            ->placeholder('Pilih satu atau lebih role')
                            ->helperText('Pengguna dapat memiliki lebih dari satu role.'),
                    ]),

                Forms\Components\Section::make('Bio')
                    ->schema([
                        Forms\Components\Textarea::make('bio')
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
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Foto')
                    ->rounded()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'U') . '&background=1a7a3c&color=fff&size=80'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'super_admin'     => 'Super Admin',
                        'editor_in_chief' => 'Editor in Chief',
                        'editor'          => 'Editor',
                        'contributor'     => 'Kontributor',
                        'moderator'       => 'Moderator',
                        default           => ucfirst(str_replace('_', ' ', $state)),
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('is_suspended')
                    ->label('Ditangguhkan')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),

                Tables\Filters\TernaryFilter::make('is_suspended')
                    ->label('Ditangguhkan')
                    ->trueLabel('Ditangguhkan')
                    ->falseLabel('Normal'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // ─── Pages ───────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
