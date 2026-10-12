<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Modules\Core\Support\OperationsDesk;
use Modules\Core\Support\OperationsReport;

class OperationsSettingsPage extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Exploitation';

    protected static ?string $title = 'Exploitation';

    protected static ?string $slug = 'parametres/exploitation';

    protected static ?int $navigationSort = 46;

    protected static string $view = 'filament.pages.manage-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(OperationsDesk::formState());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Phase 3')
                    ->description('La diffusion depuis le centre reste fermée tant que cette fiche est incomplète. Les liens YouTube du site ne dépendent pas de cette qualification.')
                    ->schema([
                        Placeholder::make('status')
                            ->label('État')
                            ->content(function (Get $get): string {
                                $missing = OperationsDesk::missing([
                                    'operator' => $get('operator'),
                                    'deputy' => $get('deputy'),
                                    'training' => $get('training'),
                                    'camera' => $get('camera'),
                                    'microphone' => $get('microphone'),
                                    'encoder' => $get('encoder'),
                                    'uplink_mbps' => $get('uplink_mbps'),
                                    'fallback' => $get('fallback'),
                                    'vps_docker' => $get('vps_docker'),
                                    'vps_port_1935' => $get('vps_port_1935'),
                                    'vps_cpu' => $get('vps_cpu'),
                                    'vps_bandwidth_mbps' => $get('vps_bandwidth_mbps'),
                                    'offsite' => $get('offsite'),
                                    'oncall_name' => $get('oncall_name'),
                                    'oncall_phone' => $get('oncall_phone'),
                                ]);

                                return $missing === []
                                    ? 'Ouverte une fois enregistrée.'
                                    : 'Fermée. Il manque : '.implode(', ', $missing).'.';
                            })
                            ->columnSpanFull(),
                    ]),
                Section::make('SMS Orange')
                    ->description('Les digests et les relances de cotisation n’appellent Orange que si le compte est renseigné. Sans identifiants, rien ne part.')
                    ->schema([
                        Placeholder::make('sms_status')
                            ->label('État')
                            ->content(function (): string {
                                $lines = OperationsReport::collect()['lines'];

                                return implode(' — ', array_values(array_filter(
                                    $lines,
                                    fn (string $line): bool => str_starts_with($line, 'SMS '),
                                )));
                            })
                            ->columnSpanFull(),
                    ]),
                Section::make('Opérateur du direct')
                    ->schema([
                        TextInput::make('operator')->label('Opérateur')->maxLength(120)
                            ->helperText('Personne qui installe la caméra, lance OBS et vérifie le son.'),
                        TextInput::make('deputy')->label('Suppléant')->maxLength(120),
                        Textarea::make('training')->label('Formation prévue')->rows(3)->columnSpanFull(),
                    ])->columns(2),
                Section::make('Matériel du centre')
                    ->schema([
                        TextInput::make('camera')->label('Caméra')->maxLength(160),
                        TextInput::make('microphone')->label('Micro')->maxLength(160),
                        TextInput::make('encoder')->label('Encodeur')->maxLength(160),
                        TextInput::make('uplink_mbps')->label('Débit montant mesuré (Mbit/s)')->numeric()
                            ->helperText('Mesure réelle de la connexion du centre. Un direct 1080p exige un débit montant stable.'),
                    ])->columns(2),
                Section::make('Si le direct tombe')
                    ->schema([
                        Textarea::make('fallback')->label('Procédure de secours')->rows(4)
                            ->helperText('Que faire pendant une khutba si le direct s’interrompt.'),
                    ]),
                Section::make('VPS')
                    ->description('Bloquant pour la phase 3. Ne cochez une case qu’après l’avoir constatée sur le serveur.')
                    ->schema([
                        Toggle::make('vps_docker')->label('Docker est disponible'),
                        Toggle::make('vps_port_1935')->label('Le port 1935 s’ouvre en entrée'),
                        TextInput::make('vps_cpu')->label('CPU constaté')->maxLength(80),
                        TextInput::make('vps_bandwidth_mbps')->label('Bande passante sortante (Mbit/s)')->numeric(),
                    ])->columns(2),
                Section::make('Sauvegarde et astreinte')
                    ->schema([
                        TextInput::make('offsite')->label('Copie hors site')->maxLength(200)->columnSpanFull()
                            ->helperText('Où la copie et la clé de chiffrement sont conservées, hors du serveur.'),
                        TextInput::make('oncall_name')->label('Astreinte')->maxLength(120)
                            ->helperText('Personne joignable si le site tombe un vendredi matin.'),
                        TextInput::make('oncall_phone')->label('Téléphone')->maxLength(30),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        OperationsDesk::persist($state);
        $this->form->fill(OperationsDesk::formState());

        Notification::make()
            ->success()
            ->title(OperationsDesk::isReady() ? 'Phase 3 ouverte' : 'Phase 3 fermée')
            ->body(OperationsDesk::isReady()
                ? 'La diffusion depuis le centre peut démarrer.'
                : 'Il manque encore : '.implode(', ', OperationsDesk::missing($state)).'.')
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
}
