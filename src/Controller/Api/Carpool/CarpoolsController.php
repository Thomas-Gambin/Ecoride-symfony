<?php

declare(strict_types=1);

namespace App\Controller\Api\Carpool;

use App\Dto\Carpool\CarpoolDto;
use App\Dto\Carpool\CreateCarpoolPayload;
use App\Dto\Carpool\UpdateCarpoolPayload;
use App\Entity\User;
use App\Repository\CarpoolRepository;
use App\Service\Carpool\CarpoolCreator;
use App\Service\Carpool\CarpoolManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CarpoolsController
{
    public function __construct(
        private readonly Security $security,
        private readonly CarpoolRepository $carpoolRepository,
        private readonly CarpoolCreator $carpoolCreator,
        private readonly CarpoolManager $carpoolManager,
    ) {
    }

    #[Route('/api/carpools/mine', name: 'api_carpools_mine', methods: ['GET'])]
    public function listMine(): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $carpools = $this->carpoolRepository->findByDriver($user);

        return new JsonResponse([
            'carpools' => array_map(
                static fn ($carpool): array => CarpoolDto::fromEntity($carpool),
                $carpools,
            ),
        ], Response::HTTP_OK);
    }

    #[Route('/api/carpools/{id<\d+>}', name: 'api_carpools_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $carpool = $this->carpoolRepository->findOneOwnedByDriver($id, $user);
        if ($carpool === null) {
            return $this->notFound();
        }

        return new JsonResponse([
            'carpool' => CarpoolDto::fromEntity($carpool),
        ], Response::HTTP_OK);
    }

    #[Route('/api/carpools', name: 'api_carpools_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateCarpoolPayload $payload): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $carpool = $this->carpoolCreator->create($user, $payload);

        return new JsonResponse([
            'message' => 'Votre trajet a bien été créé.',
            'carpool' => CarpoolDto::fromEntity($carpool),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/carpools/{id<\d+>}', name: 'api_carpools_update', methods: ['PUT'])]
    public function update(int $id, #[MapRequestPayload] UpdateCarpoolPayload $payload): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $carpool = $this->carpoolRepository->findOneOwnedByDriver($id, $user);
        if ($carpool === null) {
            return $this->notFound();
        }

        $carpool = $this->carpoolManager->update($user, $carpool, $payload);

        return new JsonResponse([
            'message' => 'Votre trajet a bien été mis à jour.',
            'carpool' => CarpoolDto::fromEntity($carpool),
        ], Response::HTTP_OK);
    }

    #[Route('/api/carpools/{id<\d+>}', name: 'api_carpools_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $carpool = $this->carpoolRepository->findOneOwnedByDriver($id, $user);
        if ($carpool === null) {
            return $this->notFound();
        }

        $this->carpoolManager->delete($user, $carpool);

        return new JsonResponse([
            'message' => 'Votre trajet a bien été supprimé.',
        ], Response::HTTP_OK);
    }

    private function currentUser(): ?User
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $user : null;
    }

    private function unauthenticated(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'UNAUTHENTICATED',
            'message' => 'Authentification requise.',
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function notFound(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'CARPOOL_NOT_FOUND',
            'message' => 'Trajet introuvable.',
        ], Response::HTTP_NOT_FOUND);
    }
}
