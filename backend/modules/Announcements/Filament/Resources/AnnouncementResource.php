<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Announcements\Enums\AnnouncementCategory;
use Modules\Announcements\Enums\AnnouncementPriority;
use Modules\Announcements\Filament\Resources\AnnouncementResource\Pages;
use Modules\Announcements\Models\Announcement;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Actualités';

    protected static ?string $navigationLabel = 'Annonces';

    protected static ?string $modelLabel = 'annonce';

    protected static ?string $pluralModelLabel = 'annonces';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Titre')
                ->schema([
                    Forms\Components\TextInput::make('title.fr')
                        ->label('Titre (français)')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\TextInput::make('title.en')
                        ->label('Titre (anglais)')
                        ->maxLength(180),
                    Forms\Components\TextInput::make('title.ar')
                        ->label('Titre (arabe)')
                        ->maxLength(180),
                ])
                ->columns(3),
            Forms\Components\Section::make('Message')
                ->schema([
                    Forms\Components\Textarea::make('message.fr')
                        ->label('Message (français)')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('message.en')
                        ->label('Message (anglais)')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('message.ar')
                        ->label('Message (arabe)')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Publication')
                ->schema([
                    Forms\Components\Select::make('category')
                        ->label('Catégorie')
                        ->options(self::categoryOptions())
                        ->required()
                        ->default(AnnouncementCategory::CENTER->value),
                    Forms\Components\Select::make('priority')
                        ->label('Priorité')
                        ->options(self::priorityOptions())
                        ->required()
                        ->default(AnnouncementPriority::NORMAL->value),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Publiée')
                        ->default(true),
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Visible à partir de')
                        ->seconds(false),
                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Visible jusqu’au')
                        ->seconds(false)
                        ->after('starts_at'),
                    Forms\Components\TextInput::make('action_url')
                        ->label('Lien interne ou externe')
                        ->maxLength(255)
                        ->placeholder('https://youtube.com/… ou /centre/khutbas')
                        ->helperText('Adresse externe (https://…) ou page du site (/centre/khutbas).'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(query: function ($query, string $search) {
                        $like = '%'.$search.'%';
                        $query->where(function ($query) use ($like) {
                            $query->where('title->fr', 'ilike', $like)
                                ->orWhere('title->en', 'ilike', $like)
                                ->orWhere('message->fr', 'ilike', $like);
                        });
                    })
                    ->limit(50),
                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn (AnnouncementCategory|string $state): string => self::categoryLabel($state)),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Priorité')
                    ->badge()
                    ->formatStateUsing(fn (AnnouncementPriority|string $state): string => self::priorityLabel($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Publiée')
                    ->boolean(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('–'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function categoryOptions(): array
    {
        $options = [];

        foreach (AnnouncementCategory::cases() as $category) {
            $options[$category->value] = $category->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function priorityOptions(): array
    {
        $options = [];

        foreach (AnnouncementPriority::cases() as $priority) {
            $options[$priority->value] = $priority->label();
        }

        return $options;
    }

    private static function categoryLabel(AnnouncementCategory|string $state): string
    {
        $category = $state instanceof AnnouncementCategory
            ? $state
            : AnnouncementCategory::tryFrom($state);

        return $category?->label() ?? (string) $state;
    }

    private static function priorityLabel(AnnouncementPriority|string $state): string
    {
        $priority = $state instanceof AnnouncementPriority
            ? $state
            : AnnouncementPriority::tryFrom($state);

        return $priority?->label() ?? (string) $state;
    }
}
