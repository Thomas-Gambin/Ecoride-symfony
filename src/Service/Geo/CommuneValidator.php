<?php

declare(strict_types=1);

namespace App\Service\Geo;

use App\Exception\ApiBusinessException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CommuneValidator
{
    private const API_BASE = 'https://geo.api.gouv.fr';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function validate(string $name, string $code, string $postalCode): void
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                self::API_BASE.'/communes/'.$code,
                [
                    'query' => [
                        'fields' => 'nom,code,codesPostaux',
                    ],
                    'timeout' => 5,
                ],
            );

            if ($response->getStatusCode() === 404) {
                throw new ApiBusinessException(
                    'INVALID_COMMUNE',
                    'La commune sélectionnée est invalide.',
                    fields: ['code' => 'Commune introuvable.'],
                );
            }

            /** @var array{nom?: string, code?: string, codesPostaux?: list<string>} $data */
            $data = $response->toArray(false);
        } catch (ApiBusinessException $exception) {
            throw $exception;
        } catch (\Throwable) {
            throw new ApiBusinessException(
                'COMMUNE_VALIDATION_FAILED',
                'Impossible de valider la commune pour le moment.',
            );
        }

        $apiName = (string) ($data['nom'] ?? '');
        $apiCode = (string) ($data['code'] ?? '');
        $postalCodes = $data['codesPostaux'] ?? [];

        if ($apiCode !== $code) {
            throw new ApiBusinessException(
                'INVALID_COMMUNE',
                'La commune sélectionnée est invalide.',
                fields: ['code' => 'Le code INSEE ne correspond pas.'],
            );
        }

        if (!$this->namesMatch($name, $apiName)) {
            throw new ApiBusinessException(
                'INVALID_COMMUNE',
                'La commune sélectionnée est invalide.',
                fields: ['name' => 'Le nom de la commune ne correspond pas.'],
            );
        }

        if (!in_array($postalCode, $postalCodes, true)) {
            throw new ApiBusinessException(
                'INVALID_COMMUNE',
                'La commune sélectionnée est invalide.',
                fields: ['postalCode' => 'Le code postal ne correspond pas à cette commune.'],
            );
        }
    }

    private function namesMatch(string $expected, string $actual): bool
    {
        return $this->normalizeName($expected) === $this->normalizeName($actual);
    }

    private function normalizeName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }
}
