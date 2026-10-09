<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Education\Filament\Resources\ProgramTypeResource\Pages;
use Modules\Education\Models\Program;
use Modules\Education\Models\ProgramType;

class ProgramTypeResource extends Resource
{
    protected static ?string $model = ProgramType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Éducation';

    protected static ?string $navigationLabel = 'Types de parcours';

    protected static ?string $modelLabel = 'type de parcours';

    protected static ?string $pluralModelLabel = 'types de parcours';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Nom')
                ->schema([
                    Forms\Components\TextInput::make('name_i18n.fr')
                        ->label('Nom (français)')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Str::slug((string) $state));
                        }),
                    Forms\Components\TextInput::make('name_i18n.en')
                        ->label('Nom (anglais)')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('name_i18n.ar')
                        ->label('Nom (arabe)')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('slug')
                        ->label('Identifiant')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated()
                        ->helperText('Utilisé par les programmes. Il ne change plus après la création.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_i18n.fr')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Identifiant'),
                Tables\Columns\TextColumn::make('programs_count')
                    ->counts('programs')
                    ->label('Programmes'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (ProgramType $record, Tables\Actions\DeleteAction $action): void {
                        if (Program::query()->where('type', $record->slug)->exists()) {
                            Notification::make()
                                ->title('Ce parcours est utilisé par des programmes')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgramTypes::route('/'),
            'create' => Pages\CreateProgramType::route('/create'),
            'edit' => Pages\EditProgramType::route('/{record}/edit'),
        ];
    }
}
