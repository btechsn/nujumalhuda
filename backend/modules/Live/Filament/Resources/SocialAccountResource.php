<?php

namespace Modules\Live\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Live\Filament\Resources\SocialAccountResource\Pages;
use Modules\Live\Models\SocialAccount;

class SocialAccountResource extends Resource
{
    protected static ?string $model = SocialAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Réseaux sociaux';

    protected static ?string $navigationLabel = 'Comptes sociaux';

    protected static ?string $modelLabel = 'compte social';

    protected static ?string $pluralModelLabel = 'Comptes sociaux';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')
                ->label('Plateforme')
                ->options(self::platformOptions())
                ->required(),
            Forms\Components\TextInput::make('handle')
                ->label('Identifiant')
                ->required()
                ->maxLength(120)
                ->helperText('Le nom du compte, par exemple @nujumalhuda.'),
            Forms\Components\TextInput::make('url')
                ->label('Adresse')
                ->url()
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('embed_url')
                ->label('Adresse d’intégration')
                ->url()
                ->maxLength(255)
                ->helperText('YouTube et Facebook seulement. TikTok et Instagram restent en redirection.'),
            Forms\Components\Toggle::make('redirect_only')
                ->label('Redirection seule')
                ->helperText('Le visiteur est envoyé vers le réseau, sans lecture intégrée.')
                ->default(false),
            Forms\Components\Toggle::make('is_active')
                ->label('Actif')
                ->default(true),
            Forms\Components\TextInput::make('sort_order')
                ->label('Ordre')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Plateforme')
                    ->formatStateUsing(fn (string $state): string => self::platformOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('handle')->label('Identifiant'),
                Tables\Columns\IconColumn::make('redirect_only')->label('Redirection seule')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function platformOptions(): array
    {
        return [
            'youtube' => 'YouTube',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'instagram' => 'Instagram',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialAccounts::route('/'),
            'create' => Pages\CreateSocialAccount::route('/create'),
            'edit' => Pages\EditSocialAccount::route('/{record}/edit'),
        ];
    }
}
