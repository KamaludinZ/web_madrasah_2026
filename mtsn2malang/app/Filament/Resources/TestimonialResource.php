<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class TestimonialResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Testimonial::class;
    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Testimoni Alumni';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel      = 'Testimoni';
    protected static ?string $pluralModelLabel = 'Testimoni Alumni';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto Alumni')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'testimonials')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->helperText('Foto profil alumni · Rasio 1:1 · Maks 5MB'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Data Alumni')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Alumni')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Nama lengkap alumni'),

                        Forms\Components\TextInput::make('graduation_year')
                            ->label('Tahun Lulus')
                            ->required()
                            ->maxLength(4)
                            ->placeholder('Contoh: 2020')
                            ->helperText('Tahun lulus dari MTsN 2 Kota Malang.'),

                        Forms\Components\TextInput::make('current_school')
                            ->label('Sekolah / Instansi Saat Ini')
                            ->maxLength(200)
                            ->placeholder('Contoh: SMAN 1 Malang, UB, dll')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Testimoni')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Isi Testimoni')
                            ->required()
                            ->rows(5)
                            ->maxLength(1000)
                            ->placeholder('Tuliskan kesan dan pesan alumni tentang MTsN 2 Kota Malang...'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'A') . '&background=1a7a3c&color=fff'),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('graduation_year')->label('Lulus')->badge()->color('success'),
                Tables\Columns\TextColumn::make('current_school')->label('Sekolah Saat Ini')->default('-')->limit(40),
                Tables\Columns\TextColumn::make('content')->label('Testimoni')->limit(60)->color('gray'),
                Tables\Columns\IconColumn::make('is_active')->label('Tampil')->boolean(),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit'   => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
