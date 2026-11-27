<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use App\Support\UploadImageHelper;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Testimoni Alumni';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 5;
    protected static ?string $modelLabel      = 'Testimoni';
    protected static ?string $pluralModelLabel = 'Testimoni Alumni';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto Alumni')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'testimonials')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->helperText('Foto profil alumni · Rasio 1:1 · Maks 5MB'),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Data Alumni')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Alumni')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Nama lengkap alumni'),

                        TextInput::make('graduation_year')
                            ->label('Tahun Lulus')
                            ->required()
                            ->maxLength(4)
                            ->placeholder('Contoh: 2020')
                            ->helperText('Tahun lulus dari MTsN 2 Kota Malang.'),

                        TextInput::make('current_school')
                            ->label('Sekolah / Instansi Saat Ini')
                            ->maxLength(200)
                            ->placeholder('Contoh: SMAN 1 Malang, UB, dll')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Testimoni')
                    ->schema([
                        Textarea::make('content')
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
                ImageColumn::make('photo')->label('Foto')->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'A') . '&background=1a7a3c&color=fff'),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('graduation_year')->label('Lulus')->badge()->color('success'),
                TextColumn::make('current_school')->label('Sekolah Saat Ini')->default('-')->limit(40),
                TextColumn::make('content')->label('Testimoni')->limit(60)->color('gray'),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
                TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit'   => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
