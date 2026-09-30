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

    protected static ?string $navigationGroup = 'Direct & Médias';

    protected static ?string $navigationLabel = 'Comptes sociaux';

    protected static ?string $modelLabel = 'compte social';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')
                ->options([
                    'youtube' => 'YouTube',
                    'facebook' => 'Facebook',
                    'tiktok' => 'TikTok',
                    'instagram' => 'Instagram',
                ])
                ->required(),
            Forms\Components\TextInput::make('handle')->required()->maxLength(120),
            Forms\Components\TextInput::make('url')->url()->required()->maxLength(255),
            Forms\Components\TextInput::make('embed_url')
                ->url()
                ->maxLength(255)
                ->helperText('YouTube et Facebook seulement. TikTok et Instagram restent en redirection.'),
            Forms\Components\Toggle::make('redirect_only')->default(false),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform'),
                Tables\Columns\TextColumn::make('handle'),
                Tables\Columns\IconColumn::make('redirect_only')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
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
