<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
use Modules\Core\Support\SiteSettings;

abstract class SettingsSectionPage extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Paramètres';

    protected static string $view = 'filament.pages.manage-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    abstract protected static function section(): string;

    public function mount(): void
    {
        $this->form->fill(SiteSettings::formState());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([$this->makeSection()])
            ->statePath('data');
    }

    public function save(): void
    {
        SiteSettings::persist($this->form->getState());

        $this->form->fill(SiteSettings::formState());

        Notification::make()
            ->success()
            ->title('Paramètres enregistrés')
            ->send();
    }

    public function settingsWidth(): string
    {
        return in_array(static::section(), ['footer', 'links'], true) ? '68rem' : '46rem';
    }

    public function searchPlace(string $query): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 3) {
            return [];
        }

        $response = Http::timeout(8)
            ->withHeaders([
                'User-Agent' => 'NujumAlHudaAdmin/1.0 (contact@nujumalhuda.com)',
                'Accept-Language' => 'fr',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'limit' => 5,
                'q' => $query,
            ]);

        if (! $response->ok()) {
            return [];
        }

        return collect($response->json())
            ->filter(fn ($row): bool => is_array($row) && isset($row['lat'], $row['lon']))
            ->map(fn (array $row): array => [
                'label' => (string) ($row['display_name'] ?? ''),
                'latitude' => (float) $row['lat'],
                'longitude' => (float) $row['lon'],
            ])
            ->values()
            ->all();
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

    protected function makeSection(): Section
    {
        $meta = SiteSettings::sections()[static::section()];

        return match (static::section()) {
            'footer' => $this->footerSection($meta),
            'links' => $this->linksSection($meta),
            default => $this->genericSection($meta, static::section()),
        };
    }

    /**
     * @param  array{title: string, description: string}  $meta
     */
    private function genericSection(array $meta, string $section): Section
    {
        $fields = [];

        foreach (SiteSettings::fields() as $key => $field) {
            if ($field['section'] !== $section) {
                continue;
            }

            if ($key === 'site.longitude') {
                continue;
            }

            if ($key === 'site.latitude') {
                $fields[] = Hidden::make('site.latitude');
                $fields[] = Hidden::make('site.longitude');
                $fields[] = View::make('filament.forms.components.map-picker')->columnSpanFull();

                continue;
            }

            $component = $field['type'] === 'text'
                ? Textarea::make($key)->rows(3)
                : TextInput::make($key);

            $component->label($field['label']);

            if ($field['type'] === 'integer' || $field['type'] === 'float') {
                $component->numeric();
            }

            if (str_ends_with($key, 'email') || str_ends_with($key, 'from_address')) {
                $component->email();
            }

            if ($key === 'analytics.measurement_id') {
                $component
                    ->placeholder('G-XXXXXXXX')
                    ->rule('regex:/^[A-Za-z0-9-]*$/');
            }

            if ($field['secret']) {
                $component
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->helperText(
                        SiteSettings::hasSecret($key)
                            ? 'Une clé est enregistrée. Laissez vide pour la conserver.'
                            : 'Aucune clé enregistrée.',
                    );
            }

            if ($field['span'] === 'full') {
                $component->columnSpanFull();
            }

            $fields[] = $component;
        }

        return Section::make($meta['title'])
            ->description($meta['description'])
            ->schema($fields)
            ->columns(2);
    }

    /**
     * @param  array{title: string, description: string}  $meta
     */
    private function footerSection(array $meta): Section
    {
        return Section::make($meta['title'])
            ->description($meta['description'])
            ->schema([
                FileUpload::make('footer.logo')
                    ->label('Logo')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('branding')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('JPEG, PNG ou WebP, 2 Mo maximum. Sans fichier, le logo actuel du site est conservé.')
                    ->columnSpanFull(),
                Grid::make(3)->schema([
                    TextInput::make('footer.title_fr')->label('Titre (français)')->required()->maxLength(120),
                    TextInput::make('footer.title_en')->label('Titre (anglais)')->required()->maxLength(120),
                    TextInput::make('footer.title_ar')->label('Titre (arabe)')->required()->maxLength(120),
                ])->columnSpanFull(),
                Grid::make(3)->schema([
                    Textarea::make('footer.blurb_fr')->label('Description (français)')->rows(4)->required(),
                    Textarea::make('footer.blurb_en')->label('Description (anglais)')->rows(4)->required(),
                    Textarea::make('footer.blurb_ar')->label('Description (arabe)')->rows(4)->required(),
                ])->columnSpanFull(),
                Textarea::make('site.footer_note')
                    ->label('Note sous l’adresse')
                    ->rows(2)
                    ->helperText('Texte facultatif ajouté sous l’adresse, dans la colonne contact.')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    /**
     * @param  array{title: string, description: string}  $meta
     */
    private function linksSection(array $meta): Section
    {
        return Section::make($meta['title'])
            ->description($meta['description'])
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('footer.useful_title_fr')->label('Liens utiles (français)')->required()->maxLength(80),
                    TextInput::make('footer.useful_title_en')->label('Liens utiles (anglais)')->required()->maxLength(80),
                    TextInput::make('footer.useful_title_ar')->label('Liens utiles (arabe)')->required()->maxLength(80),
                ])->columnSpanFull(),
                $this->linkRepeater('footer.useful_links'),
                Grid::make(3)->schema([
                    TextInput::make('footer.other_title_fr')->label('Autres liens (français)')->required()->maxLength(80),
                    TextInput::make('footer.other_title_en')->label('Autres liens (anglais)')->required()->maxLength(80),
                    TextInput::make('footer.other_title_ar')->label('Autres liens (arabe)')->required()->maxLength(80),
                ])->columnSpanFull(),
                $this->linkRepeater('footer.other_links'),
            ])
            ->columns(1);
    }

    private function linkRepeater(string $name): Repeater
    {
        return Repeater::make($name)
            ->hiddenLabel()
            ->schema([
                TextInput::make('label_fr')->label('Libellé français')->required()->maxLength(80),
                TextInput::make('label_en')->label('Libellé anglais')->required()->maxLength(80),
                TextInput::make('label_ar')->label('Libellé arabe')->required()->maxLength(80),
                TextInput::make('href')
                    ->label('Lien')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('/centre')
                    ->helperText('Chemin du site, par exemple /zawiya, ou une adresse https://…')
                    ->columnSpanFull(),
            ])
            ->columns(3)
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => filled($state['label_fr'] ?? null) ? (string) $state['label_fr'] : null)
            ->addActionLabel('Ajouter un lien')
            ->columnSpanFull();
    }
}
