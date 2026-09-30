<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception de base pour les erreurs métier.
 *
 * Toutes les exceptions spécifiques aux modules doivent hériter de celle-ci.
 * Le Handler les mappe automatiquement en réponses HTTP 400/422.
 */
class DomainException extends Exception
{
    /**
     * Code d'erreur machine-readable.
     */
    protected string $errorCode;

    /**
     * Données contextuelles additionnelles.
     */
    protected array $context = [];

    public function __construct(
        string $message = '',
        string $errorCode = 'domain_error',
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
