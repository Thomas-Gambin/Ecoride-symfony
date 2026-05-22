<?php

declare(strict_types=1);

namespace App\Controller\Api\Profile;

use App\Dto\Auth\UserProfileDto;
use App\Dto\Profile\UpdateProfileTypePayload;
use App\Entity\User;
use App\Enum\UserProfileType;
use App\Service\Profile\DriverProfileRequirementChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ProfileTypeController
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly DriverProfileRequirementChecker $requirementChecker,
    ) {
    }

    #[Route('/api/me/profile-type', name: 'api_me_profile_type_update', methods: ['PATCH'])]
    public function __invoke(#[MapRequestPayload] UpdateProfileTypePayload $payload): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return $this->unauthenticated();
        }

        $profileType = UserProfileType::from($payload->profileType);

        if ($profileType->requiresDriverProfile()) {
            $missingRequirements = $this->requirementChecker->getMissingRequirements($user);
            if ($missingRequirements !== []) {
                return new JsonResponse([
                    'code' => 'PROFILE_INCOMPLETE',
                    'message' => 'Le profil chauffeur doit être complété avant d’être activé.',
                    'fields' => $missingRequirements,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $user->setProfileType($profileType);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Votre type de profil a été mis à jour.',
            'user' => UserProfileDto::fromUser($user)->toArray(),
        ], Response::HTTP_OK);
    }

    private function unauthenticated(): JsonResponse
    {
        return new JsonResponse([
            'code' => 'UNAUTHENTICATED',
            'message' => 'Authentification requise.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
