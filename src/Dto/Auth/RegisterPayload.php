<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterPayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
        #[Assert\Length(
            min: 3,
            max: 30,
            minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères.',
            maxMessage: 'Le pseudo doit contenir au maximum {{ limit }} caractères.'
        )]
        public readonly string $pseudo,

        #[Assert\NotBlank(message: "L'email est obligatoire.")]
        #[Assert\Email(message: "L'email n'est pas valide.")]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
        #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
        #[Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir au moins une majuscule.')]
        #[Assert\Regex(pattern: '/[a-z]/', message: 'Le mot de passe doit contenir au moins une minuscule.')]
        #[Assert\Regex(pattern: '/\\d/', message: 'Le mot de passe doit contenir au moins un chiffre.')]
        #[Assert\Regex(pattern: '/[^A-Za-z0-9]/', message: 'Le mot de passe doit contenir au moins un caractère spécial.')]
        public readonly string $password,
    ) {}
}

