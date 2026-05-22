<?php

declare(strict_types=1);

namespace App\Controller\Api\Profile;

use App\Dto\Profile\CreateCustomPreferencePayload;
use App\Dto\Profile\DriverPreferenceDto;
use App\Dto\Profile\UpdateDriverPreferencePayload;
use App\Entity\CustomPreference;
use App\Entity\DriverPreference;
use App\Entity\User;
use App\Repository\CustomPreferenceRepository;
use App\Repository\DriverPreferenceRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class MePreferencesController
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly DriverPreferenceRepository $driverPreferenceRepository,
        private readonly CustomPreferenceRepository $customPreferenceRepository,
    ) {
    }

    #[Route('/api/me/preferences', name: 'api_me_preferences_get', methods: ['GET'])]
    public function get(): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        return new JsonResponse([
            'preferences' => DriverPreferenceDto::fromPreference($this->driverPreferenceRepository->findOneByUser($user)),
        ], Response::HTTP_OK);
    }

    #[Route('/api/me/preferences', name: 'api_me_preferences_update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] UpdateDriverPreferencePayload $payload): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $preference = $this->getOrCreatePreference($user);
        $preference
            ->setAllowSmoking($payload->allowSmoking)
            ->setAllowAnimals($payload->allowAnimals);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Vos préférences conducteur ont été enregistrées.',
            'preferences' => DriverPreferenceDto::fromPreference($preference),
        ], Response::HTTP_OK);
    }

    #[Route('/api/me/preferences/custom', name: 'api_me_preferences_custom_create', methods: ['POST'])]
    public function createCustom(#[MapRequestPayload] CreateCustomPreferencePayload $payload): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $label = trim($payload->label);
        $preference = $this->getOrCreatePreference($user);

        if ($this->customPreferenceRepository->findDuplicate($preference, $label) instanceof CustomPreference) {
            return $this->duplicateCustomPreferenceResponse();
        }

        $customPreference = new CustomPreference();
        $customPreference->setLabel($label);
        $preference->addCustomPreference($customPreference);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->duplicateCustomPreferenceResponse();
        }

        return new JsonResponse([
            'message' => 'La préférence personnalisée a été ajoutée.',
            'customPreference' => DriverPreferenceDto::customPreferenceToArray($customPreference),
            'preferences' => DriverPreferenceDto::fromPreference($preference),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/me/preferences/custom/{id<\d+>}', name: 'api_me_preferences_custom_delete', methods: ['DELETE'])]
    public function deleteCustom(int $id): JsonResponse
    {
        $user = $this->currentUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $customPreference = $this->customPreferenceRepository->find($id);
        if (!$customPreference instanceof CustomPreference
            || $customPreference->getDriverPreference()?->getUser()?->getId() !== $user->getId()
        ) {
            return new JsonResponse([
                'code' => 'CUSTOM_PREFERENCE_NOT_FOUND',
                'message' => 'Préférence personnalisée introuvable.',
            ], Response::HTTP_NOT_FOUND);
        }

        $preference = $customPreference->getDriverPreference();
        $this->entityManager->remove($customPreference);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'La préférence personnalisée a été supprimée.',
            'preferences' => DriverPreferenceDto::fromPreference($preference),
        ], Response::HTTP_OK);
    }

    private function getOrCreatePreference(User $user): DriverPreference
    {
        $preference = $this->driverPreferenceRepository->findOneByUser($user);
        if ($preference instanceof DriverPreference) {
            return $preference;
        }

        $preference = new DriverPreference();
        $preference->setUser($user);
        $user->setDriverPreference($preference);
        $this->entityManager->persist($preference);

        return $preference;
    }

    private function currentUser(): ?User
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $user : null;
    }

    private function duplicateCustomPreferenceResponse(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'CUSTOM_PREFERENCE_ALREADY_EXISTS',
            'message' => 'Cette préférence personnalisée existe déjà.',
            'fields' => [
                'label' => 'Cette préférence personnalisée existe déjà.',
            ],
        ], Response::HTTP_CONFLICT);
    }

    private function unauthenticated(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'UNAUTHENTICATED',
            'message' => 'Authentification requise.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
