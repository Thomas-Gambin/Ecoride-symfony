<?php

declare(strict_types=1);

namespace App\Tests\Functional\Profile;

use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @requires extension mongodb
 */
final class ProfileTest extends WebTestCase
{
    private const PASSWORD = 'SecurePass1!';

    private ?string $accessToken = null;

    public function testProfileReturnsProfileType(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-me-'.uniqid('', true).'@example.com');

        $this->request($client, 'GET', '/api/profile');

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertSame('passenger', $data['user']['profileType'] ?? null);
    }

    public function testPassengerProfileRoleCanBeSaved(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-passenger-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'PUT', '/api/profile/role', [
            'role' => 'passenger',
        ]);

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertSame('passenger', $data['user']['profileType'] ?? null);
    }

    public function testDriverProfileRoleRequiresCompletedDriverProfile(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-driver-missing-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'PUT', '/api/profile/role', [
            'role' => 'driver',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertSame('PROFILE_INCOMPLETE', $data['code'] ?? null);
        self::assertArrayHasKey('vehicles', $data['fields'] ?? []);
        self::assertArrayHasKey('preferences', $data['fields'] ?? []);
    }

    public function testVehicleCrudAndDriverProfileActivation(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-vehicle-'.uniqid('', true).'@example.com');

        $registrationNumber = $this->uniqueRegistrationNumber();

        $this->jsonRequest($client, 'POST', '/api/vehicles', $this->validVehiclePayload($registrationNumber));
        self::assertResponseStatusCodeSame(201);
        $vehicleData = $this->json($client);
        $vehicleId = $vehicleData['vehicle']['id'] ?? null;
        self::assertIsInt($vehicleId);
        self::assertSame('electrique', $vehicleData['vehicle']['energy'] ?? null);

        $this->jsonRequest($client, 'PUT', '/api/vehicles/'.$vehicleId, [
            ...$this->validVehiclePayload($registrationNumber),
            'color' => 'Bleu',
        ]);
        self::assertResponseIsSuccessful();
        $updatedVehicleData = $this->json($client);
        self::assertSame('Bleu', $updatedVehicleData['vehicle']['color'] ?? null);

        $this->jsonRequest($client, 'PUT', '/api/preferences/standard', [
            'allowSmoking' => false,
            'allowAnimals' => true,
        ]);
        self::assertResponseIsSuccessful();

        $this->jsonRequest($client, 'PUT', '/api/profile/role', [
            'role' => 'driver',
        ]);
        self::assertResponseIsSuccessful();
        $profileData = $this->json($client);
        self::assertSame('driver', $profileData['user']['profileType'] ?? null);

        $this->request($client, 'DELETE', '/api/vehicles/'.$vehicleId);
        self::assertResponseIsSuccessful();

        $this->request($client, 'GET', '/api/vehicles');
        self::assertResponseIsSuccessful();
        $vehicles = $this->json($client);
        self::assertSame([], $vehicles['vehicles'] ?? null);
    }

    public function testPassengerDriverProfileRoleCanBeSaved(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-passenger-driver-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'POST', '/api/vehicles', $this->validVehiclePayload($this->uniqueRegistrationNumber()));
        self::assertResponseStatusCodeSame(201);

        $this->jsonRequest($client, 'PUT', '/api/preferences/standard', [
            'allowSmoking' => false,
            'allowAnimals' => false,
        ]);
        self::assertResponseIsSuccessful();

        $this->jsonRequest($client, 'PUT', '/api/profile/role', [
            'role' => 'passenger_driver',
        ]);
        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        self::assertSame('passenger_driver', $data['user']['profileType'] ?? null);
    }

    public function testInvalidVehicleIsRejected(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-invalid-vehicle-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'POST', '/api/vehicles', [
            ...$this->validVehiclePayload($this->uniqueRegistrationNumber()),
            'energy' => 'gpl',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertSame('VALIDATION_ERROR', $data['code'] ?? null);
    }

    public function testUserCannotUpdateAnotherUsersVehicle(): void
    {
        $client = static::createClient();
        $owner = $this->createVerifiedUser($client, 'profile-owner-'.uniqid('', true).'@example.com');
        $vehicle = $this->createVehicleForUser($client, $owner, $this->uniqueRegistrationNumber());

        $this->loginAs($client, 'profile-other-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'PUT', '/api/vehicles/'.(int) $vehicle->getId(), $this->validVehiclePayload($this->uniqueRegistrationNumber()));

        self::assertResponseStatusCodeSame(404);
    }

    public function testStandardAndCustomPreferencesCanBeManaged(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'profile-preferences-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'PUT', '/api/preferences/standard', [
            'allowSmoking' => true,
            'allowAnimals' => false,
        ]);
        self::assertResponseIsSuccessful();
        $preferences = $this->json($client);
        self::assertTrue($preferences['preferences']['allowSmoking'] ?? false);
        self::assertFalse($preferences['preferences']['allowAnimals'] ?? true);

        $this->jsonRequest($client, 'POST', '/api/preferences/custom', [
            'label' => 'Musique calme uniquement',
        ]);
        self::assertResponseStatusCodeSame(201);
        $custom = $this->json($client);
        $customId = $custom['customPreference']['id'] ?? null;
        self::assertIsInt($customId);

        $this->jsonRequest($client, 'POST', '/api/preferences/custom', [
            'label' => 'musique calme uniquement',
        ]);
        self::assertResponseStatusCodeSame(409);

        $this->request($client, 'DELETE', '/api/preferences/custom/'.$customId);
        self::assertResponseIsSuccessful();
    }

    /**
     * @return array<string, mixed>
     */
    private function validVehiclePayload(string $registrationNumber): array
    {
        return [
            'registrationNumber' => $registrationNumber,
            'firstRegistrationDate' => '2020-01-15',
            'brand' => 'Renault',
            'model' => 'Zoé',
            'color' => 'Vert',
            'energy' => 'électrique',
        ];
    }

    private function uniqueRegistrationNumber(): string
    {
        return strtoupper(substr(str_replace('.', '', uniqid('EC', true)), 0, 12));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(KernelBrowser $client, string $method, string $uri): void
    {
        $server = [];
        if (null !== $this->accessToken) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$this->accessToken;
        }

        $client->request($method, $uri, server: $server);
    }

    private function jsonRequest(KernelBrowser $client, string $method, string $uri, array $payload): void
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if (null !== $this->accessToken) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$this->accessToken;
        }

        $client->request(
            $method,
            $uri,
            server: $server,
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function json(KernelBrowser $client): array
    {
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($data);

        return $data;
    }

    /**
     * @return array{token: string, refresh_token: string, user: array<string, mixed>}
     */
    private function loginAs(KernelBrowser $client, string $email): array
    {
        $this->createVerifiedUser($client, $email);
        $this->accessToken = null;

        $client->request(
            'POST',
            '/api/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => self::PASSWORD,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $data = $this->json($client);
        $this->accessToken = (string) ($data['token'] ?? '');

        return $data;
    }

    private function createVerifiedUser(KernelBrowser $client, string $email): User
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setUsername('user_'.substr(uniqid('', true), -8));
        $user->setRoles(['ROLE_USER']);
        $user->setCredits(20);
        $user->setIsVerified(true);
        $user->setPassword($hasher->hashPassword($user, self::PASSWORD));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createVehicleForUser(KernelBrowser $client, User $user, string $registrationNumber): Car
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $brand = new Brand();
        $brand->setLabel('Peugeot');

        $car = new Car();
        $car
            ->setOwner($user)
            ->setBrand($brand)
            ->setRegistrationNumber($registrationNumber)
            ->setFirstRegistrationDate(new \DateTime('2021-03-10'))
            ->setModel('208')
            ->setColor('Noir')
            ->setEnergy('essence');

        $em->persist($brand);
        $em->persist($car);
        $em->flush();

        return $car;
    }
}
