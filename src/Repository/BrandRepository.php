<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function findOneByLabel(string $label): ?Brand
    {
        return $this->createQueryBuilder('b')
            ->andWhere('LOWER(b.label) = :label')
            ->setParameter('label', mb_strtolower(trim($label)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
