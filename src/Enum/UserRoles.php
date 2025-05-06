<?php

namespace App\Enum;

enum UserRoles: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
