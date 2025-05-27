<?php

namespace App\Enum;

enum DifficultyLevel: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::BEGINNER => 'Başlangıç',
            self::INTERMEDIATE => 'Orta',
            self::ADVANCED => 'İleri',
            self::EXPERT => 'Uzman',
        };
    }
}
