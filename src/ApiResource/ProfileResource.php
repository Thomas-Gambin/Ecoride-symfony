<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\Profile\PreferencesController;
use App\Controller\Api\Profile\ProfileController;
use App\Controller\Api\Profile\ProfileRoleController;
use App\Controller\Api\Profile\VehiclesController;
use App\Dto\Profile\CreateCustomPreferencePayload;
use App\Dto\Profile\UpdateDriverPreferencePayload;
use App\Dto\Profile\UpdateProfileRolePayload;
use App\Dto\Profile\UpsertCarPayload;

/**
 * Documentation API Platform pour l’espace profil utilisateur.
 * Les contrôleurs Symfony restent la source du comportement métier.
 */
#[ApiResource(
    shortName: 'Profile',
    operations: [
        new Get(
            uriTemplate: '/profile',
            name: 'api_profile_get_doc',
            controller: ProfileController::class,
            read: false,
            write: false,
            serialize: false,
            description: 'Retourne le profil de l’utilisateur connecté.',
            output: false,
        ),
        new Put(
            uriTemplate: '/profile/role',
            name: 'api_profile_role_update_doc',
            controller: ProfileRoleController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Met à jour le rôle métier : passenger, driver ou passenger_driver.',
            input: UpdateProfileRolePayload::class,
            output: false,
        ),
        new Get(
            uriTemplate: '/vehicles',
            name: 'api_vehicles_list_doc',
            controller: VehiclesController::class.'::list',
            read: false,
            write: false,
            serialize: false,
            description: 'Liste les véhicules de l’utilisateur connecté.',
            output: false,
        ),
        new Post(
            uriTemplate: '/vehicles',
            name: 'api_vehicles_create_doc',
            controller: VehiclesController::class.'::create',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Ajoute un véhicule à l’utilisateur connecté.',
            input: UpsertCarPayload::class,
            output: false,
        ),
        new Put(
            uriTemplate: '/vehicles/{id}',
            name: 'api_vehicles_update_doc',
            controller: VehiclesController::class.'::update',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Modifie un véhicule appartenant à l’utilisateur connecté.',
            input: UpsertCarPayload::class,
            output: false,
        ),
        new Delete(
            uriTemplate: '/vehicles/{id}',
            name: 'api_vehicles_delete_doc',
            controller: VehiclesController::class.'::delete',
            read: false,
            write: false,
            serialize: false,
            description: 'Supprime un véhicule appartenant à l’utilisateur connecté.',
            output: false,
        ),
        new Get(
            uriTemplate: '/preferences',
            name: 'api_preferences_get_doc',
            controller: PreferencesController::class.'::get',
            read: false,
            write: false,
            serialize: false,
            description: 'Retourne les préférences conducteur de l’utilisateur connecté.',
            output: false,
        ),
        new Put(
            uriTemplate: '/preferences/standard',
            name: 'api_preferences_standard_update_doc',
            controller: PreferencesController::class.'::updateStandard',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Crée ou met à jour les préférences conducteur standard.',
            input: UpdateDriverPreferencePayload::class,
            output: false,
        ),
        new Post(
            uriTemplate: '/preferences/custom',
            name: 'api_preferences_custom_create_doc',
            controller: PreferencesController::class.'::createCustom',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Ajoute une préférence conducteur personnalisée.',
            input: CreateCustomPreferencePayload::class,
            output: false,
        ),
        new Delete(
            uriTemplate: '/preferences/custom/{id}',
            name: 'api_preferences_custom_delete_doc',
            controller: PreferencesController::class.'::deleteCustom',
            read: false,
            write: false,
            serialize: false,
            description: 'Supprime une préférence conducteur personnalisée.',
            output: false,
        ),
    ],
)]
final class ProfileResource
{
}
