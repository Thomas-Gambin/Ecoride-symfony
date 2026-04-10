<?php

declare(strict_types=1);

namespace App\Service\Mongo;

use App\Document\CarpoolReview;
use App\Dto\Mongo\CreateReviewPayload;
use App\Repository\Mongo\CarpoolReviewRepository;
use DateTimeImmutable;

final class ReviewService
{
    public function __construct(
        private readonly CarpoolReviewRepository $carpoolReviewRepository,
    ) {
    }

    public function createFromPayload(CreateReviewPayload $payload): CarpoolReview
    {
        $review = (new CarpoolReview())
            ->setCarpoolId($payload->carpoolId)
            ->setDriverUserId($payload->driverUserId)
            ->setPassengerUserId($payload->passengerUserId)
            ->setRating($payload->rating)
            ->setComment($payload->comment)
            ->setStatus($payload->status)
            ->setCreatedAt(new DateTimeImmutable());

        $dm = $this->carpoolReviewRepository->getDocumentManager();
        $dm->persist($review);
        $dm->flush();

        return $review;
    }

    /**
     * @return list<CarpoolReview>
     */
    public function listForCarpool(int $carpoolId): array
    {
        return $this->carpoolReviewRepository->findByCarpoolIdOrdered($carpoolId);
    }
}
