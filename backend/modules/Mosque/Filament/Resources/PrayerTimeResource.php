<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Models\PrayerTime;
use Modules\Mosque\Services\PrayerTimeService;

class PrayerTimeResource extends Resource
{
    protected static ?string $model = PrayerTime::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Mosquée';
    protected static ?string $navigationLabel = 'Horaires de prière';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Date et prière')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->organization_id),
                        
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\Select::make('prayer_name')
                            ->options([
                                'fajr' => 'Fajr',
                                'dhuhr' => 'Dhuhr',
                                'asr' => 'Asr',
                                'maghrib' => 'Maghrib',
                                'isha' => 'Isha',
                            ])
                            ->required(),
                    ])->columns(3),
                
                Forms\Components\Section::make('Horaire automatique')
                    ->schema([
                        Forms\Components\TimePicker::make('calculated_time')
                            ->label('Heure calculée (API Aladhan)')
                            ->disabled()
                            ->helperText('Calculé automatiquement via l\'API Aladhan'),
                        
                        Forms\Components\TextInput::make('calculation_method')
                            ->label('Méthode de calcul')
                            ->default('MWL')
                            ->disabled(),
                    ])->columns(2),
                
                Forms\Components\Section::make('⚠️ Override manuel (priorité imam)')
                    ->description('L\'heure manuelle a TOUJOURS priorité sur le calcul automatique')
                    ->schema([
                        Forms\Components\TimePicker::make('manual_time')
                            ->label('Heure manuelle (override)')
                            ->helperText('Laissez vide pour utiliser le calcul automatique'),
                        
                        Forms\Components\Textarea::make('override_reason')
                            ->label('Raison de l\'override')
                            ->rows(2)
                            ->helperText('Ex: Correction selon l\'observation locale'),
                        
                        Forms\Components\Placeholder::make('overridden_info')
                            ->label('Informations override')
                            ->content(function ($record) {
                                if (!$record || !$record->is_overridden) {
                                    return 'Pas d\'override actif';
                                }
                                
                                return "Overridé par {$record->overriddenBy?->name} le " . 
                                    $record->overridden_at?->format('d/m/Y H:i');
                            })
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Iqama')
                    ->schema([
                        Forms\Components\TimePicker::make('iqama_time')
                            ->label('Heure de l\'iqama')
                            ->helperText('Calculé automatiquement avec les décalages configurés')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('prayer_name')
                    ->label('Prière')
                    ->colors([
                        'primary' => 'fajr',
                        'warning' => 'dhuhr',
                        'info' => 'asr',
                        'danger' => 'maghrib',
                        'secondary' => 'isha',
                    ]),
                
                Tables\Columns\TextColumn::make('calculated_time')
                    ->label('Calculé')
                    ->time('H:i'),
                
                Tables\Columns\TextColumn::make('manual_time')
                    ->label('Manuel')
                    ->time('H:i')
                    ->default('-'),
                
                Tables\Columns\IconColumn::make('is_overridden')
                    ->label('Override')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('warning'),
                
                Tables\Columns\TextColumn::make('iqama_time')
                    ->label('Iqama')
                    ->time('H:i'),
                
                Tables\Columns\TextColumn::make('overriddenBy.name')
                    ->label('Overridé par')
                    ->default('-')
                    ->limit(20),
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
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('De'),
                        Forms\Components\DatePicker::make('until')->label('À'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
                Tables\Filters\TernaryFilter::make('is_overridden')
                    ->label('Avec override manuel'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('remove_override')
                    ->label('Supprimer override')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn($record) => $record->is_overridden)
                    ->requiresConfirmation()
                    ->action(function ($record, PrayerTimeService $service) {
                        $record->removeOverride();
                        $service->clearCacheForDate($record->date->format('Y-m-d'), $record->organization_id);
                        
                        Notification::make()
                            ->success()
                            ->title('Override supprimé')
                            ->body('Le système utilise à nouveau le calcul automatique')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Mosque\Filament\Resources\PrayerTimeResource\Pages\ListPrayerTimes::route('/'),
            'create' => \Modules\Mosque\Filament\Resources\PrayerTimeResource\Pages\CreatePrayerTime::route('/create'),
            'edit' => \Modules\Mosque\Filament\Resources\PrayerTimeResource\Pages\EditPrayerTime::route('/{record}/edit'),
        ];
    }
}
