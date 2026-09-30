<?php

namespace Modules\News\Filament\Resources\ArticleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Modules\News\Filament\Resources\ArticleResource;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

}
