<?php

declare(strict_types=1);

namespace App\Dto\Mongo;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTripSnapshotPayload
{
    public function __construct(
        #[Assert\NotBlank]
        public string $departureDate,
        #[Assert\NotBlank]
        public string $departureTime,
        #[Assert\NotBlank]
        public string $departureLocation,
        #[Assert\NotBlank]
        public string $arrivalDate,
        #[Assert\NotBlank]
        public string $arrivalTime,
        #[Assert\NotBlank]
        public string $arrivalLocation,
    ) {
    }
}
