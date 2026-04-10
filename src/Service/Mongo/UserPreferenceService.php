<?php

declare(strict_types=1);

namespace App\Service\Mongo;

use App\Repository\Mongo\UserPreferenceRepository;

final class UserPreferenceService
{
    public function __construct(
        private readonly UserPreferenceRepository $userPreferenceRepository,
    ) {
    }

    /**
     * @return array{data: array<string, mixed>, persisted: bool}
     */
    public function getOrDefault(int $userId): array
    {
        $doc = $this->userPreferenceRepository->findOneByUserId($userId);

        if ($doc === null) {
            return [
                'data' => [],
                'persisted' => false,
            ];
        }

        return [
            'data' => $doc->getData(),
            'persisted' => true,
        ];
    }
}
