<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response(
            'Backend de développement (Symfony) — stack technique opérationnelle.',
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
}
