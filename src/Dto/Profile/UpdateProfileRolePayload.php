<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use App\Enum\UserProfileType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateProfileRolePayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le rôle est obligatoire.')]
        #[Assert\Choice(callback: [UserProfileType::class, 'values'], message: 'Le rôle est invalide.')]
        public string $role,
    ) {
    }
}
