<?php

declare(strict_types=1);

namespace App\Service\Mongo;

use App\Document\TripIncident;
use App\Document\TripSnapshot;
use App\Dto\Mongo\CreateTripIncidentPayload;
use App\Repository\Mongo\TripIncidentRepository;
use DateTimeImmutable;

final class TripIncidentService
{
    public function __construct(
        private readonly TripIncidentRepository $tripIncidentRepository,
    ) {
    }

    public function createFromPayload(CreateTripIncidentPayload $payload): TripIncident
    {
        $snapshot = (new TripSnapshot())
            ->setDepartureDate($payload->tripSnapshot->departureDate)
            ->setDepartureTime($payload->tripSnapshot->departureTime)
            ->setDepartureLocation($payload->tripSnapshot->departureLocation)
            ->setArrivalDate($payload->tripSnapshot->arrivalDate)
            ->setArrivalTime($payload->tripSnapshot->arrivalTime)
            ->setArrivalLocation($payload->tripSnapshot->arrivalLocation);

        $incident = (new TripIncident())
            ->setCarpoolId($payload->carpoolId)
            ->setDriverUserId($payload->driverUserId)
            ->setPassengerUserId($payload->passengerUserId)
            ->setDeclaredByUserId($payload->declaredByUserId)
            ->setStatus($payload->status)
            ->setReason($payload->reason)
            ->setComment($payload->comment)
            ->setTripSnapshot($snapshot)
            ->setCreatedAt(new DateTimeImmutable());

        $dm = $this->tripIncidentRepository->getDocumentManager();
        $dm->persist($incident);
        $dm->flush();

        return $incident;
    }
}
