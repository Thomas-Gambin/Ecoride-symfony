<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DriverPreference;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DriverPreference>
 */
class DriverPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriverPreference::class);
    }

    public function findOneByUser(User $user): ?DriverPreference
    {
        return $this->findOneBy(['user' => $user]);
    }
}
