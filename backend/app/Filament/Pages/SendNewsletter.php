<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Mail\InstituteNewsletter;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Community\Models\NewsletterSubscriber;
use Modules\Core\Support\SiteSettings;
use Throwable;

class SendNewsletter extends Page implements HasForms, HasTable
{
    use InteractsWithFormActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $title = 'Newsletter';

    protected static ?string $slug = 'parametres/newsletter';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.pages.send-newsletter';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $compose = [];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->composeForm->fill([
            'subject' => '',
            'body' => '',
        ]);

        $this->fillNewsletterSettings();
    }

    private function fillNewsletterSettings(): void
    {
        $footer = SiteSettings::formState()['footer'] ?? [];

        $this->form->fill([
            'footer' => array_filter(
                $footer,
                fn (string $key): bool => str_starts_with($key, 'newsletter_'),
                ARRAY_FILTER_USE_KEY,
            ),
        ]);
    }

    public function getSubheading(): ?string
    {
        $count = NewsletterSubscriber::query()->count();

        return $count.' abonné'.($count > 1 ? 's' : '');
    }

    public function composeForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Envoyer')
                    ->description('Le message est envoyé à toutes les adresses inscrites depuis le site.')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Sujet')
                            ->required()
                            ->maxLength(160),
                        RichEditor::make('body')
                            ->label('Message')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                            ]),
                    ]),
            ])
            ->statePath('compose');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Formulaire du site')
                    ->description('Textes du champ d’inscription affiché dans le pied de page.')
                    ->schema([
                        Toggle::make('footer.newsletter_enabled')
                            ->label('Afficher la newsletter')
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('footer.newsletter_placeholder_fr')->label('Champ e-mail (français)')->required()->maxLength(80),
                            TextInput::make('footer.newsletter_placeholder_en')->label('Champ e-mail (anglais)')->required()->maxLength(80),
                            TextInput::make('footer.newsletter_placeholder_ar')->label('Champ e-mail (arabe)')->required()->maxLength(80),
                        ])->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('footer.newsletter_button_fr')->label('Bouton (français)')->required()->maxLength(40),
                            TextInput::make('footer.newsletter_button_en')->label('Bouton (anglais)')->required()->maxLength(40),
                            TextInput::make('footer.newsletter_button_ar')->label('Bouton (arabe)')->required()->maxLength(40),
                        ])->columnSpanFull(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    /**
     * @return array<int, Action>
     */
    public function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Envoyer')
                ->submit('send')
                ->button()
                ->requiresConfirmation()
                ->modalHeading('Envoyer la newsletter')
                ->modalDescription(fn (): string => 'Le message sera envoyé à '.NewsletterSubscriber::query()->count().' abonné(s).'),
            Action::make('saveSettings')
                ->label('Enregistrer le formulaire')
                ->submit('saveSettings')
                ->button()
                ->color('gray'),
        ];
    }

    public function sendAction(): Action
    {
        return collect($this->getCachedFormActions())->first(fn (Action $action): bool => $action->getName() === 'send');
    }

    public function saveSettingsAction(): Action
    {
        return collect($this->getCachedFormActions())->first(fn (Action $action): bool => $action->getName() === 'saveSettings');
    }

    protected function getForms(): array
    {
        return [
            'composeForm',
            'form',
        ];
    }

    public function send(): void
    {
        $state = $this->composeForm->getState();
        $body = trim(strip_tags((string) ($state['body'] ?? '')));

        if ($body === '') {
            Notification::make()->warning()->title('Le message est vide')->send();

            return;
        }

        $subscribers = NewsletterSubscriber::query()->orderBy('email')->get();

        if ($subscribers->isEmpty()) {
            Notification::make()->warning()->title('Aucun abonné')->body('Ajoutez une adresse ou attendez une inscription depuis le site.')->send();

            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new InstituteNewsletter(
                    (string) $state['subject'],
                    (string) $state['body'],
                ));
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        if ($sent === 0) {
            Notification::make()
                ->danger()
                ->title('L’envoi a échoué')
                ->body('Vérifiez le serveur SMTP dans E-mail.')
                ->send();

            return;
        }

        $this->composeForm->fill([
            'subject' => '',
            'body' => '',
        ]);

        $notice = Notification::make()->success()->title('Newsletter envoyée à '.$sent.' abonné'.($sent > 1 ? 's' : ''));

        if ($failed > 0) {
            $notice->body($failed.' envoi'.($failed > 1 ? 's' : '').' en échec.');
        }

        $notice->send();
    }

    public function saveSettings(): void
    {
        SiteSettings::persist($this->form->getState());
        $this->fillNewsletterSettings();

        Notification::make()
            ->success()
            ->title('Formulaire enregistré')
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(NewsletterSubscriber::query())
            ->columns([
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('locale')
                    ->label('Langue')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'en' => 'Anglais',
                        'ar' => 'Arabe',
                        default => 'Français',
                    }),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter')
                    ->modalHeading('Ajouter un abonné')
                    ->form([
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('newsletter_subscribers', 'email'),
                        Select::make('locale')
                            ->label('Langue')
                            ->options([
                                'fr' => 'Français',
                                'en' => 'Anglais',
                                'ar' => 'Arabe',
                            ])
                            ->default('fr')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['email'] = Str::lower($data['email']);

                        return $data;
                    }),
            ])
            ->actions([
                DeleteAction::make()->label('Retirer'),
            ])
            ->emptyStateHeading('Aucun abonné')
            ->emptyStateDescription('Les inscriptions faites sur le site apparaissent ici.');
    }
}
