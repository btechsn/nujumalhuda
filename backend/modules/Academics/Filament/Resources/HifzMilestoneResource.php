<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Academics\Filament\Resources\HifzMilestoneResource\Pages;
use Modules\Academics\Models\HifzMilestone;

class HifzMilestoneResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('academics');
    }

    protected static ?string $model = HifzMilestone::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Paliers de mémorisation';

    protected static ?string $modelLabel = 'palier';

    protected static ?string $pluralModelLabel = 'paliers';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')
                ->label('Identifiant')
                ->helperText('Court nom interne, par exemple juz-amma. Il n’apparaît pas sur le site.')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('title_i18n.fr')->label('Titre affiché')->required(),
            Forms\Components\TextInput::make('title_i18n.en')->label('Titre en anglais'),
            Forms\Components\TextInput::make('title_i18n.ar')->label('العنوان'),
            Forms\Components\Textarea::make('description_i18n.fr')->label('Description'),
            Forms\Components\TextInput::make('from_juz')->label('Juz de début')->numeric()->minValue(1)->maxValue(30),
            Forms\Components\TextInput::make('to_juz')->label('Juz de fin')->numeric()->minValue(1)->maxValue(30),
            Forms\Components\TextInput::make('display_order')->label('Ordre')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('display_order')->label('Ordre')->sortable(),
            Tables\Columns\TextColumn::make('title_i18n.fr')->label('Palier')->searchable(),
            Tables\Columns\TextColumn::make('from_juz')->label('Juz de début'),
            Tables\Columns\TextColumn::make('to_juz')->label('Juz de fin'),
        ])->defaultSort('display_order')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHifzMilestones::route('/'),
            'create' => Pages\CreateHifzMilestone::route('/create'),
            'edit' => Pages\EditHifzMilestone::route('/{record}/edit'),
        ];
    }
}
