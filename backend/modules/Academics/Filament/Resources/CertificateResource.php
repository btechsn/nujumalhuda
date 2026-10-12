<?php

declare(strict_types=1);

namespace Modules\Academics\Filament\Resources;

use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Academics\Filament\Resources\CertificateResource\Pages;
use Modules\Academics\Models\Certificate;
use Modules\Core\Models\AuditLog;

/**
 * Les attestations sont émises automatiquement au franchissement d'un
 * palier (voir CertificateIssuer) : il n'y a donc ni création ni édition
 * manuelle ici, seulement la bascule « publique », verrouillée par le
 * consentement « nom public » (cahier des charges §8.3, point 1).
 */
class CertificateResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('academics');
    }

    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Attestations';

    protected static ?string $modelLabel = 'attestation';

    protected static ?string $pluralModelLabel = 'Attestations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['student', 'milestone']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn (mixed $state, Certificate $record): string => self::personLabel($record)),
                Tables\Columns\TextColumn::make('level_label_i18n.fr')->label('Niveau'),
                Tables\Columns\TextColumn::make('verification_code')->label('Code')->copyable(),
                Tables\Columns\TextColumn::make('issued_at')->label('Délivrée le')->dateTime('d/m/Y')->sortable(),
                Tables\Columns\IconColumn::make('is_public')->label('Visible sur le site')->boolean(),
            ])
            ->defaultSort('issued_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Publier')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Certificate $record): bool => !$record->is_public)
                    ->requiresConfirmation()
                    ->modalDescription('La publication affichera le nom de l\'élève sur le site. Elle est refusée automatiquement sans consentement « nom public » enregistré.')
                    ->action(function (Certificate $record): void {
                        $student = $record->student;
                        if (!$student || !$student->hasConsent('public_name')) {
                            AuditLog::log('certificate.publication_refused', $student, context: [
                                'certificate_id' => $record->id,
                                'student_id' => $record->student_id,
                            ]);
                            Notification::make()
                                ->title('Publication refusée')
                                ->body('Le consentement « nom public » n\'est pas enregistré pour cet élève (Conformité & RGPD → Élèves mineurs).')
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->update(['is_public' => true]);
                        AuditLog::log('certificate.published', $student, context: [
                            'certificate_id' => $record->id,
                            'student_id' => $record->student_id,
                        ]);
                        Notification::make()->title('Attestation publiée')->success()->send();
                    }),
                Tables\Actions\Action::make('unpublish')
                    ->label('Retirer')
                    ->icon('heroicon-o-eye-slash')
                    ->color('gray')
                    ->visible(fn (Certificate $record): bool => $record->is_public)
                    ->requiresConfirmation()
                    ->action(function (Certificate $record): void {
                        $record->update(['is_public' => false]);
                        AuditLog::log('certificate.unpublished', $record->student, context: [
                            'certificate_id' => $record->id,
                            'student_id' => $record->student_id,
                        ]);
                        Notification::make()->title('Attestation retirée du site')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Aucune attestation émise');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function personLabel(Certificate $certificate): string
    {
        $name = $certificate->student?->fullName();

        return $name ? $name : 'Étudiant';
    }
}
