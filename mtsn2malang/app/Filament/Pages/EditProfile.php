<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected string  $view           = 'filament.pages.edit-profile';

    public static function isSimple(): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Profil Saya';
    }

    public static function getLabel(): string
    {
        return 'Profil Saya';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Baris 1: Foto + Info Akun (sidebar kiri) & Data Diri (kanan) ──
            Grid::make(['default' => 1, 'md' => 3])
                ->schema([

                    // Kolom kiri — foto profil + info read-only (custom view)
                    Group::make()
                        ->schema([
                            Section::make('Foto Profil')
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
                                        ->helperText('JPG / PNG / WebP · Maks 2MB · Rasio 1:1')
                                        ->avatar()
                                        ->alignCenter()
                                        ->extraAttributes(['class' => 'flex justify-center']),
                                ]),

                            View::make('filament.components.profile-info-card')
                                ->viewData(['user' => $this->getUser()]),
                        ])
                        ->columnSpan(['default' => 1, 'md' => 1]),

                    // Kolom kanan — data yang bisa diubah
                    Group::make()
                        ->schema([
                            Section::make('Data Diri')
                                ->description('Informasi yang ditampilkan di profil publik Anda.')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Textarea::make('bio')
                                        ->label('Bio / Deskripsi Singkat')
                                        ->rows(4)
                                        ->maxLength(500)
                                        ->placeholder('Ceritakan sedikit tentang diri Anda...')
                                        ->helperText('Maks 500 karakter. Ditampilkan di halaman profil penulis berita.')
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Ubah Password')
                                ->description('Kosongkan semua field jika tidak ingin mengubah password.')
                                ->icon('heroicon-o-lock-closed')
                                ->schema([
                                    TextInput::make('current_password')
                                        ->label('Password Saat Ini')
                                        ->password()
                                        ->revealable()
                                        ->dehydrated(false)
                                        ->helperText('Wajib diisi jika ingin mengubah password.')
                                        ->columnSpanFull(),

                                    TextInput::make('new_password')
                                        ->label('Password Baru')
                                        ->password()
                                        ->revealable()
                                        ->dehydrated(false)
                                        ->minLength(8)
                                        ->maxLength(255)
                                        ->placeholder('Minimal 8 karakter'),

                                    TextInput::make('new_password_confirmation')
                                        ->label('Konfirmasi Password Baru')
                                        ->password()
                                        ->revealable()
                                        ->dehydrated(false)
                                        ->same('new_password')
                                        ->placeholder('Ulangi password baru'),
                                ])
                                ->columns(2),
                        ])
                        ->columnSpan(['default' => 1, 'md' => 2]),

                ]),

        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['password'], $data['otp_code']);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);
        unset($data['email'], $data['roles']);
        return $data;
    }

    public function save(): void
    {
        $rawState        = $this->form->getRawState();
        $newPassword     = $rawState['new_password'] ?? null;
        $currentPassword = $rawState['current_password'] ?? null;

        if (filled($newPassword)) {
            if (! filled($currentPassword) || ! Hash::check($currentPassword, $this->getUser()->password)) {
                Notification::make()
                    ->title('Password saat ini tidak sesuai')
                    ->body('Masukkan password saat ini yang benar untuk mengubah password.')
                    ->danger()->send();
                return;
            }
            if ($newPassword !== ($rawState['new_password_confirmation'] ?? null)) {
                Notification::make()
                    ->title('Konfirmasi password tidak cocok')
                    ->danger()->send();
                return;
            }
        }

        parent::save();

        if (filled($newPassword)) {
            $this->getUser()->update(['password' => Hash::make($newPassword)]);
        }

        $this->data['current_password']          = null;
        $this->data['new_password']              = null;
        $this->data['new_password_confirmation'] = null;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        unset($data['email'], $data['roles'], $data['current_password'],
              $data['new_password'], $data['new_password_confirmation']);
        $record->update($data);
        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil berhasil diperbarui';
    }

    protected function getFormActions(): array
    {
        return [$this->getSaveFormAction()];
    }
}
