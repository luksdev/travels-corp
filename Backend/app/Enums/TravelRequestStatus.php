<?php

declare(strict_types = 1);

namespace App\Enums;

enum TravelRequestStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED  = 'approved';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Solicitado',
            self::APPROVED  => 'Aprovado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function canBeCancelled(): bool
    {
        return $this === self::REQUESTED;
    }
}
