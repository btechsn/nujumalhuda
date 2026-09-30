<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Filament\Resources\PrayerTimeHistoryResource\Pages;
use Modules\Mosque\Models\PrayerTimeHistory;

class PrayerTimeHistoryResource extends Resource
{
    protected static ?string $model = PrayerTimeHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Historique horaires';

    protected static ?string $navigationGroup = 'Mosquée';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false; // Historique en lecture seule
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Lecture seule
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prayerTime.date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('prayerTime.prayer_name')
                    ->label('Prière')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fajr' => 'Fajr',
                        'dhuhr' => 'Dhuhr',
                        'asr' => 'Asr',
                        'maghrib' => 'Maghrib',
                        'isha' => 'Isha',
                        default => $state,
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('change_type')
                    ->label('Type de modification')
                    ->colors([
                        'warning' => 'manual_override',
                        'success' => 'remove_override',
                        'info' => 'iqama_adjustment',
                        'gray' => 'recalculation',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual_override' => 'Override manuel',
                        'remove_override' => 'Retrait override',
                        'iqama_adjustment' => 'Ajustement iqama',
                        'recalculation' => 'Recalcul',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('old_calculated_time')
                    ->label('Ancien horaire')
                    ->time('H:i')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('new_calculated_time')
                    ->label('Nouveau horaire')
                    ->time('H:i')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('old_manual_time')
                    ->label('Ancien manuel')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('new_manual_time')
                    ->label('Nouveau manuel')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('old_is_overridden')
                    ->label('Était overridé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('new_is_overridden')
                    ->label('Est overridé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Modifié par')
                    ->default('Système')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Raison')
                    ->limit(40)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('changed_at')
                    ->label('Date modification')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('change_type')
                    ->label('Type')
                    ->options([
                        'manual_override' => 'Override manuel',
                        'remove_override' => 'Retrait override',
                        'iqama_adjustment' => 'Ajustement iqama',
                        'recalculation' => 'Recalcul',
                    ]),

                Tables\Filters\SelectFilter::make('prayer_name')
                    ->label('Prière')
                    ->options([
                        'fajr' => 'Fajr',
                        'dhuhr' => 'Dhuhr',
                        'asr' => 'Asr',
                        'maghrib' => 'Maghrib',
                        'isha' => 'Isha',
                    ])
                    ->attribute('prayerTime.prayer_name'),

                Tables\Filters\Filter::make('changed_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('changed_at', '>=', $date))
                            ->when($data['to'], fn ($q, $date) => $q->whereDate('changed_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('changed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrayerTimeHistory::route('/'),
            'view' => Pages\ViewPrayerTimeHistory::route('/{record}'),
        ];
    }
}
