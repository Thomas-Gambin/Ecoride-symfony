<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

final class ApiBusinessException extends \RuntimeException
{
    /**
     * @param array<string, string> $fields
     */
    public function __construct(
        private readonly string $errorCode,
        string $message,
        private readonly int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY,
        private readonly array $fields = [],
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
