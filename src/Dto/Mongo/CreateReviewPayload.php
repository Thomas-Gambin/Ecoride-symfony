<?php

declare(strict_types=1);

namespace App\Dto\Mongo;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateReviewPayload
{
    public function __construct(
        #[Assert\Positive]
        public int $carpoolId,
        #[Assert\Positive]
        public int $driverUserId,
        #[Assert\Positive]
        public int $passengerUserId,
        #[Assert\Range(min: 1, max: 5)]
        public int $rating,
        #[Assert\Length(max: 10_000)]
        public ?string $comment = null,
        #[Assert\Choice(choices: ['pending', 'approved', 'rejected'])]
        public string $status = 'pending',
    ) {
    }
}
