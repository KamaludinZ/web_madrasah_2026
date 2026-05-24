<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Website';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::get('site_name', 'MTsN 2 Kota Malang'),
            'site_tagline' => Setting::get('site_tagline', 'Unggul, Berkarakter, dan Berwawasan Global'),
            'announcement_text' => Setting::get('announcement_text', ''),
            'academic_year' => Setting::get('academic_year', '2024/2025'),
            'google_analytics_id' => Setting::get('google_analytics_id', ''),
            'recaptcha_site_key' => Setting::get('recaptcha_site_key', ''),
            'gemini_api_key' => Setting::get('gemini_api_key', ''),
            'openai_api_key' => Setting::get('openai_api_key', ''),
            'ai_provider' => Setting::get('ai_provider', 'gemini'),
            'whatsapp_number' => Setting::get('whatsapp_number', ''),
            'footer_text' => Setting::get('footer_text', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Pengaturan')->tabs([
                Forms\Components\Tabs\Tab::make('Umum')->schema([
                    Forms\Components\TextInput::make('site_name')->label('Nama Website')->required(),
                    Forms\Components\TextInput::make('site_tagline')->label('Tagline'),
                    Forms\Components\Textarea::make('announcement_text')->label('Teks Pengumuman Berjalan')->rows(2)->helperText('Teks yang berjalan di bawah header'),
                    Forms\Components\TextInput::make('academic_year')->label('Tahun Pelajaran Aktif')->placeholder('2024/2025'),
                    Forms\Components\TextInput::make('whatsapp_number')->label('Nomor WhatsApp PTSP'),
                    Forms\Components\Textarea::make('footer_text')->label('Teks Footer')->rows(2),
                ]),
                Forms\Components\Tabs\Tab::make('AI & SEO')->schema([
                    Forms\Components\Select::make('ai_provider')->label('Provider AI')->options(['gemini' => 'Google Gemini', 'openai' => 'OpenAI'])->default('gemini'),
                    Forms\Components\TextInput::make('gemini_api_key')->label('Gemini API Key')->password()->revealable(),
                    Forms\Components\TextInput::make('openai_api_key')->label('OpenAI API Key')->password()->revealable(),
                    Forms\Components\TextInput::make('google_analytics_id')->label('Google Analytics ID')->placeholder('G-XXXXXXXXXX'),
                    Forms\Components\TextInput::make('recaptcha_site_key')->label('reCAPTCHA Site Key'),
                ]),
            ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
        Notification::make()->title('Pengaturan berhasil disimpan!')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Pengaturan')
                ->action('save')
                ->color('primary'),
        ];
    }
}
