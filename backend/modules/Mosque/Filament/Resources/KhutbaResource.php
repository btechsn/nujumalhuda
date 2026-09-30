<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Models\Khutba;

class KhutbaResource extends Resource
{
    protected static ?string $model = Khutba::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Mosquée';
    protected static ?string $navigationLabel = 'Khutbas';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->organization_id),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('Date (Vendredi)')
                            ->required()
                            ->default(now()->next('Friday')),
                        
                        Forms\Components\TimePicker::make('time')
                            ->label('Heure')
                            ->default('13:30'),
                    ])->columns(3),
                
                Forms\Components\Section::make('Orateur')
                    ->schema([
                        Forms\Components\Select::make('speaker_id')
                            ->label('Orateur (enseignant)')
                            ->relationship('speaker', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('speaker_name')
                            ->label('Orateur externe')
                            ->maxLength(255)
                            ->helperText('Si l\'orateur n\'est pas un enseignant du centre'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Titre')
                    ->schema([
                        Forms\Components\TextInput::make('title_i18n.fr')
                            ->label('Titre (Français)')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('title_i18n.en')
                            ->label('Title (English)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('title_i18n.ar')
                            ->label('العنوان (عربي)')
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Section::make('Résumé')
                    ->schema([
                        Forms\Components\Textarea::make('summary_i18n.fr')
                            ->label('Résumé (Français)')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('summary_i18n.en')
                            ->label('Summary (English)')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('summary_i18n.ar')
                            ->label('الملخص (عربي)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Contenu complet')
                    ->schema([
                        Forms\Components\RichEditor::make('content_i18n.fr')
                            ->label('Transcription (Français)')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('key_points')
                            ->label('Points clés')
                            ->rows(4)
                            ->helperText('Un point par ligne')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Médias')
                    ->schema([
                        Forms\Components\Select::make('audio_media_id')
                            ->label('Audio')
                            ->relationship('audio', 'filename')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('video_media_id')
                            ->label('Vidéo')
                            ->relationship('video', 'filename')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('youtube_url')
                            ->label('URL YouTube')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/watch?v=...'),
                    ])->columns(3),
                
                Forms\Components\Section::make('Références')
                    ->schema([
                        Forms\Components\KeyValue::make('references')
                            ->label('Versets et hadiths cités')
                            ->keyLabel('Type')
                            ->valueLabel('Référence')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Publication')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Publié')
                            ->default(false),
                        
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->default(now()),
                    ])->columns(2),
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
                
                Tables\Columns\TextColumn::make('title_i18n.fr')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('speaker.name')
                    ->label('Orateur')
                    ->default(fn($record) => $record->speaker_name ?? '-')
                    ->limit(30),
                
                Tables\Columns\IconColumn::make('audio_media_id')
                    ->label('Audio')
                    ->boolean()
                    ->trueIcon('heroicon-o-speaker-wave')
                    ->trueColor('success'),
                
                Tables\Columns\IconColumn::make('video_media_id')
                    ->label('Vidéo')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->trueColor('success'),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
                Tables\Filters\TernaryFilter::make('is_published')->label('Publié'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => \Modules\Mosque\Filament\Resources\KhutbaResource\Pages\ListKhutbas::route('/'),
            'create' => \Modules\Mosque\Filament\Resources\KhutbaResource\Pages\CreateKhutba::route('/create'),
            'edit' => \Modules\Mosque\Filament\Resources\KhutbaResource\Pages\EditKhutba::route('/{record}/edit'),
        ];
    }
}
