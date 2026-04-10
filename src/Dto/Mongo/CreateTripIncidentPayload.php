<?php

declare(strict_types=1);

namespace App\Dto\Mongo;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTripIncidentPayload
{
    public function __construct(
        #[Assert\Positive]
        public int $carpoolId,
        #[Assert\Positive]
        public int $driverUserId,
        #[Assert\Positive]
        public int $passengerUserId,
        #[Assert\Positive]
        public int $declaredByUserId,
        #[Assert\Choice(choices: ['open', 'in_review', 'closed'])]
        public string $status = 'open',
        #[Assert\Length(max: 255)]
        public ?string $reason = null,
        #[Assert\Length(max: 10_000)]
        public ?string $comment = null,
        #[Assert\NotNull]
        #[Assert\Valid]
        public CreateTripSnapshotPayload $tripSnapshot,
    ) {
    }
}
