<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ApiBusinessException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class ApiBusinessExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof ApiBusinessException) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $payload = [
            'code' => $exception->getErrorCode(),
            'message' => $exception->getMessage(),
        ];

        if ($exception->getFields() !== []) {
            $payload['fields'] = $exception->getFields();
        }

        $event->setResponse(new JsonResponse($payload, $exception->getStatusCode()));
    }
}
