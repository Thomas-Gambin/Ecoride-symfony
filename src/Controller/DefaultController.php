<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page d’accueil identique au welcome Symfony (vendor/symfony/http-kernel/Resources/welcome.html.php).
 */
final class DefaultController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): Response
    {
        $version = Kernel::VERSION;
        $projectRoot = \dirname(__DIR__, 2);
        $resolved = realpath($projectRoot);
        $projectDir = ($resolved !== false ? $resolved : $projectRoot).\DIRECTORY_SEPARATOR;
        $docVersion = substr(Kernel::VERSION, 0, 3);

        ob_start();
        include $projectRoot.'/vendor/symfony/http-kernel/Resources/welcome.html.php';

        return new Response(ob_get_clean(), Response::HTTP_OK);
    }
}
