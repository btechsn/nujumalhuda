<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Models\IqamaAdjustment;

class IqamaAdjustmentResource extends Resource
{
    protected static ?string $model = IqamaAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Zawiya';
    protected static ?string $navigationLabel = 'Décalages Iqama';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Prière')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->organization_id),
                        
                        Forms\Components\Select::make('prayer_name')
                            ->label('Prière')
                            ->options([
                                'fajr' => 'Fajr',
                                'dhuhr' => 'Dhuhr',
                                'asr' => 'Asr',
                                'maghrib' => 'Maghrib',
                                'isha' => 'Isha',
                            ])
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Décalage')
                    ->schema([
                        Forms\Components\TextInput::make('minutes_offset')
                            ->label('Décalage (minutes)')
                            ->required()
                            ->numeric()
                            ->default(15)
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix('min')
                            ->helperText('Temps entre l\'adhan et l\'iqama'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->placeholder('Ex: Horaire d\'hiver, Période Ramadan')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Période de validité')
                    ->schema([
                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Valide du')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('valid_to')
                            ->label('Valide jusqu\'au')
                            ->after('valid_from')
                            ->helperText('Laissez vide pour une validité indéfinie'),
                    ])->columns(2),
                
                Forms\Components\Section::make('État')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('prayer_name')
                    ->label('Prière')
                    ->colors([
                        'primary' => 'fajr',
                        'warning' => 'dhuhr',
                        'info' => 'asr',
                        'danger' => 'maghrib',
                        'secondary' => 'isha',
                    ]),
                
                Tables\Columns\TextColumn::make('minutes_offset')
                    ->label('Décalage')
                    ->suffix(' min')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Du')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('valid_to')
                    ->label('Au')
                    ->date('d/m/Y')
                    ->default('Indéfini')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('prayer_name')
                    ->options([
                        'fajr' => 'Fajr',
                        'dhuhr' => 'Dhuhr',
                        'asr' => 'Asr',
                        'maghrib' => 'Maghrib',
                        'isha' => 'Isha',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('valid_from', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages\ListIqamaAdjustments::route('/'),
            'create' => \Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages\CreateIqamaAdjustment::route('/create'),
            'edit' => \Modules\Mosque\Filament\Resources\IqamaAdjustmentResource\Pages\EditIqamaAdjustment::route('/{record}/edit'),
        ];
    }
}
