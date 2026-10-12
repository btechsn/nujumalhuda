<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Concerns\ScopesToDahira;
use Modules\Dahira\Filament\Resources\TreasuryEntryResource\Pages;
use Modules\Dahira\Models\TreasuryEntry;
use Modules\Dahira\Services\DahiraAccess;

class TreasuryEntryResource extends Resource
{
    use ScopesToDahira;

    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('dahira_money');
    }

    protected static function officerRoles(): array
    {
        return DahiraAccess::TREASURER_ROLES;
    }

    protected static ?string $model = TreasuryEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Trésorerie';

    protected static ?string $modelLabel = 'écriture';

    protected static ?string $pluralModelLabel = 'Trésorerie';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('dahira_group_id')
                ->label('Dahira')
                ->relationship('group', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name_i18n['fr'] ?? $record->id)
                ->searchable()
                ->required(),
            Forms\Components\Select::make('direction')
                ->label('Sens')
                ->options(['in' => 'Entrée', 'out' => 'Sortie'])
                ->required(),
            Forms\Components\Select::make('category')
                ->label('Catégorie')
                ->options([
                    'contribution' => 'Cotisation',
                    'donation' => 'Don',
                    'expense' => 'Dépense',
                    'adjustment' => 'Ajustement',
                ])
                ->required(),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required()->label('Montant (XOF)'),
            Forms\Components\TextInput::make('label')->label('Libellé')->required(),
            Forms\Components\DatePicker::make('occurred_on')->label('Date')->required(),
            Forms\Components\Textarea::make('note')->label('Note'),
            Forms\Components\Hidden::make('recorded_by')->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('occurred_on')->label('Date')->date(),
            Tables\Columns\TextColumn::make('label')->label('Libellé'),
            Tables\Columns\TextColumn::make('direction')
                ->label('Sens')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => $state === 'out' ? 'Sortie' : 'Entrée'),
            Tables\Columns\TextColumn::make('category')
                ->label('Catégorie')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'donation' => 'Don',
                    'expense' => 'Dépense',
                    'adjustment' => 'Ajustement',
                    default => 'Cotisation',
                }),
            Tables\Columns\TextColumn::make('amount_minor')->label('Montant (XOF)'),
        ])->defaultSort('occurred_on', 'desc')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreasuryEntries::route('/'),
            'create' => Pages\CreateTreasuryEntry::route('/create'),
            'edit' => Pages\EditTreasuryEntry::route('/{record}/edit'),
        ];
    }
}
