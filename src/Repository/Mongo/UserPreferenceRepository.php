<?php

declare(strict_types=1);

namespace App\Repository\Mongo;

use App\Document\UserPreference;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @extends ServiceDocumentRepository<UserPreference>
 */
class UserPreferenceRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPreference::class);
    }

    public function findOneByUserId(int $userId): ?UserPreference
    {
        return $this->findOneBy(['userId' => $userId]);
    }
}
