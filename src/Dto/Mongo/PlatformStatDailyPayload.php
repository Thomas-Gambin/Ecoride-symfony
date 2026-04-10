<?php

declare(strict_types=1);

namespace App\Dto\Mongo;

use Symfony\Component\Validator\Constraints as Assert;

final class PlatformStatDailyPayload
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Date]
        public string $date,
        #[Assert\PositiveOrZero]
        public int $carpoolCount,
        #[Assert\PositiveOrZero]
        public float $platformCreditsEarned,
    ) {
    }
}
