<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Dto\Auth\RegisterPayload;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Mail\WelcomeEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
final class RegisterController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly WelcomeEmailService $welcomeEmailService,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(#[MapRequestPayload] RegisterPayload $payload): JsonResponse
    {
        $email = strtolower(trim($payload->email));
        $pseudo = trim($payload->pseudo);

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            return $this->errorResponse(
                message: 'Cet email est déjà utilisé.',
                fields: ['email' => 'Cet email est déjà utilisé.'],
                status: Response::HTTP_CONFLICT
            );
        }

        if ($this->userRepository->findOneBy(['username' => $pseudo]) !== null) {
            return $this->errorResponse(
                message: 'Ce pseudo est déjà utilisé.',
                fields: ['pseudo' => 'Ce pseudo est déjà utilisé.'],
                status: Response::HTTP_CONFLICT
            );
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($pseudo);
        $user->setCredits(20);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $payload->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        try {
            $this->welcomeEmailService->send(toEmail: $user->getEmail(), pseudo: $pseudo, credits: $user->getCredits());
        } catch (\Throwable $e) {
            $this->logger->error('Welcome email failed after registration.', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'userId' => $user->getId(),
                'email' => $user->getEmail(),
            ]);
        }

        return new JsonResponse([
            'message' => 'Compte créé avec succès.',
            'user' => [
                'id' => $user->getId(),
                'pseudo' => $user->getUsername(),
                'email' => $user->getEmail(),
                'credits' => $user->getCredits(),
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * @param array<string,string> $fields
     */
    private function errorResponse(string $message, array $fields, int $status): JsonResponse
    {
        return new JsonResponse([
            'code' => 'VALIDATION_ERROR',
            'message' => $message,
            'fields' => $fields,
        ], $status);
    }
}

