<?php

namespace Modules\Community\Filament\Resources;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Modules\Community\Filament\Resources\ContactMessageResource\Pages;
use Modules\Community\Models\ContactMessage;
use Throwable;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Messages contact';

    protected static ?string $modelLabel = 'Message';

    protected static ?string $pluralModelLabel = 'Messages contact';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->label('Prénom')->disabled(),
            Forms\Components\TextInput::make('last_name')->label('Nom')->disabled(),
            Forms\Components\TextInput::make('email')->label('Email')->disabled(),
            Forms\Components\TextInput::make('phone')->label('Téléphone')->disabled(),
            Forms\Components\TextInput::make('subject')->label('Sujet')->disabled(),
            Forms\Components\Textarea::make('message')->label('Message')->rows(8)->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('reply_message')
                ->label('Réponse envoyée')
                ->rows(6)
                ->disabled()
                ->visible(fn (?ContactMessage $record): bool => filled($record?->reply_message))
                ->columnSpanFull(),
            Forms\Components\Select::make('status')->label('Statut')->options(self::statusOptions()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('last_name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('Prénom')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')->label('Tél.')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('subject')->label('Sujet')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'read' => 'gray',
                        'replied' => 'success',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label('Reçu le')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::statusOptions()),
            ])
            ->actions([
                self::tableReplyAction(),
                Tables\Actions\ViewAction::make()
                    ->after(function (ContactMessage $record) {
                        if ($record->status === 'new') {
                            $record->update(['status' => 'read', 'read_at' => now()]);
                        }
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'new' => 'Nouveau',
            'read' => 'Lu',
            'replied' => 'Répondu',
            'archived' => 'Archivé',
        ];
    }

    public static function sendReply(ContactMessage $record, string $reply): void
    {
        $reply = trim($reply);

        if (blank($record->email)) {
            throw new \InvalidArgumentException('Ce contact n’a pas d’adresse email.');
        }

        if ($reply === '') {
            throw new \InvalidArgumentException('Écrivez une réponse.');
        }

        $name = $record->fullName();
        $body = implode("\n\n", [
            'Salam alaykoum'.($name !== '' ? ' '.$name : '').',',
            $reply,
            "—\nVotre message (« {$record->subject} ») :\n{$record->message}",
            'Nujum Al-Huda',
        ]);

        Mail::raw($body, function ($message) use ($record, $name): void {
            $message->to($record->email, $name !== '' ? $name : null)
                ->subject('Re: '.$record->subject);
        });

        $record->update([
            'reply_message' => $reply,
            'replied_at' => now(),
            'replied_by' => auth()->id(),
            'status' => 'replied',
            'read_at' => $record->read_at ?? now(),
        ]);
    }

    public static function notifyReplySent(ContactMessage $record): void
    {
        $logged = config('mail.default') === 'log';

        Notification::make()
            ->title($logged ? 'Réponse enregistrée' : 'Réponse envoyée')
            ->body($logged
                ? 'Le texte est conservé sur la fiche. L’envoi d’emails n’est pas encore activé sur le serveur.'
                : 'Envoyée à '.$record->email)
            ->success()
            ->send();
    }

    public static function notifyReplyFailed(Throwable $error): void
    {
        Notification::make()
            ->title('La réponse n’a pas été envoyée')
            ->body($error->getMessage())
            ->danger()
            ->send();
    }

    public static function tableReplyAction(): Tables\Actions\Action
    {
        return self::configureReplyAction(Tables\Actions\Action::make('reply'));
    }

    public static function pageReplyAction(): Action
    {
        return self::configureReplyAction(Action::make('reply'))
            ->action(function (ContactMessage $record, array $data, ViewRecord $livewire): void {
                if (! self::attemptReply($record, (string) ($data['reply_message'] ?? ''))) {
                    return;
                }

                $livewire->refreshFormData(['reply_message', 'status', 'replied_at']);
            });
    }

    private static function configureReplyAction(Action | Tables\Actions\Action $action): Action | Tables\Actions\Action
    {
        return $action
            ->label('Répondre')
            ->icon('heroicon-o-paper-airplane')
            ->modalHeading('Répondre au message')
            ->modalSubmitActionLabel('Envoyer')
            ->modalDescription(fn (ContactMessage $record): string => filled($record->email)
                ? 'La réponse part à '.$record->email.'.'
                : 'Ce contact n’a pas d’email'.(filled($record->phone) ? '. Téléphone : '.$record->phone.'.' : '.'))
            ->modalSubmitAction(fn (ContactMessage $record) => filled($record->email) ? null : false)
            ->form(fn (ContactMessage $record): array => filled($record->email) ? [
                Forms\Components\Textarea::make('reply_message')
                    ->label('Votre réponse')
                    ->required()
                    ->minLength(2)
                    ->rows(8),
            ] : [])
            ->action(function (ContactMessage $record, array $data): void {
                self::attemptReply($record, (string) ($data['reply_message'] ?? ''));
            });
    }

    private static function attemptReply(ContactMessage $record, string $reply): bool
    {
        try {
            self::sendReply($record, $reply);
        } catch (Throwable $error) {
            self::notifyReplyFailed($error);

            return false;
        }

        self::notifyReplySent($record);

        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::query()->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }
}
