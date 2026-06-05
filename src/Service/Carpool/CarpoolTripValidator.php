<?php

declare(strict_types=1);

namespace App\Service\Carpool;

use App\Dto\Carpool\CityPayload;
use App\Entity\Carpool;
use App\Exception\ApiBusinessException;
use App\Service\Geo\CommuneValidator;

final class CarpoolTripValidator
{
    public function __construct(
        private readonly CommuneValidator $communeValidator,
    ) {
    }

    public function validate(
        CityPayload $departureCity,
        CityPayload $arrivalCity,
        string $departureDate,
        string $departureTime,
        string $arrivalTime,
        int $priceCredits,
    ): void {
        if ($departureCity->code === $arrivalCity->code) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: [
                    'arrivalCity' => 'La ville d’arrivée doit être différente de la ville de départ.',
                ],
            );
        }

        if ($priceCredits <= Carpool::PLATFORM_FEE_CREDITS) {
            throw new ApiBusinessException(
                'PRICE_TOO_LOW',
                'Le prix doit être strictement supérieur à la commission plateforme.',
                fields: [
                    'priceCredits' => sprintf(
                        'Le prix doit être strictement supérieur à %d crédits.',
                        Carpool::PLATFORM_FEE_CREDITS,
                    ),
                ],
            );
        }

        $this->communeValidator->validate(
            $departureCity->name,
            $departureCity->code,
            $departureCity->postalCode,
        );
        $this->communeValidator->validate(
            $arrivalCity->name,
            $arrivalCity->code,
            $arrivalCity->postalCode,
        );

        $departureDay = new \DateTimeImmutable($departureDate);
        $today = new \DateTimeImmutable('today');

        if ($departureDay < $today) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: ['departureDate' => 'La date de départ ne peut pas être dans le passé.'],
            );
        }

        $departureDateTime = $this->combineDateAndTime($departureDate, $departureTime);
        $arrivalDateTime = $this->combineDateAndTime($departureDate, $arrivalTime);

        if ($departureDay->format('Y-m-d') === $today->format('Y-m-d')) {
            $now = new \DateTimeImmutable();
            if ($departureDateTime <= $now) {
                throw new ApiBusinessException(
                    'VALIDATION_ERROR',
                    'Certains champs sont invalides.',
                    fields: ['departureTime' => 'L’heure de départ doit être dans le futur.'],
                );
            }
        }

        if ($arrivalDateTime <= $departureDateTime) {
            throw new ApiBusinessException(
                'INVALID_ARRIVAL_TIME',
                'L’heure d’arrivée doit être postérieure à l’heure de départ.',
                fields: ['arrivalTime' => 'L’heure d’arrivée doit être postérieure à l’heure de départ.'],
            );
        }
    }

    public function combineDateAndTime(string $date, string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('%s %s:00', $date, $time));
    }
}
