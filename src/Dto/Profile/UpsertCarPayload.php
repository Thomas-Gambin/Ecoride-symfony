<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpsertCarPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'La plaque d’immatriculation est obligatoire.')]
        #[Assert\Length(max: 20, maxMessage: 'La plaque d’immatriculation ne peut pas dépasser {{ limit }} caractères.')]
        #[Assert\Regex(pattern: '/^[A-Z0-9 -]{4,20}$/i', message: 'Le format de la plaque est invalide.')]
        public string $registrationNumber,

        #[Assert\NotBlank(message: 'La date de première immatriculation est obligatoire.')]
        #[Assert\Date(message: 'La date de première immatriculation est invalide.')]
        public string $firstRegistrationDate,

        #[Assert\NotBlank(message: 'La marque est obligatoire.')]
        #[Assert\Length(max: 50, maxMessage: 'La marque ne peut pas dépasser {{ limit }} caractères.')]
        public string $brand,

        #[Assert\NotBlank(message: 'Le modèle est obligatoire.')]
        #[Assert\Length(max: 50, maxMessage: 'Le modèle ne peut pas dépasser {{ limit }} caractères.')]
        public string $model,

        #[Assert\NotBlank(message: 'La couleur est obligatoire.')]
        #[Assert\Length(max: 50, maxMessage: 'La couleur ne peut pas dépasser {{ limit }} caractères.')]
        public string $color,

        #[Assert\NotBlank(message: 'L’énergie est obligatoire.')]
        #[Assert\Length(max: 50, maxMessage: 'L’énergie ne peut pas dépasser {{ limit }} caractères.')]
        public string $energy,

        #[Assert\NotNull(message: 'Le nombre de places est obligatoire.')]
        #[Assert\Positive(message: 'Le nombre de places doit être supérieur ou égal à 1.')]
        public int $seatsAvailable,
    ) {
    }
}
