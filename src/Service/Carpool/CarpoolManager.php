<?php

declare(strict_types=1);

namespace App\Service\Carpool;

use App\Dto\Carpool\UpdateCarpoolPayload;
use App\Entity\Carpool;
use App\Entity\User;
use App\Enum\CarpoolStatus;
use App\Exception\ApiBusinessException;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class CarpoolManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CarRepository $carRepository,
        private readonly CarpoolTripValidator $tripValidator,
    ) {
    }

    public function update(User $driver, Carpool $carpool, UpdateCarpoolPayload $payload): Carpool
    {
        $this->assertDriverOwnsCarpool($driver, $carpool);
        $this->assertCarpoolIsEditable($carpool);

        $this->tripValidator->validate(
            $payload->departureCity,
            $payload->arrivalCity,
            $payload->departureDate,
            $payload->departureTime,
            $payload->arrivalTime,
            $payload->priceCredits,
        );

        $car = $this->carRepository->findOneOwnedBy($payload->vehicleId, $driver);
        if ($car === null) {
            throw new ApiBusinessException(
                'VEHICLE_NOT_FOUND',
                'Véhicule introuvable.',
                Response::HTTP_NOT_FOUND,
            );
        }

        $departureDate = new \DateTimeImmutable($payload->departureDate);
        $departureDateTime = $this->tripValidator->combineDateAndTime($payload->departureDate, $payload->departureTime);
        $arrivalDateTime = $this->tripValidator->combineDateAndTime($payload->departureDate, $payload->arrivalTime);

        if ($carpool->getCar()?->getId() !== $car->getId()) {
            $carpool->getCar()?->removeCarpool($carpool);
            $car->addCarpool($carpool);
        }

        $carpool
            ->setDepartureDate(\DateTime::createFromImmutable($departureDate))
            ->setDepartureTime(\DateTime::createFromImmutable($departureDateTime))
            ->setDepartureLocation(trim($payload->departureCity->name))
            ->setDepartureCityCode($payload->departureCity->code)
            ->setDeparturePostalCode($payload->departureCity->postalCode)
            ->setArrivalDate(\DateTime::createFromImmutable($arrivalDateTime))
            ->setArrivalTime(\DateTime::createFromImmutable($arrivalDateTime))
            ->setArrivalLocation(trim($payload->arrivalCity->name))
            ->setArrivalCityCode($payload->arrivalCity->code)
            ->setArrivalPostalCode($payload->arrivalCity->postalCode)
            ->setSeatCount($payload->seatCount)
            ->setPricePerPerson((float) $payload->priceCredits)
            ->setCar($car);

        $this->entityManager->flush();

        return $carpool;
    }

    public function delete(User $driver, Carpool $carpool): void
    {
        $this->assertDriverOwnsCarpool($driver, $carpool);
        $this->assertCarpoolIsEditable($carpool);

        $car = $carpool->getCar();
        if ($car !== null) {
            $car->removeCarpool($carpool);
        }

        foreach ($carpool->getUsers()->toArray() as $user) {
            $carpool->removeUser($user);
        }

        $this->entityManager->remove($carpool);
        $this->entityManager->flush();
    }

    private function assertDriverOwnsCarpool(User $driver, Carpool $carpool): void
    {
        if (!$driver->getProfileType()->requiresDriverProfile()) {
            throw new ApiBusinessException(
                'NOT_DRIVER',
                'Vous devez activer le rôle chauffeur pour gérer vos trajets.',
                Response::HTTP_FORBIDDEN,
            );
        }

        $owner = $carpool->getCar()?->getOwner();
        if ($owner === null || $owner->getId() !== $driver->getId()) {
            throw new ApiBusinessException(
                'CARPOOL_NOT_FOUND',
                'Trajet introuvable.',
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    private function assertCarpoolIsEditable(Carpool $carpool): void
    {
        if ($carpool->getStatus() !== CarpoolStatus::Open) {
            throw new ApiBusinessException(
                'CARPOOL_NOT_EDITABLE',
                'Ce trajet ne peut plus être modifié.',
                Response::HTTP_CONFLICT,
            );
        }

        $driver = $carpool->getCar()?->getOwner();
        $bookedPassengers = 0;
        foreach ($carpool->getUsers() as $user) {
            if ($driver !== null && $user->getId() === $driver->getId()) {
                continue;
            }
            ++$bookedPassengers;
        }

        if ($bookedPassengers > 0) {
            throw new ApiBusinessException(
                'CARPOOL_HAS_PASSENGERS',
                'Ce trajet a déjà des passagers inscrits et ne peut pas être modifié ou supprimé.',
                Response::HTTP_CONFLICT,
            );
        }
    }
}
