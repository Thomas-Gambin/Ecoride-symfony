<?php

declare(strict_types=1);

namespace App\Repository\Mongo;

use App\Document\PlatformStatDaily;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @extends ServiceDocumentRepository<PlatformStatDaily>
 */
class PlatformStatDailyRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatformStatDaily::class);
    }

    public function findOneByStatDate(string $statDate): ?PlatformStatDaily
    {
        return $this->findOneBy(['statDate' => $statDate]);
    }
}
