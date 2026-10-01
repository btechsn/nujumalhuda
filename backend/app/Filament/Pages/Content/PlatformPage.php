<?php

declare(strict_types=1);

namespace App\Filament\Pages\Content;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Modules\Core\Support\PlatformPages;

abstract class PlatformPage extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Pages';

    protected static string $view = 'filament.pages.manage-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    abstract protected static function pageId(): string;

    public static function getSlug(): string
    {
        return PlatformPages::definition(static::pageId())['slug'];
    }

    public static function getNavigationLabel(): string
    {
        return PlatformPages::definition(static::pageId())['label'];
    }

    public static function getNavigationSort(): ?int
    {
        return PlatformPages::definition(static::pageId())['sort'];
    }

    public function getTitle(): string|Htmlable
    {
        return PlatformPages::definition(static::pageId())['label'];
    }

    public function mount(): void
    {
        $this->form->fill(PlatformPages::formState(static::pageId()));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([$this->makeSection()])
            ->statePath('data');
    }

    public function save(): void
    {
        PlatformPages::persist(static::pageId(), $this->form->getState());
        $this->form->fill(PlatformPages::formState(static::pageId()));

        Notification::make()
            ->success()
            ->title('Page enregistrée')
            ->send();
    }

    public function settingsWidth(): string
    {
        return '68rem';
    }

    /**
     * @return array<int, Action>
     */
    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->submit('save')
                ->button(),
        ];
    }

    private function makeSection(): Section
    {
        $fields = [];

        foreach (PlatformPages::fields(static::pageId()) as $field) {
            if ($field['type'] === 'image') {
                $fields[] = FileUpload::make($field['name'])
                    ->label($field['label'])
                    ->image()
                    ->disk('public')
                    ->directory($field['directory'] ?? 'pages/'.static::pageId())
                    ->visibility('public')
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('JPEG, PNG ou WebP, 4 Mo maximum. Sans fichier, la photo actuelle du site est conservée.')
                    ->columnSpanFull();

                continue;
            }

            if ($field['type'] === 'points') {
                $fields[] = Repeater::make('points')
                    ->label('Points')
                    ->schema([
                        Textarea::make('fr')->label('Français')->rows(2)->required()->maxLength(500),
                        Textarea::make('en')->label('Anglais')->rows(2)->maxLength(500),
                        Textarea::make('ar')->label('Arabe')->rows(2)->maxLength(500),
                    ])
                    ->columns(3)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => filled($state['fr'] ?? null) ? (string) $state['fr'] : null)
                    ->addActionLabel('Ajouter un point')
                    ->columnSpanFull();

                continue;
            }

            $locales = [];

            foreach (['fr' => 'français', 'en' => 'anglais', 'ar' => 'arabe'] as $locale => $language) {
                $component = $field['type'] === 'textarea'
                    ? Textarea::make($field['name'].'.'.$locale)->rows(4)->maxLength(2000)
                    : TextInput::make($field['name'].'.'.$locale)->maxLength(180);

                $component->label($field['label'].' ('.$language.')');

                if ($locale === 'fr') {
                    $component->required();
                }

                $locales[] = $component;
            }

            $fields[] = Grid::make(3)->schema($locales)->columnSpanFull();
        }

        $description = match (static::pageId()) {
            'centre' => 'Photo et textes affichés sur la page Centre. Les textes sont préremplis en français, anglais et arabe.',
            'zawiya' => 'Photo du fondateur, textes de la zawiya, introduction des horaires, titre et introduction des khutbas, et les deux textes du calendrier.',
            default => 'Textes affichés sur cette page du site. Ils sont préremplis en français, anglais et arabe.',
        };

        return Section::make(PlatformPages::definition(static::pageId())['label'])
            ->description($description)
            ->schema($fields)
            ->columns(1);
    }
}
