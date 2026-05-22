<?php

declare(strict_types=1);

namespace App\Controller\Api\Profile;

use App\Dto\Profile\CarDto;
use App\Dto\Profile\UpsertCarPayload;
use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\CarRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class MeVehiclesController
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly CarRepository $carRepository,
        private readonly BrandRepository $brandRepository,
    ) {
    }

    #[Route('/api/me/vehicles', name: 'api_me_vehicles_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        return new JsonResponse([
            'vehicles' => array_map(
                static fn (Car $car): array => CarDto::fromCar($car),
                $this->carRepository->findByOwner($user),
            ),
        ], Response::HTTP_OK);
    }

    #[Route('/api/me/vehicles', name: 'api_me_vehicles_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] UpsertCarPayload $payload): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $registrationNumber = $this->normalizeRegistrationNumber($payload->registrationNumber);
        if ($this->carRepository->findOneBy(['registrationNumber' => $registrationNumber]) !== null) {
            return $this->duplicateRegistrationResponse();
        }

        $car = new Car();
        $car->setOwner($user);
        $error = $this->fillCarFromPayload($car, $payload, $registrationNumber);
        if ($error instanceof JsonResponse) {
            return $error;
        }

        $this->entityManager->persist($car);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->duplicateRegistrationResponse();
        }

        return new JsonResponse([
            'message' => 'Le véhicule a été ajouté.',
            'vehicle' => CarDto::fromCar($car),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/me/vehicles/{id<\d+>}', name: 'api_me_vehicles_update', methods: ['PATCH'])]
    public function update(int $id, #[MapRequestPayload] UpsertCarPayload $payload): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $car = $this->carRepository->findOneOwnedBy($id, $user);
        if (!$car instanceof Car) {
            return $this->notFound();
        }

        $registrationNumber = $this->normalizeRegistrationNumber($payload->registrationNumber);
        $existingCar = $this->carRepository->findOneBy(['registrationNumber' => $registrationNumber]);
        if ($existingCar instanceof Car && $existingCar->getId() !== $car->getId()) {
            return $this->duplicateRegistrationResponse();
        }

        $error = $this->fillCarFromPayload($car, $payload, $registrationNumber);
        if ($error instanceof JsonResponse) {
            return $error;
        }

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->duplicateRegistrationResponse();
        }

        return new JsonResponse([
            'message' => 'Le véhicule a été mis à jour.',
            'vehicle' => CarDto::fromCar($car),
        ], Response::HTTP_OK);
    }

    #[Route('/api/me/vehicles/{id<\d+>}', name: 'api_me_vehicles_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $car = $this->carRepository->findOneOwnedBy($id, $user);
        if (!$car instanceof Car) {
            return $this->notFound();
        }

        if ($car->getCarpools()->count() > 0) {
            return new JsonResponse([
                'code' => 'VEHICLE_IN_USE',
                'message' => 'Ce véhicule est associé à un trajet et ne peut pas être supprimé.',
            ], Response::HTTP_CONFLICT);
        }

        $this->entityManager->remove($car);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Le véhicule a été supprimé.',
        ], Response::HTTP_OK);
    }

    private function fillCarFromPayload(Car $car, UpsertCarPayload $payload, string $registrationNumber): ?JsonResponse
    {
        $firstRegistrationDate = new \DateTimeImmutable($payload->firstRegistrationDate);
        if ($firstRegistrationDate > new \DateTimeImmutable('today')) {
            return new JsonResponse([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Certains champs sont invalides.',
                'fields' => [
                    'firstRegistrationDate' => 'La date de première immatriculation ne peut pas être dans le futur.',
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $car
            ->setRegistrationNumber($registrationNumber)
            ->setFirstRegistrationDate(\DateTime::createFromImmutable($firstRegistrationDate))
            ->setBrand($this->resolveBrand($payload->brand))
            ->setModel(trim($payload->model))
            ->setColor(trim($payload->color))
            ->setEnergy(trim($payload->energy))
            ->setSeatsAvailable($payload->seatsAvailable);

        return null;
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

    private function currentUser(): ?User
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $user : null;
    }

    private function duplicateRegistrationResponse(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'VEHICLE_REGISTRATION_ALREADY_EXISTS',
            'message' => 'Cette plaque d’immatriculation est déjà utilisée.',
            'fields' => [
                'registrationNumber' => 'Cette plaque d’immatriculation est déjà utilisée.',
            ],
        ], Response::HTTP_CONFLICT);
    }

    private function notFound(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'VEHICLE_NOT_FOUND',
            'message' => 'Véhicule introuvable.',
        ], Response::HTTP_NOT_FOUND);
    }

    private function unauthenticated(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'UNAUTHENTICATED',
            'message' => 'Authentification requise.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
