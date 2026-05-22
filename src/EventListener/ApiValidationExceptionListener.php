<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Réponses JSON pour les erreurs de validation sur les routes /api/* (évite les pages HTML 422 en dev).
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 16)]
final class ApiValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();
        $validation = $this->resolveValidationException($throwable);
        if (null === $validation) {
            return;
        }

        $fields = [];
        foreach ($validation->getViolations() as $violation) {
            $property = $violation->getPropertyPath() ?: 'form';
            $fields[$property] = (string) $violation->getMessage();
        }

        $event->setResponse(new JsonResponse([
            'code' => 'VALIDATION_ERROR',
            'message' => 'Certains champs sont invalides.',
            'fields' => $fields,
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    private function resolveValidationException(\Throwable $throwable): ?ValidationFailedException
    {
        if ($throwable instanceof ValidationFailedException) {
            return $throwable;
        }

        if ($throwable instanceof HttpException) {
            $previous = $throwable->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                return $previous;
            }
        }

        return null;
    }
}
