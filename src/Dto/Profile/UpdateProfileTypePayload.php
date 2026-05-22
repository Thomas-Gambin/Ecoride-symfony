<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use App\Enum\UserProfileType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateProfileTypePayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le type de profil est obligatoire.')]
        #[Assert\Choice(callback: [UserProfileType::class, 'values'], message: 'Le type de profil est invalide.')]
        public string $profileType,
    ) {
    }
}
