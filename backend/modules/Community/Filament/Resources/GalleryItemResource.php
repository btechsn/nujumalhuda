<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\GalleryItemResource\Pages;
use Modules\Community\Models\GalleryItem;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;

class GalleryItemResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('community');
    }

    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Médiathèque';

    protected static ?string $modelLabel = 'média';

    protected static ?string $pluralModelLabel = 'Médiathèque';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('kind')
                ->label('Type')
                ->options(['photo' => 'Photo', 'video' => 'Vidéo'])
                ->required()
                ->live(),
            Forms\Components\TextInput::make('event_name')->label('Événement')->required(),
            Forms\Components\Select::make('student_id')
                ->label('Élève identifiable sur ce média')
                ->helperText('Facultatif. À renseigner seulement si un élève précis est reconnaissable : son consentement « photo en galerie » devient alors nécessaire pour publier.')
                ->options(fn (): array => User::query()
                    ->orderBy('last_name')->orderBy('first_name')->limit(200)->get()
                    ->mapWithKeys(fn (User $u): array => [$u->getKey() => self::personLabel($u)])->all())
                ->searchable()
                ->preload()
                ->live(),
            Forms\Components\FileUpload::make('media_path')
                ->label('Fichier')
                ->disk('public')
                ->directory('gallery')
                ->visibility('public')
                ->acceptedFileTypes(fn (Get $get): array => $get('kind') === 'video'
                    ? ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-m4v']
                    : ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->maxSize(fn (Get $get): int => $get('kind') === 'video' ? 102400 : 10240)
                ->required(fn (Get $get): bool => blank($get('media_url')))
                ->helperText(fn (Get $get): string => $get('kind') === 'video'
                    ? 'MP4, WEBM ou MOV, 100 Mo maximum. Inutile si vous collez un lien.'
                    : 'JPG, PNG, WEBP ou GIF, 10 Mo maximum. Inutile si vous collez un lien.'),
            Forms\Components\FileUpload::make('thumbnail_path')
                ->label('Vignette')
                ->disk('public')
                ->directory('gallery/thumbnails')
                ->visibility('public')
                ->image()
                ->maxSize(5120)
                ->helperText('Facultatif. Utile pour une vidéo, afin d’afficher une image sur la carte.'),
            Forms\Components\TextInput::make('media_url')
                ->label('Lien du média')
                ->url()
                ->maxLength(255)
                ->required(fn (Get $get): bool => blank($get('media_path')))
                ->helperText('Collez une adresse si vous ne téléversez pas de fichier. Le fichier est utilisé en priorité.'),
            Forms\Components\TextInput::make('thumbnail_url')
                ->url()
                ->maxLength(255)
                ->label('Lien de la vignette')
                ->helperText('Facultatif si vous téléversez une vignette.'),
            Forms\Components\TextInput::make('caption_i18n.fr')->label('Légende (français)'),
            Forms\Components\TextInput::make('caption_i18n.en')->label('Légende (anglais)'),
            Forms\Components\TextInput::make('caption_i18n.ar')->label('Légende (arabe)'),
            Forms\Components\DatePicker::make('taken_on')->label('Date'),
            Forms\Components\Toggle::make('is_public')
                ->label('Visible sur le site')
                ->helperText(fn (Get $get): string => self::consentHelperText($get('student_id'))),
        ]);
    }

    /**
     * Verrou technique (cahier des charges §8.3, point 1) : si un élève est
     * associé au média et qu'il n'a pas de consentement « photo en
     * galerie » enregistré, la publication est refusée quoi qu'il arrive.
     */
    public static function enforceConsent(array $data): array
    {
        if (!($data['is_public'] ?? false) || blank($data['student_id'] ?? null)) {
            return $data;
        }

        $student = User::find($data['student_id']);

        $allowed = $student && $student->hasConsent('gallery_image');

        if (!$allowed) {
            $data['is_public'] = false;
            Notification::make()
                ->title('Publication refusée')
                ->body('Le consentement « photo en galerie » n\'est pas enregistré pour cet élève. Le média reste masqué du site.')
                ->warning()
                ->send();
        }

        AuditLog::log(
            $allowed ? 'gallery.published' : 'gallery.publication_refused',
            $student,
            context: ['student_id' => $data['student_id']],
        );

        return $data;
    }

    public static function consentHelperText(?string $studentId): string
    {
        if (blank($studentId)) {
            return 'Ne publier une photo d\'élève qu\'avec l\'autorisation du tuteur.';
        }

        $student = User::find($studentId);

        return $student && $student->hasConsent('gallery_image')
            ? 'Consentement enregistré : la publication est possible.'
            : 'Aucun consentement enregistré pour cet élève (Conformité & RGPD → Élèves mineurs). La publication sera automatiquement refusée.';
    }

    public static function personLabel(User $user): string
    {
        $name = $user->fullName();

        return $name !== '' ? $name : ($user->email ?: (string) $user->phone);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('kind')
                ->label('Type')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => $state === 'video' ? 'Vidéo' : 'Photo'),
            Tables\Columns\TextColumn::make('event_name')->label('Événement')->searchable(),
            Tables\Columns\TextColumn::make('caption_i18n.fr')->label('Légende'),
            Tables\Columns\TextColumn::make('student.first_name')
                ->label('Élève')
                ->formatStateUsing(fn (mixed $state, GalleryItem $record): string => $record->student ? self::personLabel($record->student) : '—'),
            Tables\Columns\IconColumn::make('is_public')->label('Visible')->boolean(),
            Tables\Columns\TextColumn::make('taken_on')->label('Date')->date(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
