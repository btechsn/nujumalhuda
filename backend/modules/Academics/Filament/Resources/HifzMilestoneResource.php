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
    protected static ?string $model = HifzMilestone::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Paliers de hifz';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('title_i18n.fr')->label('Titre (français)')->required(),
            Forms\Components\TextInput::make('title_i18n.en')->label('Title (English)'),
            Forms\Components\TextInput::make('title_i18n.ar')->label('العنوان'),
            Forms\Components\Textarea::make('description_i18n.fr')->label('Description'),
            Forms\Components\TextInput::make('from_juz')->numeric()->minValue(1)->maxValue(30),
            Forms\Components\TextInput::make('to_juz')->numeric()->minValue(1)->maxValue(30),
            Forms\Components\TextInput::make('display_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('display_order')->label('Ordre')->sortable(),
            Tables\Columns\TextColumn::make('title_i18n.fr')->label('Palier')->searchable(),
            Tables\Columns\TextColumn::make('from_juz'),
            Tables\Columns\TextColumn::make('to_juz'),
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
