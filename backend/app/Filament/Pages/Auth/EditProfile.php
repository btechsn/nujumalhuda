<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    protected static string $view = 'filament.pages.auth.edit-profile';

    public function form(Form $form): Form
    {
        return $form;
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Section::make('Identité')
                            ->description('Nom et photo affichés dans le menu.')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 12])
                                    ->schema([
                                        $this->getAvatarFormComponent()
                                            ->columnSpan(['md' => 4]),
                                        Grid::make(2)
                                            ->schema([
                                                $this->getFirstNameFormComponent(),
                                                $this->getLastNameFormComponent(),
                                                $this->getEmailFormComponent()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpan(['md' => 8]),
                                    ]),
                            ]),
                        Section::make('Mot de passe')
                            ->description('Laissez vide pour conserver le mot de passe actuel.')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 2])
                                    ->schema([
                                        $this->getPasswordFormComponent(),
                                        $this->getPasswordConfirmationFormComponent(),
                                    ]),
                            ]),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data'),
            ),
        ];
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar')
            ->label('Photo')
            ->avatar()
            ->image()
            ->placeholder('Ajouter')
            ->disk('public')
            ->directory('avatars')
            ->visibility('public')
            ->maxSize(2048)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->helperText('JPEG, PNG ou WebP, 2 Mo maximum.');
    }

    protected function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label('Prénom')
            ->required()
            ->maxLength(80)
            ->autofocus();
    }

    protected function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label('Nom')
            ->required()
            ->maxLength(80);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Nouveau mot de passe')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Confirmer le mot de passe')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn (Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }
}
