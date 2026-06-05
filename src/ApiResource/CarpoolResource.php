<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\Carpool\CarpoolsController;
use App\Dto\Carpool\CreateCarpoolPayload;
use App\Dto\Carpool\UpdateCarpoolPayload;

/**
 * Documentation API Platform pour la gestion des covoiturages.
 */
#[ApiResource(
    shortName: 'Carpool',
    operations: [
        new Get(
            uriTemplate: '/carpools/mine',
            name: 'api_carpools_mine_doc',
            controller: CarpoolsController::class.'::listMine',
            read: false,
            write: false,
            serialize: false,
            description: 'Liste les trajets créés par l’utilisateur connecté.',
            output: false,
        ),
        new Get(
            uriTemplate: '/carpools/{id}',
            name: 'api_carpools_get_doc',
            controller: CarpoolsController::class.'::get',
            read: false,
            write: false,
            serialize: false,
            description: 'Retourne un trajet appartenant à l’utilisateur connecté.',
            output: false,
        ),
        new Post(
            uriTemplate: '/carpools',
            name: 'api_carpools_create_doc',
            controller: CarpoolsController::class.'::create',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Crée un trajet pour un utilisateur ayant le rôle chauffeur.',
            input: CreateCarpoolPayload::class,
            output: false,
        ),
        new Put(
            uriTemplate: '/carpools/{id}',
            name: 'api_carpools_update_doc',
            controller: CarpoolsController::class.'::update',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Met à jour un trajet appartenant à l’utilisateur connecté.',
            input: UpdateCarpoolPayload::class,
            output: false,
        ),
        new Delete(
            uriTemplate: '/carpools/{id}',
            name: 'api_carpools_delete_doc',
            controller: CarpoolsController::class.'::delete',
            read: false,
            write: false,
            serialize: false,
            description: 'Supprime un trajet appartenant à l’utilisateur connecté.',
            output: false,
        ),
    ],
)]
final class CarpoolResource
{
}
