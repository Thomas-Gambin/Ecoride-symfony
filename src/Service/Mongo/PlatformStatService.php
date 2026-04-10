<?php

declare(strict_types=1);

namespace App\Service\Mongo;

use App\Document\PlatformStatDaily;
use App\Dto\Mongo\PlatformStatDailyPayload;
use App\Repository\Mongo\PlatformStatDailyRepository;
use DateTimeImmutable;

final class PlatformStatService
{
    public function __construct(
        private readonly PlatformStatDailyRepository $platformStatDailyRepository,
    ) {
    }

    public function recordDaily(PlatformStatDailyPayload $payload): PlatformStatDaily
    {
        $dm = $this->platformStatDailyRepository->getDocumentManager();
        $stat = $this->platformStatDailyRepository->findOneByStatDate($payload->date);

        if ($stat === null) {
            $stat = (new PlatformStatDaily())
                ->setStatDate($payload->date);
            $dm->persist($stat);
        }

        $stat
            ->setCarpoolCount($payload->carpoolCount)
            ->setPlatformCreditsEarned($payload->platformCreditsEarned)
            ->setRecordedAt(new DateTimeImmutable());

        $dm->flush();

        return $stat;
    }
}
