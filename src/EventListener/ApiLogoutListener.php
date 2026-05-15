<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
final class ApiLogoutListener
{
    public function onLogout(LogoutEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/logout')) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'message' => 'Déconnexion réussie.',
        ], Response::HTTP_OK));
    }
}
