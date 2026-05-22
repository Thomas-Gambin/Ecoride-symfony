<?php

declare(strict_types=1);

namespace App\Dto\Profile;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCustomPreferencePayload
{
    public function __construct(
        #[Assert\NotBlank(message: 'La préférence est obligatoire.')]
        #[Assert\Length(max: 120, maxMessage: 'La préférence ne peut pas dépasser {{ limit }} caractères.')]
        public string $label,
    ) {
    }
}
