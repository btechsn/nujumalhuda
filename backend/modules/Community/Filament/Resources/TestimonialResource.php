<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\TestimonialResource\Pages;
use Modules\Community\Models\Testimonial;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Témoignages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('author_name')->required(),
            Forms\Components\Select::make('relation')->options([
                'parent' => 'Parent',
                'alumni' => 'Ancien élève',
                'other' => 'Autre',
            ])->required(),
            Forms\Components\Textarea::make('content_i18n.fr')->label('Témoignage')->required(),
            Forms\Components\Select::make('status')->options([
                'pending' => 'En attente',
                'approved' => 'Publié',
                'rejected' => 'Refusé',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('author_name'),
            Tables\Columns\TextColumn::make('relation')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
