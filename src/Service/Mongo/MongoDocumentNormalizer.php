<?php

declare(strict_types=1);

namespace App\Service\Mongo;

use App\Document\CarpoolReview;
use App\Document\PlatformStatDaily;
use App\Document\TripIncident;
use App\Document\TripSnapshot;
use DateTimeInterface;

final class MongoDocumentNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public static function review(CarpoolReview $review): array
    {
        return [
            'id' => self::idToString($review->getId()),
            'carpoolId' => $review->getCarpoolId(),
            'driverUserId' => $review->getDriverUserId(),
            'passengerUserId' => $review->getPassengerUserId(),
            'rating' => $review->getRating(),
            'comment' => $review->getComment(),
            'status' => $review->getStatus(),
            'createdAt' => self::formatDate($review->getCreatedAt()),
        ];
    }

    /**
     * @param list<CarpoolReview> $reviews
     *
     * @return list<array<string, mixed>>
     */
    public static function reviews(array $reviews): array
    {
        return array_map(self::review(...), $reviews);
    }

    /**
     * @return array<string, mixed>
     */
    public static function tripIncident(TripIncident $incident): array
    {
        return [
            'id' => self::idToString($incident->getId()),
            'carpoolId' => $incident->getCarpoolId(),
            'driverUserId' => $incident->getDriverUserId(),
            'passengerUserId' => $incident->getPassengerUserId(),
            'declaredByUserId' => $incident->getDeclaredByUserId(),
            'status' => $incident->getStatus(),
            'reason' => $incident->getReason(),
            'comment' => $incident->getComment(),
            'tripSnapshot' => self::tripSnapshot($incident->getTripSnapshot()),
            'createdAt' => self::formatDate($incident->getCreatedAt()),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function tripSnapshot(?TripSnapshot $snapshot): ?array
    {
        if ($snapshot === null) {
            return null;
        }

        return [
            'departureDate' => $snapshot->getDepartureDate(),
            'departureTime' => $snapshot->getDepartureTime(),
            'departureLocation' => $snapshot->getDepartureLocation(),
            'arrivalDate' => $snapshot->getArrivalDate(),
            'arrivalTime' => $snapshot->getArrivalTime(),
            'arrivalLocation' => $snapshot->getArrivalLocation(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function platformStat(PlatformStatDaily $stat): array
    {
        return [
            'id' => self::idToString($stat->getId()),
            'date' => $stat->getStatDate(),
            'carpoolCount' => $stat->getCarpoolCount(),
            'platformCreditsEarned' => $stat->getPlatformCreditsEarned(),
            'recordedAt' => self::formatDate($stat->getRecordedAt()),
        ];
    }

    private static function idToString(?string $id): ?string
    {
        return $id !== null ? (string) $id : null;
    }

    private static function formatDate(?\DateTimeImmutable $date): ?string
    {
        return $date?->format(DateTimeInterface::ATOM);
    }
}
