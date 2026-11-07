<?php

namespace App\Enums;

/**
 * Enumeration of User Roles within SEACAST PRIGI V3 RBAC
 */
enum UserRole: string
{
    case NELAYAN = 'nelayan';
    case SYAHBANDAR = 'syahbandar';
    case PENELITI = 'peneliti';
    case ADMIN = 'admin';

    /**
     * Get human-readable Indonesian label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::NELAYAN => 'Nelayan & Nakhoda Kapal',
            self::SYAHBANDAR => 'Syahbandar & Otoritas Pelabuhan PPN Prigi',
            self::PENELITI => 'Peneliti Oseanografi & BRIN',
            self::ADMIN => 'Administrator Sistem',
        };
    }

    /**
     * Get all role string values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a string is a valid role value.
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::values(), true);
    }
}

