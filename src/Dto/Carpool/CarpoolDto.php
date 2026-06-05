<?php

declare(strict_types=1);

namespace App\Dto\Carpool;

use App\Entity\Carpool;
use App\Entity\User;

final readonly class CarpoolDto
{
    /**
     * @return array<string, mixed>
     */
    public static function fromEntity(Carpool $carpool): array
    {
        $car = $carpool->getCar();
        $driver = $car?->getOwner();

        $departureTime = $carpool->getDepartureTime();
        $arrivalTime = $carpool->getArrivalTime();

        return [
            'id' => $carpool->getId(),
            'departureDate' => $carpool->getDepartureDate()?->format('Y-m-d'),
            'departureTime' => $departureTime?->format('H:i'),
            'departureLocation' => $carpool->getDepartureLocation(),
            'departureCityCode' => $carpool->getDepartureCityCode(),
            'departurePostalCode' => $carpool->getDeparturePostalCode(),
            'arrivalDate' => $carpool->getArrivalDate()?->format('Y-m-d'),
            'arrivalTime' => $arrivalTime?->format('H:i'),
            'arrivalLocation' => $carpool->getArrivalLocation(),
            'arrivalCityCode' => $carpool->getArrivalCityCode(),
            'arrivalPostalCode' => $carpool->getArrivalPostalCode(),
            'status' => $carpool->getStatus()->value,
            'seatCount' => $carpool->getSeatCount(),
            'pricePerPerson' => $carpool->getPricePerPerson(),
            'platformFeeCredits' => $carpool->getPlatformFeeCredits(),
            'vehicleId' => $car?->getId(),
            'driver' => [
                'userId' => $driver?->getId(),
                'username' => $driver?->getUsername() ?? 'Conducteur',
                'averageRating' => 0,
            ],
            'car' => [
                'brandLabel' => $car?->getBrand()?->getLabel() ?? '',
                'model' => $car?->getModel() ?? '',
                'energy' => $car?->getEnergy() ?? '',
                'color' => $car?->getColor() ?? '',
            ],
            'bookedPassengerCount' => self::countBookedPassengers($carpool, $driver),
            'durationMinutes' => self::computeDurationMinutes($departureTime, $arrivalTime),
        ];
    }

    private static function countBookedPassengers(Carpool $carpool, ?User $driver): int
    {
        $count = 0;
        foreach ($carpool->getUsers() as $user) {
            if ($driver !== null && $user->getId() === $driver->getId()) {
                continue;
            }
            ++$count;
        }

        return $count;
    }

    private static function computeDurationMinutes(?\DateTime $departureTime, ?\DateTime $arrivalTime): int
    {
        if ($departureTime === null || $arrivalTime === null) {
            return 0;
        }

        $departureMinutes = ((int) $departureTime->format('H')) * 60 + (int) $departureTime->format('i');
        $arrivalMinutes = ((int) $arrivalTime->format('H')) * 60 + (int) $arrivalTime->format('i');
        $duration = $arrivalMinutes - $departureMinutes;

        return max(0, $duration);
    }
}
