<?php

declare(strict_types=1);

namespace App\Security;

use App\Dto\Auth\UserProfileDto;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class ApiAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();
        $payload = [
            'message' => 'Authentification réussie.',
        ];

        if ($user instanceof User) {
            $payload['user'] = UserProfileDto::fromUser($user)->toArray();
        }

        return new JsonResponse($payload, Response::HTTP_OK);
    }
}
