<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\SmsDigestSubscriberResource\Pages;
use Modules\Community\Models\SmsDigestSubscriber;

class SmsDigestSubscriberResource extends Resource
{
    protected static ?string $model = SmsDigestSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Digest SMS';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('phone')->required(),
            Forms\Components\Select::make('locale')->options(['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية']),
            Forms\Components\Toggle::make('is_active'),
            Forms\Components\DateTimePicker::make('consented_at')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\TextColumn::make('locale'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('last_prepared_at')->dateTime()->label('Digest préparé'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsDigestSubscribers::route('/'),
            'edit' => Pages\EditSmsDigestSubscriber::route('/{record}/edit'),
        ];
    }
}
