<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Modules\Core\Support\ContributionDesk;
use Modules\Core\Support\PanelAccess;

class ModerationSettingsPage extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Modération';

    protected static ?string $title = 'Modération';

    protected static ?string $slug = 'parametres/moderation';

    protected static ?int $navigationSort = 45;

    protected static string $view = 'filament.pages.manage-settings';

    public static function canAccess(): bool
    {
        return PanelAccess::allows('settings_editorial');
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ContributionDesk::formState());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->sections())
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        ContributionDesk::persist($state);
        $this->form->fill(ContributionDesk::formState());

        $lines = [];

        foreach (ContributionDesk::surfaces() as $key => $surface) {
            $missing = ContributionDesk::missing(is_array($state[$key] ?? null) ? $state[$key] : []);
            $lines[] = $missing === []
                ? $surface['label'].' : ouverte'
                : $surface['label'].' : fermée';
        }

        Notification::make()
            ->success()
            ->title('Gouvernance enregistrée')
            ->body(implode(' · ', $lines))
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

    /**
     * @return array<int, Section>
     */
    private function sections(): array
    {
        $sections = [];

        foreach (ContributionDesk::surfaces() as $key => $surface) {
            $mode = $surface['mode'] === 'prior' ? 'Relecture avant publication' : 'Relecture après envoi';

            $sections[] = Section::make($surface['label'])
                ->description($mode.'. '.$surface['note'])
                ->schema([
                    Placeholder::make($key.'_status')
                        ->label('État')
                        ->content(function (Get $get) use ($key): string {
                            $missing = ContributionDesk::missing([
                                'moderator' => $get("{$key}.moderator"),
                                'deputy' => $get("{$key}.deputy"),
                                'hours' => $get("{$key}.hours"),
                                'charter' => $get("{$key}.charter"),
                                'appeal' => $get("{$key}.appeal"),
                            ]);

                            return $missing === []
                                ? 'Ouverte une fois enregistrée.'
                                : 'Fermée. Il manque : '.implode(', ', $missing).'.';
                        })
                        ->columnSpanFull(),
                    TextInput::make("{$key}.moderator")
                        ->label('Modérateur')
                        ->maxLength(120)
                        ->helperText('Nom de la personne responsable. Laisser vide garde la surface fermée.'),
                    TextInput::make("{$key}.deputy")
                        ->label('Suppléant')
                        ->maxLength(120),
                    TextInput::make("{$key}.hours")
                        ->label('Délai (heures)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(168)
                        ->default(48)
                        ->helperText('48 heures est le délai tenu par le cahier des charges.'),
                    Grid::make(3)->schema([
                        Textarea::make("{$key}.charter.fr")->label('Charte (français)')->rows(4),
                        Textarea::make("{$key}.charter.en")->label('Charte (anglais)')->rows(4),
                        Textarea::make("{$key}.charter.ar")->label('Charte (arabe)')->rows(4),
                    ])->columnSpanFull(),
                    Grid::make(3)->schema([
                        Textarea::make("{$key}.appeal.fr")->label('Recours (français)')->rows(3),
                        Textarea::make("{$key}.appeal.en")->label('Recours (anglais)')->rows(3),
                        Textarea::make("{$key}.appeal.ar")->label('Recours (arabe)')->rows(3),
                    ])->columnSpanFull(),
                ])
                ->columns(3);
        }

        return $sections;
    }
}
