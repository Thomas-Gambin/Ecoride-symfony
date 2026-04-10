<?php

declare(strict_types=1);

namespace App\Repository\Mongo;

use App\Document\CarpoolReview;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @extends ServiceDocumentRepository<CarpoolReview>
 */
class CarpoolReviewRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarpoolReview::class);
    }

    /**
     * @return list<CarpoolReview>
     */
    public function findByCarpoolIdOrdered(int $carpoolId): array
    {
        return $this->findBy(['carpoolId' => $carpoolId], ['createdAt' => 'DESC']);
    }
}
