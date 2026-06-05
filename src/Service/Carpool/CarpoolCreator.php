<?php

declare(strict_types=1);

namespace App\Service\Carpool;

use App\Dto\Carpool\CreateCarpoolPayload;
use App\Entity\Carpool;
use App\Entity\User;
use App\Enum\CarpoolStatus;
use App\Exception\ApiBusinessException;
use App\Repository\CarRepository;
use App\Service\Profile\CarFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class CarpoolCreator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CarRepository $carRepository,
        private readonly CarFactory $carFactory,
        private readonly CarpoolTripValidator $tripValidator,
    ) {
    }

    public function create(User $driver, CreateCarpoolPayload $payload): Carpool
    {
        if (!$driver->getProfileType()->requiresDriverProfile()) {
            throw new ApiBusinessException(
                'NOT_DRIVER',
                'Vous devez activer le rôle chauffeur pour créer un trajet.',
                Response::HTTP_FORBIDDEN,
            );
        }

        $this->assertVehiclePayload($payload);

        $this->tripValidator->validate(
            $payload->departureCity,
            $payload->arrivalCity,
            $payload->departureDate,
            $payload->departureTime,
            $payload->arrivalTime,
            $payload->priceCredits,
        );

        $car = $this->resolveVehicle($driver, $payload);

        $departureDate = new \DateTimeImmutable($payload->departureDate);
        $departureDateTime = $this->tripValidator->combineDateAndTime($payload->departureDate, $payload->departureTime);
        $arrivalDateTime = $this->tripValidator->combineDateAndTime($payload->departureDate, $payload->arrivalTime);

        $carpool = new Carpool();
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
            ->setStatus(CarpoolStatus::Open)
            ->setSeatCount($payload->seatCount)
            ->setPricePerPerson((float) $payload->priceCredits)
            ->setPlatformFeeCredits(Carpool::PLATFORM_FEE_CREDITS)
            ->setCar($car)
            ->addUser($driver);

        $car->addCarpool($carpool);

        $this->entityManager->persist($carpool);
        $this->entityManager->flush();

        return $carpool;
    }

    private function assertVehiclePayload(CreateCarpoolPayload $payload): void
    {
        $hasVehicleId = $payload->vehicleId !== null;
        $hasNewVehicle = $payload->newVehicle !== null;

        if ($hasVehicleId && $hasNewVehicle) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: ['vehicleId' => 'Choisissez un véhicule existant ou ajoutez-en un nouveau, pas les deux.'],
            );
        }

        if (!$hasVehicleId && !$hasNewVehicle) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: ['vehicleId' => 'Un véhicule est obligatoire.'],
            );
        }
    }

    private function resolveVehicle(User $driver, CreateCarpoolPayload $payload): \App\Entity\Car
    {
        if ($payload->vehicleId !== null) {
            $car = $this->carRepository->findOneOwnedBy($payload->vehicleId, $driver);
            if ($car === null) {
                throw new ApiBusinessException(
                    'VEHICLE_NOT_FOUND',
                    'Véhicule introuvable.',
                    Response::HTTP_NOT_FOUND,
                );
            }

            return $car;
        }

        if ($payload->newVehicle === null) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: ['vehicleId' => 'Un véhicule est obligatoire.'],
            );
        }

        return $this->carFactory->createForOwner($driver, $payload->newVehicle);
    }
}
