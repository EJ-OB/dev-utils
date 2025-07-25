<?php

declare(strict_types=1);

namespace App\Enums;

enum SystemRoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (SystemRoleEnum $role): string => $role->value, self::cases());
    }
}
