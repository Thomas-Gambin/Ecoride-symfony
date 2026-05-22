<?php

declare(strict_types=1);

namespace App\Service\Profile;

use App\Entity\Car;
use App\Entity\DriverPreference;
use App\Entity\User;
use App\Enum\CarEnergy;
use App\Repository\CarRepository;
use App\Repository\DriverPreferenceRepository;

final class DriverProfileRequirementChecker
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly DriverPreferenceRepository $driverPreferenceRepository,
    ) {
    }

    public function hasValidVehicle(User $user): bool
    {
        foreach ($this->carRepository->findByOwner($user) as $car) {
            if ($this->isVehicleValid($car)) {
                return true;
            }
        }

        return false;
    }

    public function hasPreferences(User $user): bool
    {
        return $this->driverPreferenceRepository->findOneByUser($user) instanceof DriverPreference;
    }

    /**
     * @return array<string, string>
     */
    public function getMissingRequirements(User $user): array
    {
        $fields = [];

        if (!$this->hasValidVehicle($user)) {
            $fields['vehicles'] = 'Au moins un véhicule valide est obligatoire pour devenir chauffeur.';
        }

        if (!$this->hasPreferences($user)) {
            $fields['preferences'] = 'Les préférences conducteur sont obligatoires pour devenir chauffeur.';
        }

        return $fields;
    }

    private function isVehicleValid(Car $car): bool
    {
        $energy = $car->getEnergy();

        return $car->getRegistrationNumber() !== null
            && $car->getRegistrationNumber() !== ''
            && $car->getFirstRegistrationDate() !== null
            && $car->getBrand() !== null
            && $car->getModel() !== null
            && $car->getModel() !== ''
            && $car->getColor() !== null
            && $car->getColor() !== ''
            && $energy !== null
            && $energy !== ''
            && CarEnergy::tryFrom($energy) !== null;
    }
}
