<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use App\Entity\CustomPreference;
use App\Entity\DriverPreference;

final readonly class DriverPreferenceDto
{
    /**
     * @return array<string, mixed>
     */
    public static function fromPreference(?DriverPreference $preference): array
    {
        if ($preference === null) {
            return [
                'allowSmoking' => false,
                'allowAnimals' => false,
                'customPreferences' => [],
            ];
        }

        return [
            'id' => $preference->getId(),
            'allowSmoking' => $preference->allowsSmoking(),
            'allowAnimals' => $preference->allowsAnimals(),
            'customPreferences' => array_map(
                static fn (CustomPreference $customPreference): array => self::customPreferenceToArray($customPreference),
                $preference->getCustomPreferences()->toArray(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function customPreferenceToArray(CustomPreference $customPreference): array
    {
        return [
            'id' => $customPreference->getId(),
            'label' => $customPreference->getLabel(),
            'createdAt' => $customPreference->getCreatedAt()?->format(DATE_ATOM),
        ];
    }
}
