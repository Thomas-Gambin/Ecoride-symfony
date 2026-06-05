<?php

declare(strict_types=1);

namespace App\Service\Profile;

use App\Dto\Profile\UpsertCarPayload;
use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\User;
use App\Enum\CarEnergy;
use App\Exception\ApiBusinessException;
use App\Repository\BrandRepository;
use App\Repository\CarRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class CarFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CarRepository $carRepository,
        private readonly BrandRepository $brandRepository,
    ) {
    }

    public function createForOwner(User $owner, UpsertCarPayload $payload): Car
    {
        $registrationNumber = $this->normalizeRegistrationNumber($payload->registrationNumber);

        if ($this->carRepository->findOneBy(['registrationNumber' => $registrationNumber]) !== null) {
            throw new ApiBusinessException(
                'VEHICLE_REGISTRATION_ALREADY_EXISTS',
                'Cette plaque d’immatriculation est déjà utilisée.',
                Response::HTTP_CONFLICT,
                ['registrationNumber' => 'Cette plaque d’immatriculation est déjà utilisée.'],
            );
        }

        $energy = $this->resolveEnergy($payload->energy);
        $firstRegistrationDate = new \DateTimeImmutable($payload->firstRegistrationDate);

        if ($firstRegistrationDate > new \DateTimeImmutable('today')) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: [
                    'firstRegistrationDate' => 'La date de première immatriculation ne peut pas être dans le futur.',
                ],
            );
        }

        $car = new Car();
        $car
            ->setOwner($owner)
            ->setRegistrationNumber($registrationNumber)
            ->setFirstRegistrationDate(\DateTime::createFromImmutable($firstRegistrationDate))
            ->setBrand($this->resolveBrand($payload->brand))
            ->setModel(trim($payload->model))
            ->setColor(trim($payload->color))
            ->setEnergy($energy);

        $this->entityManager->persist($car);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            throw new ApiBusinessException(
                'VEHICLE_REGISTRATION_ALREADY_EXISTS',
                'Cette plaque d’immatriculation est déjà utilisée.',
                Response::HTTP_CONFLICT,
                ['registrationNumber' => 'Cette plaque d’immatriculation est déjà utilisée.'],
            );
        }

        return $car;
    }

    private function resolveEnergy(string $energy): string
    {
        $normalized = CarEnergy::normalize($energy);
        $resolved = CarEnergy::tryFrom($normalized);

        if ($resolved === null) {
            throw new ApiBusinessException(
                'VALIDATION_ERROR',
                'Certains champs sont invalides.',
                fields: ['energy' => 'L’énergie est invalide.'],
            );
        }

        return $resolved->value;
    }

    private function resolveBrand(string $label): Brand
    {
        $label = trim($label);
        $brand = $this->brandRepository->findOneByLabel($label);
        if ($brand instanceof Brand) {
            return $brand;
        }

        $brand = new Brand();
        $brand->setLabel($label);
        $this->entityManager->persist($brand);

        return $brand;
    }

    private function normalizeRegistrationNumber(string $registrationNumber): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $registrationNumber) ?? $registrationNumber));
    }
}
