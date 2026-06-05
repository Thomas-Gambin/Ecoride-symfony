<?php

declare(strict_types=1);

namespace App\Dto\Carpool;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CityPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le nom de la commune est obligatoire.')]
        #[Assert\Length(max: 100, maxMessage: 'Le nom de la commune ne peut pas dépasser {{ limit }} caractères.')]
        public string $name,

        #[Assert\NotBlank(message: 'Le code INSEE est obligatoire.')]
        #[Assert\Length(exactly: 5, exactMessage: 'Le code INSEE doit contenir {{ limit }} caractères.')]
        #[Assert\Regex(pattern: '/^\d{5}$/', message: 'Le code INSEE est invalide.')]
        public string $code,

        #[Assert\NotBlank(message: 'Le code postal est obligatoire.')]
        #[Assert\Length(max: 10, maxMessage: 'Le code postal ne peut pas dépasser {{ limit }} caractères.')]
        #[Assert\Regex(pattern: '/^\d{5}$/', message: 'Le code postal est invalide.')]
        public string $postalCode,
    ) {
    }
}
