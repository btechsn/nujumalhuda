<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Core\Enums\OrganizationType;
use Modules\Core\Models\Organization;
use Modules\Dahira\Filament\Resources\DahiraGroupResource\Pages;
use Modules\Dahira\Models\DahiraGroup;

class DahiraGroupResource extends Resource
{
    protected static ?string $model = DahiraGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Dahiras';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('organization_id')
                ->label('Organisation')
                ->options(fn () => Organization::query()->where('type', OrganizationType::DAHIRA)->pluck('slug', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name_i18n.fr')->label('Nom (français)')->required(),
            Forms\Components\TextInput::make('name_i18n.en')->label('Name'),
            Forms\Components\TextInput::make('name_i18n.ar')->label('الاسم'),
            Forms\Components\Textarea::make('description_i18n.fr')->label('Description'),
            Forms\Components\DatePicker::make('founded_on'),
            Forms\Components\TextInput::make('meeting_weekday')->numeric()->minValue(0)->maxValue(6)->helperText('0 = dimanche, 5 = vendredi'),
            Forms\Components\TextInput::make('location'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name_i18n.fr')->label('Nom')->searchable(),
            Tables\Columns\TextColumn::make('organization.slug')->label('Organisation'),
            Tables\Columns\TextColumn::make('location'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDahiraGroups::route('/'),
            'create' => Pages\CreateDahiraGroup::route('/create'),
            'edit' => Pages\EditDahiraGroup::route('/{record}/edit'),
        ];
    }
}
