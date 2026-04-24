<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function __invoke(): Response
    {
        throw new \LogicException('Cette route est gérée par le firewall json_login.');
    }
}
