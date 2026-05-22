<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateDriverPreferencePayload
{
    public function __construct(
        #[Assert\NotNull(message: 'Le choix fumeur est obligatoire.')]
        #[Assert\Type(type: 'bool', message: 'Le choix fumeur est invalide.')]
        public bool $allowSmoking,

        #[Assert\NotNull(message: 'Le choix animaux est obligatoire.')]
        #[Assert\Type(type: 'bool', message: 'Le choix animaux est invalide.')]
        public bool $allowAnimals,
    ) {
    }
}
