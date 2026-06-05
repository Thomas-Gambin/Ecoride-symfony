<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeoApiMockHttpClientFactory
{
    /**
     * @var array<string, array{nom: string, code: string, codesPostaux: list<string>}>
     */
    private const COMMUNES = [
        '69123' => [
            'nom' => 'Lyon',
            'code' => '69123',
            'codesPostaux' => ['69001', '69002', '69003', '69004', '69005'],
        ],
        '13055' => [
            'nom' => 'Marseille',
            'code' => '13055',
            'codesPostaux' => ['13001', '13002', '13003'],
        ],
        '75056' => [
            'nom' => 'Paris',
            'code' => '75056',
            'codesPostaux' => ['75001', '75002', '75003'],
        ],
    ];

    public static function create(): HttpClientInterface
    {
        return new MockHttpClient(static function (string $method, string $url): MockResponse {
            if (!str_contains($url, 'geo.api.gouv.fr/communes/')) {
                return new MockResponse('', ['http_code' => 404]);
            }

            $code = basename(parse_url($url, PHP_URL_PATH) ?: '');
            $commune = self::COMMUNES[$code] ?? null;

            if ($commune === null) {
                return new MockResponse('', ['http_code' => 404]);
            }

            return new MockResponse(json_encode($commune, JSON_THROW_ON_ERROR), [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]);
        });
    }
}
