<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomPreference;
use App\Entity\DriverPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomPreference>
 */
class CustomPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomPreference::class);
    }

    public function findDuplicate(DriverPreference $driverPreference, string $label): ?CustomPreference
    {
        return $this->createQueryBuilder('customPreference')
            ->andWhere('customPreference.driverPreference = :driverPreference')
            ->andWhere('LOWER(customPreference.label) = LOWER(:label)')
            ->setParameter('driverPreference', $driverPreference)
            ->setParameter('label', trim($label))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
