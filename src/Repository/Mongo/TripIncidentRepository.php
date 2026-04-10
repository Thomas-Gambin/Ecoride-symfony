<?php

declare(strict_types=1);

namespace App\Repository\Mongo;

use App\Document\TripIncident;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

/**
 * @extends ServiceDocumentRepository<TripIncident>
 */
class TripIncidentRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripIncident::class);
    }
}
