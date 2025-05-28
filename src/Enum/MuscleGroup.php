<?php

namespace App\Enum;

enum MuscleGroup: string
{
    case CHEST = 'chest';
    case BACK = 'back';
    case LEGS = 'legs';
    case SHOULDERS = 'shoulders';
    case ARMS = 'arms';
    case CORE = 'core';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::CHEST => 'Chest',
            self::BACK => 'Back',
            self::LEGS => 'Legs',
            self::SHOULDERS => 'Shoulders',
            self::ARMS => 'Arms',
            self::CORE => 'Core',
        };
    }
}
