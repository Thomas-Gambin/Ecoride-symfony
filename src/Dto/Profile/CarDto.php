<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use App\Entity\Car;

final readonly class CarDto
{
    /**
     * @return array<string, mixed>
     */
    public static function fromCar(Car $car): array
    {
        return [
            'id' => $car->getId(),
            'registrationNumber' => $car->getRegistrationNumber(),
            'firstRegistrationDate' => $car->getFirstRegistrationDate()?->format('Y-m-d'),
            'brand' => $car->getBrand()?->getLabel(),
            'brandId' => $car->getBrand()?->getId(),
            'model' => $car->getModel(),
            'color' => $car->getColor(),
            'energy' => $car->getEnergy(),
        ];
    }
}
