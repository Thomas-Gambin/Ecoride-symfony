<?php

declare(strict_types=1);

namespace App\Enum;

enum CarEnergy: string
{
    case Essence = 'essence';
    case Diesel = 'diesel';
    case Hybride = 'hybride';
    case Electrique = 'electrique';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $energy): string => $energy->value, self::cases());
    }

    public static function tryFromNormalized(string $value): ?self
    {
        $normalized = self::normalize($value);

        return self::tryFrom($normalized);
    }

    public static function normalize(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = str_replace(['é', 'è', 'ê'], 'e', $value);

        return match ($value) {
            'electrique', 'electric', 'électrique' => self::Electrique->value,
            'essence' => self::Essence->value,
            'diesel' => self::Diesel->value,
            'hybride', 'hybrid' => self::Hybride->value,
            default => $value,
        };
    }
}
