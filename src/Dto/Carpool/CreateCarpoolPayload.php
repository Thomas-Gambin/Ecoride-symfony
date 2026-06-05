<?php

declare(strict_types=1);

namespace App\Dto\Carpool;

use App\Dto\Profile\UpsertCarPayload;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCarpoolPayload
{
    public function __construct(
        #[Assert\NotNull(message: 'La ville de départ est obligatoire.')]
        #[Assert\Valid]
        public CityPayload $departureCity,

        #[Assert\NotNull(message: 'La ville d’arrivée est obligatoire.')]
        #[Assert\Valid]
        public CityPayload $arrivalCity,

        #[Assert\NotBlank(message: 'La date de départ est obligatoire.')]
        #[Assert\Date(message: 'La date de départ est invalide.')]
        public string $departureDate,

        #[Assert\NotBlank(message: 'L’heure de départ est obligatoire.')]
        #[Assert\Regex(pattern: '/^\d{2}:\d{2}$/', message: 'L’heure de départ est invalide.')]
        public string $departureTime,

        #[Assert\NotBlank(message: 'L’heure d’arrivée est obligatoire.')]
        #[Assert\Regex(pattern: '/^\d{2}:\d{2}$/', message: 'L’heure d’arrivée est invalide.')]
        public string $arrivalTime,

        #[Assert\NotNull(message: 'Le prix est obligatoire.')]
        #[Assert\Positive(message: 'Le prix doit être strictement positif.')]
        public int $priceCredits,

        #[Assert\NotNull(message: 'Le nombre de places est obligatoire.')]
        #[Assert\Positive(message: 'Le nombre de places doit être au moins 1.')]
        public int $seatCount,

        #[Assert\Positive(message: 'L’identifiant du véhicule est invalide.')]
        public ?int $vehicleId = null,

        #[Assert\Valid]
        public ?UpsertCarPayload $newVehicle = null,
    ) {
    }
}
