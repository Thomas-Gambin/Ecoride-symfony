<?php

declare(strict_types=1);

namespace App\Enum;

enum UserProfileType: string
{
    case Passenger = 'passenger';
    case Driver = 'driver';
    case PassengerDriver = 'passenger_driver';

    public function requiresDriverProfile(): bool
    {
        return $this === self::Driver || $this === self::PassengerDriver;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
