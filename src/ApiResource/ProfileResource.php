<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\Profile\MePreferencesController;
use App\Controller\Api\Profile\MeVehiclesController;
use App\Controller\Api\Profile\ProfileTypeController;
use App\Dto\Profile\CreateCustomPreferencePayload;
use App\Dto\Profile\UpdateDriverPreferencePayload;
use App\Dto\Profile\UpdateProfileTypePayload;
use App\Dto\Profile\UpsertCarPayload;

/**
 * Documentation API Platform pour l’espace profil utilisateur.
 * Les contrôleurs Symfony restent la source du comportement métier.
 */
#[ApiResource(
    shortName: 'Profile',
    operations: [
        new Patch(
            uriTemplate: '/me/profile-type',
            name: 'api_me_profile_type_update_doc',
            controller: ProfileTypeController::class,
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Met à jour le type métier du profil : passenger, driver ou passenger_driver.',
            input: UpdateProfileTypePayload::class,
            output: false,
        ),
        new Get(
            uriTemplate: '/me/vehicles',
            name: 'api_me_vehicles_list_doc',
            controller: MeVehiclesController::class.'::list',
            read: false,
            write: false,
            serialize: false,
            description: 'Liste les véhicules de l’utilisateur connecté.',
            output: false,
        ),
        new Post(
            uriTemplate: '/me/vehicles',
            name: 'api_me_vehicles_create_doc',
            controller: MeVehiclesController::class.'::create',
            read: false,
            deserialize: false,
            validate: false,
            write: false,
            serialize: false,
            description: 'Ajoute un véhicule à l’utilisateur connecté.',
            input: UpsertCarPayload::class,
            output: false,
        ),
        new Patch(
            uriTemplate: '/me/vehicles/{id}',
            name: 'api_me_vehicles_update_doc',
            controller: MeVehiclesController::class.'::update',
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
            uriTemplate: '/me/vehicles/{id}',
            name: 'api_me_vehicles_delete_doc',
            controller: MeVehiclesController::class.'::delete',
            read: false,
            write: false,
            serialize: false,
            description: 'Supprime un véhicule appartenant à l’utilisateur connecté.',
            output: false,
        ),
        new Get(
            uriTemplate: '/me/preferences',
            name: 'api_me_preferences_get_doc',
            controller: MePreferencesController::class.'::get',
            read: false,
            write: false,
            serialize: false,
            description: 'Retourne les préférences conducteur de l’utilisateur connecté.',
            output: false,
        ),
        new Put(
            uriTemplate: '/me/preferences',
            name: 'api_me_preferences_update_doc',
            controller: MePreferencesController::class.'::update',
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
            uriTemplate: '/me/preferences/custom',
            name: 'api_me_preferences_custom_create_doc',
            controller: MePreferencesController::class.'::createCustom',
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
            uriTemplate: '/me/preferences/custom/{id}',
            name: 'api_me_preferences_custom_delete_doc',
            controller: MePreferencesController::class.'::deleteCustom',
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
