<?php

namespace App\Enum;

enum UserRoles: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case TRAINER = 'ROLE_TRAINER';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::USER => 'Kullanıcı',
            self::ADMIN => 'Yönetici',
            self::TRAINER => 'Antrenör',
        };
    }
}
