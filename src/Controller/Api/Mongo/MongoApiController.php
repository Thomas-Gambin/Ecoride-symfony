<?php

declare(strict_types=1);

namespace App\Controller\Api\Mongo;

use App\Dto\Mongo\CreateReviewPayload;
use App\Dto\Mongo\CreateTripIncidentPayload;
use App\Dto\Mongo\PlatformStatDailyPayload;
use App\Service\Mongo\MongoDocumentNormalizer;
use App\Service\Mongo\PlatformStatService;
use App\Service\Mongo\ReviewService;
use App\Service\Mongo\TripIncidentService;
use App\Service\Mongo\UserPreferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API exemple pour les documents MongoDB (préfixe distinct d’API Platform /api).
 */
#[Route('/api/mongo')]
final class MongoApiController extends AbstractController
{
    #[Route('/reviews', name: 'api_mongo_review_create', methods: ['POST'])]
    public function createReview(
        #[MapRequestPayload] CreateReviewPayload $payload,
        ReviewService $reviewService,
    ): JsonResponse {
        $review = $reviewService->createFromPayload($payload);

        return $this->json(
            MongoDocumentNormalizer::review($review),
            Response::HTTP_CREATED,
        );
    }

    #[Route('/carpools/{carpoolId<\d+>}/reviews', name: 'api_mongo_reviews_list', methods: ['GET'])]
    public function listReviewsForCarpool(int $carpoolId, ReviewService $reviewService): JsonResponse
    {
        $reviews = $reviewService->listForCarpool($carpoolId);

        return $this->json(MongoDocumentNormalizer::reviews($reviews));
    }

    #[Route('/trip-incidents', name: 'api_mongo_trip_incident_create', methods: ['POST'])]
    public function createTripIncident(
        #[MapRequestPayload] CreateTripIncidentPayload $payload,
        TripIncidentService $tripIncidentService,
    ): JsonResponse {
        $incident = $tripIncidentService->createFromPayload($payload);

        return $this->json(
            MongoDocumentNormalizer::tripIncident($incident),
            Response::HTTP_CREATED,
        );
    }

    #[Route('/users/{userId<\d+>}/preferences', name: 'api_mongo_user_preferences', methods: ['GET'])]
    public function getUserPreferences(int $userId, UserPreferenceService $preferenceService): JsonResponse
    {
        $result = $preferenceService->getOrDefault($userId);

        return $this->json($result['data'] + ['persisted' => $result['persisted']]);
    }

    #[Route('/platform-stats/daily', name: 'api_mongo_platform_stat_daily', methods: ['POST'])]
    public function recordDailyPlatformStat(
        #[MapRequestPayload] PlatformStatDailyPayload $payload,
        PlatformStatService $platformStatService,
    ): JsonResponse {
        $stat = $platformStatService->recordDaily($payload);

        return $this->json(MongoDocumentNormalizer::platformStat($stat));
    }
}
