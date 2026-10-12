<?php

namespace Modules\News\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Models\Media;
use Modules\Core\Models\Organization;
use Modules\Core\Models\User;
use Modules\News\Models\Article;

class ArticleResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('news');
    }

    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Actualités';
    protected static ?string $navigationLabel = 'Articles';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Hidden::make('author_id')
                            ->default(fn() => auth()->id()),
                        
                        Forms\Components\Hidden::make('organization_id')
                            ->default(fn (): ?string => self::currentOrganizationId()),
                        
                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name_i18n->fr')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Titre et slug')
                    ->schema([
                        Forms\Components\TextInput::make('title_i18n.fr')
                            ->label('Titre (Français)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state)))
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('title_i18n.en')
                            ->label('Title (English)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('title_i18n.ar')
                            ->label('العنوان (عربي)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Généré automatiquement depuis le titre français'),
                    ]),
                
                Forms\Components\Section::make('Résumé')
                    ->schema([
                        Forms\Components\Textarea::make('excerpt_i18n.fr')
                            ->label('Résumé (Français)')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('excerpt_i18n.en')
                            ->label('Excerpt (English)')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('excerpt_i18n.ar')
                            ->label('الملخص (عربي)')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('content_i18n.fr')
                            ->label('Contenu (Français)')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('content_i18n.en')
                            ->label('Content (English)')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('content_i18n.ar')
                            ->label('المحتوى (عربي)')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Image de couverture')
                    ->schema([
                        Forms\Components\FileUpload::make('cover')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('articles/covers')
                            ->visibility('public')
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Choisissez une image sur votre ordinateur. JPEG, PNG ou WebP, 4 Mo maximum.'),
                    ]),
                
                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\Textarea::make('meta_description_i18n.fr')
                            ->label('Meta description (Français)')
                            ->rows(2)
                            ->maxLength(160)
                            ->columnSpanFull(),
                        
                        Forms\Components\TagsInput::make('meta_keywords')
                            ->label('Mots-clés')
                            ->helperText('Pour le référencement')
                            ->columnSpanFull(),
                        
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->helperText('Pour le filtrage dans l\'application')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Publication')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Brouillon',
                                'published' => 'Publié',
                                'archived' => 'Archivé',
                            ])
                            ->required()
                            ->default('draft'),
                        
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->default(now()),
                    ])->columns(2),
                
                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Vedette')
                            ->helperText('Affiche cet article dans le slider de la page Centre')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('allow_comments')
                            ->label('Autoriser les commentaires')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover')
                    ->label('Image')
                    ->circular()
                    ->state(fn (Article $record): ?string => $record->coverImage?->url()),
                
                Tables\Columns\TextColumn::make('title_i18n.fr')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category.name_i18n.fr')
                    ->label('Catégorie')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('author.first_name')
                    ->label('Auteur')
                    ->formatStateUsing(fn (mixed $state, Article $record): string => $record->author?->fullName() ?: '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('author', function (Builder $author) use ($search): void {
                            $like = '%'.$search.'%';
                            $author->where('first_name', 'ilike', $like)
                                ->orWhere('last_name', 'ilike', $like)
                                ->orWhereRaw("concat(first_name, ' ', last_name) ilike ?", [$like]);
                        });
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'secondary' => 'archived',
                    ]),
                
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vues')
                    ->sortable()
                    ->suffix(' vues'),
                
                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Commentaires')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name_i18n->fr')
                    ->label('Catégorie'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Auteur')
                    ->options(fn (): array => User::query()
                        ->whereIn('id', Article::query()->select('author_id'))
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn (User $user): array => [
                            $user->getKey() => $user->fullName() !== '' ? $user->fullName() : ($user->email ?: (string) $user->phone),
                        ])
                        ->all())
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Vedette'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function currentOrganizationId(): ?string
    {
        $fromMembership = auth()->user()?->memberships()->value('organization_id');
        if (filled($fromMembership)) {
            return $fromMembership;
        }

        return Organization::query()->orderBy('created_at')->value('id');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyCover(array $data, ?string $articleId = null, ?string $currentMediaId = null): array
    {
        $path = self::uploadedPath($data['cover'] ?? null);
        unset($data['cover']);

        if ($path === null) {
            $data['cover_image_id'] = null;

            return $data;
        }

        $current = filled($currentMediaId) ? Media::query()->find($currentMediaId) : null;
        if ($current && $current->path === $path) {
            $data['cover_image_id'] = $current->id;

            return $data;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            $data['cover_image_id'] = $current?->id;

            return $data;
        }

        $absolute = $disk->path($path);
        $info = is_file($absolute) ? @getimagesize($absolute) : false;
        $fileName = basename($path);

        $media = Media::query()->create([
            'mediable_type' => Article::class,
            'mediable_id' => $articleId,
            'uploaded_by' => auth()->id(),
            'collection' => 'cover',
            'name' => pathinfo($fileName, PATHINFO_FILENAME) ?: $fileName,
            'file_name' => $fileName,
            'mime_type' => $disk->mimeType($path) ?: 'image/jpeg',
            'disk' => 'public',
            'path' => $path,
            'size' => $disk->size($path) ?: 0,
            'width' => is_array($info) ? ($info[0] ?? null) : null,
            'height' => is_array($info) ? ($info[1] ?? null) : null,
        ]);

        $data['cover_image_id'] = $media->id;

        return $data;
    }

    private static function uploadedPath(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = array_values(array_filter($value, 'is_string'))[0] ?? null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\News\Filament\Resources\ArticleResource\Pages\ListArticles::route('/'),
            'create' => \Modules\News\Filament\Resources\ArticleResource\Pages\CreateArticle::route('/create'),
            'edit' => \Modules\News\Filament\Resources\ArticleResource\Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
