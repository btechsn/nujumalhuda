<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Media;
use Modules\Education\Models\Teacher;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Éducation';
    protected static ?string $navigationLabel = 'Enseignants';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                fn (?Teacher $record) => Rule::unique('users', 'email')->ignore($record?->getKey()),
                            ]),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20)
                            ->nullable()
                            ->rules([
                                fn (?Teacher $record) => Rule::unique('users', 'phone')->ignore($record?->getKey()),
                            ]),
                        Forms\Components\Select::make('organization_id')
                            ->label('Organisation')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn (): ?string => auth()->user()?->memberships()->value('organization_id')),
                    ])->columns(2),
                
                Forms\Components\Section::make('Biographie')
                    ->schema([
                        Forms\Components\RichEditor::make('bio_i18n.fr')
                            ->label('Biographie (Français)')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('bio_i18n.en')
                            ->label('Biography (English)')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('bio_i18n.ar')
                            ->label('السيرة الذاتية (عربي)')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Spécialités')
                    ->schema([
                        Forms\Components\TagsInput::make('specialties_i18n.fr')
                            ->label('Spécialités (Français)')
                            ->placeholder('Ex: Tajwid, Fiqh, Hadith')
                            ->helperText('Appuyez sur Entrée pour ajouter'),
                        
                        Forms\Components\TagsInput::make('specialties_i18n.en')
                            ->label('Specialties (English)'),
                        
                        Forms\Components\TagsInput::make('specialties_i18n.ar')
                            ->label('التخصصات (عربي)'),
                    ]),
                
                Forms\Components\Section::make('Qualifications')
                    ->schema([
                        Forms\Components\TagsInput::make('qualifications_i18n.fr')
                            ->label('Diplômes et formations (Français)')
                            ->placeholder('Ex: Licence en études islamiques'),
                        
                        Forms\Components\TagsInput::make('qualifications_i18n.en')
                            ->label('Qualifications (English)'),
                        
                        Forms\Components\TagsInput::make('qualifications_i18n.ar')
                            ->label('المؤهلات (عربي)'),
                    ]),
                
                Forms\Components\Section::make('Ijaza et Sanad')
                    ->schema([
                        Forms\Components\Toggle::make('has_ijaza')
                            ->label('Possède une ijaza')
                            ->live(),

                        Forms\Components\TextInput::make('ijaza_title')
                            ->label('Intitulé')
                            ->maxLength(180)
                            ->visible(fn ($get) => (bool) $get('has_ijaza')),

                        Forms\Components\TextInput::make('ijaza_issuer')
                            ->label('Délivrée par')
                            ->maxLength(180)
                            ->visible(fn ($get) => (bool) $get('has_ijaza')),

                        Forms\Components\TextInput::make('ijaza_year')
                            ->label('Année')
                            ->maxLength(10)
                            ->visible(fn ($get) => (bool) $get('has_ijaza')),

                        Forms\Components\TextInput::make('ijaza_domain')
                            ->label('Domaine')
                            ->maxLength(180)
                            ->visible(fn ($get) => (bool) $get('has_ijaza')),

                        Forms\Components\FileUpload::make('ijaza_document')
                            ->label('Document de l\'ijaza')
                            ->disk('public')
                            ->directory('teachers/ijaza')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Téléversez le document depuis votre ordinateur. PDF, JPEG, PNG ou WebP, 8 Mo maximum.')
                            ->visible(fn ($get) => (bool) $get('has_ijaza'))
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('sanad')
                            ->label('Chaîne de transmission (Sanad)')
                            ->rows(4)
                            ->visible(fn($get) => $get('has_ijaza'))
                            ->columnSpanFull()
                            ->helperText('Un maître par ligne, du plus récent au plus ancien.'),
                    ]),
                
                Forms\Components\Section::make('Disponibilité')
                    ->schema([
                        Forms\Components\Toggle::make('is_available')
                            ->label('Disponible pour enseigner')
                            ->default(true),
                        
                        Forms\Components\KeyValue::make('availability')
                            ->label('Horaires disponibles')
                            ->keyLabel('Jour')
                            ->valueLabel('Heures')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Médias et réseaux')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Photo de profil')
                            ->image()
                            ->disk('public')
                            ->directory('teachers/photos')
                            ->visibility('public')
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Choisissez une image sur votre ordinateur. JPEG, PNG ou WebP, 4 Mo maximum.')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('youtube_channel')
                            ->label('Chaîne YouTube')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/@...'),
                        
                        Forms\Components\TextInput::make('facebook_page')
                            ->label('Page Facebook')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://facebook.com/...'),
                    ]),
                
                Forms\Components\Section::make('Options d\'affichage')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Affiché sur la page enseignants')
                            ->default(false),
                        
                        Forms\Components\TextInput::make('display_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('0 = premier'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Nom')
                    ->formatStateUsing(fn (?string $state, Teacher $record): string => $record->user?->fullName() ?? '')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $user) use ($search): void {
                            $user->where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%");
                        });
                    }),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('has_ijaza')
                    ->label('Ijaza')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Disponible')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('En avant')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Élèves')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_sessions')
                    ->label('Sessions')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_ijaza')->label('Possède ijaza'),
                Tables\Filters\TernaryFilter::make('is_available')->label('Disponible'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('En avant'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_order');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyPhoto(array $data, ?string $ownerId, ?string $currentMediaId = null): array
    {
        $path = self::uploadedPath($data['photo'] ?? null);
        unset($data['photo']);

        if ($path === null) {
            $data['photo_media_id'] = null;

            return $data;
        }

        $current = filled($currentMediaId) ? Media::query()->find($currentMediaId) : null;
        if ($current && $current->path === $path) {
            $data['photo_media_id'] = $current->id;

            return $data;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            $data['photo_media_id'] = $current?->id;

            return $data;
        }

        $absolute = $disk->path($path);
        $info = is_file($absolute) ? @getimagesize($absolute) : false;
        $fileName = basename($path);

        $media = Media::query()->create([
            'mediable_type' => Teacher::class,
            'mediable_id' => $ownerId,
            'uploaded_by' => auth()->id(),
            'collection' => 'photo',
            'name' => pathinfo($fileName, PATHINFO_FILENAME) ?: $fileName,
            'file_name' => $fileName,
            'mime_type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'disk' => 'public',
            'path' => $path,
            'size' => $disk->size($path) ?: 0,
            'width' => is_array($info) ? ($info[0] ?? null) : null,
            'height' => is_array($info) ? ($info[1] ?? null) : null,
        ]);

        $data['photo_media_id'] = $media->id;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>|null  $currentDetails
     * @return array<string, mixed>
     */
    public static function applyIjaza(array $data, ?array $currentDetails = null): array
    {
        $path = self::uploadedPath($data['ijaza_document'] ?? null);
        $previous = is_array($currentDetails) && is_string($currentDetails['document'] ?? null)
            ? $currentDetails['document']
            : null;

        if ($path !== null && ! Storage::disk('public')->exists($path)) {
            $path = $previous;
        }

        $french = [
            'title' => trim((string) ($data['ijaza_title'] ?? '')),
            'issuer' => trim((string) ($data['ijaza_issuer'] ?? '')),
            'year' => trim((string) ($data['ijaza_year'] ?? '')),
            'domain' => trim((string) ($data['ijaza_domain'] ?? '')),
        ];

        unset(
            $data['ijaza_document'],
            $data['ijaza_title'],
            $data['ijaza_issuer'],
            $data['ijaza_year'],
            $data['ijaza_domain'],
        );

        if (implode('', $french) === '' && $path === null) {
            $data['ijaza_details'] = null;

            return $data;
        }

        $details = ['fr' => $french];
        foreach (['en', 'ar'] as $locale) {
            if (is_array($currentDetails) && is_array($currentDetails[$locale] ?? null)) {
                $details[$locale] = $currentDetails[$locale];
            }
        }
        if ($path !== null) {
            $details['document'] = $path;
        }

        $data['ijaza_details'] = $details;

        return $data;
    }

    public static function sanadText(mixed $sanad): string
    {
        if (is_string($sanad)) {
            return $sanad;
        }

        if (! is_array($sanad)) {
            return '';
        }

        $lines = $sanad['chain'] ?? (array_is_list($sanad) ? $sanad : []);

        return implode("\n", array_values(array_filter(
            array_map(fn (mixed $line): string => is_string($line) ? trim($line) : '', $lines),
        )));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applySanad(array $data, mixed $current = null): array
    {
        $text = is_string($data['sanad'] ?? null) ? $data['sanad'] : self::sanadText($data['sanad'] ?? null);
        $lines = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $text) ?: [],
        )));

        if ($lines === []) {
            $data['sanad'] = null;

            return $data;
        }

        $payload = ['chain' => $lines];
        if (is_array($current) && is_string($current['riwaya'] ?? null) && trim($current['riwaya']) !== '') {
            $payload['riwaya'] = trim($current['riwaya']);
        }

        $data['sanad'] = $payload;

        return $data;
    }

    private static function uploadedPath(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = array_values(array_filter($value, 'is_string'))[0] ?? null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Education\Filament\Resources\TeacherResource\Pages\ListTeachers::route('/'),
            'create' => \Modules\Education\Filament\Resources\TeacherResource\Pages\CreateTeacher::route('/create'),
            'edit' => \Modules\Education\Filament\Resources\TeacherResource\Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
