<?php

namespace App\Repository;

use App\Entity\Carpool;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carpool>
 */
class CarpoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carpool::class);
    }

    /**
     * @return list<Carpool>
     */
    public function findByDriver(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.car', 'car')
            ->andWhere('car.relation = :user')
            ->setParameter('user', $user)
            ->orderBy('c.departureDate', 'DESC')
            ->addOrderBy('c.departureTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneOwnedByDriver(int $id, User $user): ?Carpool
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.car', 'car')
            ->andWhere('c.id = :id')
            ->andWhere('car.relation = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
