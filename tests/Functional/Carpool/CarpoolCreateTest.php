<?php

declare(strict_types=1);

namespace App\Tests\Functional\Carpool;

use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\DriverPreference;
use App\Entity\User;
use App\Enum\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @requires extension mongodb
 */
final class CarpoolCreateTest extends WebTestCase
{
    private const PASSWORD = 'SecurePass1!';

    private ?string $accessToken = null;

    public function testUnauthenticatedUserCannotCreateCarpool(): void
    {
        $client = static::createClient();

        $this->jsonRequest($client, 'POST', '/api/carpools', $this->validCarpoolPayload(1));

        self::assertResponseStatusCodeSame(401);
    }

    public function testPassengerCannotCreateCarpool(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'carpool-passenger-'.uniqid('', true).'@example.com');

        $this->jsonRequest($client, 'POST', '/api/carpools', $this->validCarpoolPayload(1));

        self::assertResponseStatusCodeSame(403);
        $data = $this->json($client);
        self::assertSame('NOT_DRIVER', $data['code'] ?? null);
    }

    public function testDriverCanCreateCarpoolWithExistingVehicle(): void
    {
        $client = static::createClient();
        $email = 'carpool-driver-'.uniqid('', true).'@example.com';
        $this->loginAs($client, $email);
        $vehicleId = $this->activateDriverProfile($client);

        $this->jsonRequest($client, 'POST', '/api/carpools', $this->validCarpoolPayload($vehicleId));

        self::assertResponseStatusCodeSame(201);
        $data = $this->json($client);
        self::assertSame('Votre trajet a bien été créé.', $data['message'] ?? null);
        self::assertSame('Lyon', $data['carpool']['departureLocation'] ?? null);
        self::assertSame('Marseille', $data['carpool']['arrivalLocation'] ?? null);
        self::assertSame(2, $data['carpool']['platformFeeCredits'] ?? null);
        self::assertSame('open', $data['carpool']['status'] ?? null);
    }

    public function testPriceMustBeGreaterThanPlatformFee(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'carpool-price-'.uniqid('', true).'@example.com');
        $vehicleId = $this->activateDriverProfile($client);

        $payload = $this->validCarpoolPayload($vehicleId);
        $payload['priceCredits'] = 2;

        $this->jsonRequest($client, 'POST', '/api/carpools', $payload);

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertSame('PRICE_TOO_LOW', $data['code'] ?? null);
    }

    public function testUserCannotCreateCarpoolWithAnotherUsersVehicle(): void
    {
        $client = static::createClient();
        $owner = $this->createVerifiedUser($client, 'carpool-owner-'.uniqid('', true).'@example.com');
        $vehicle = $this->createVehicleForUser($client, $owner, $this->uniqueRegistrationNumber());

        $this->loginAs($client, 'carpool-other-'.uniqid('', true).'@example.com');
        $this->activateDriverProfile($client);

        $this->jsonRequest($client, 'POST', '/api/carpools', $this->validCarpoolPayload((int) $vehicle->getId()));

        self::assertResponseStatusCodeSame(404);
        $data = $this->json($client);
        self::assertSame('VEHICLE_NOT_FOUND', $data['code'] ?? null);
    }

    public function testDriverCanCreateCarpoolWithNewVehicle(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'carpool-new-vehicle-'.uniqid('', true).'@example.com');
        $this->activateDriverProfile($client);

        $payload = $this->validCarpoolPayload(null);
        $payload['newVehicle'] = [
            'registrationNumber' => $this->uniqueRegistrationNumber(),
            'firstRegistrationDate' => '2020-01-15',
            'brand' => 'Renault',
            'model' => 'Zoé',
            'color' => 'Vert',
            'energy' => 'electrique',
        ];

        $this->jsonRequest($client, 'POST', '/api/carpools', $payload);

        self::assertResponseStatusCodeSame(201);
        $data = $this->json($client);
        self::assertSame('Zoé', $data['carpool']['car']['model'] ?? null);
    }

    public function testInvalidCommuneCodeIsRejected(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'carpool-invalid-city-'.uniqid('', true).'@example.com');
        $vehicleId = $this->activateDriverProfile($client);

        $payload = $this->validCarpoolPayload($vehicleId);
        $payload['departureCity']['code'] = '99999';

        $this->jsonRequest($client, 'POST', '/api/carpools', $payload);

        self::assertResponseStatusCodeSame(422);
        $data = $this->json($client);
        self::assertSame('INVALID_COMMUNE', $data['code'] ?? null);
    }

    public function testDriverCanListUpdateAndDeleteOwnCarpools(): void
    {
        $client = static::createClient();
        $this->loginAs($client, 'carpool-manage-'.uniqid('', true).'@example.com');
        $vehicleId = $this->activateDriverProfile($client);

        $this->jsonRequest($client, 'POST', '/api/carpools', $this->validCarpoolPayload($vehicleId));
        self::assertResponseStatusCodeSame(201);
        $created = $this->json($client);
        $carpoolId = (int) ($created['carpool']['id'] ?? 0);
        self::assertGreaterThan(0, $carpoolId);

        $client->request(
            'GET',
            '/api/carpools/mine',
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken],
        );
        self::assertResponseIsSuccessful();
        $list = $this->json($client);
        self::assertCount(1, $list['carpools'] ?? []);

        $updatePayload = $this->validCarpoolPayload($vehicleId);
        $updatePayload['priceCredits'] = 18;
        $updatePayload['departureCity']['name'] = 'Paris';
        $updatePayload['departureCity']['code'] = '75056';
        $updatePayload['departureCity']['postalCode'] = '75001';

        $this->jsonRequest($client, 'PUT', '/api/carpools/'.$carpoolId, $updatePayload);
        self::assertResponseIsSuccessful();
        $updated = $this->json($client);
        self::assertSame(18, $updated['carpool']['pricePerPerson'] ?? null);
        self::assertSame('Paris', $updated['carpool']['departureLocation'] ?? null);

        $client->request(
            'DELETE',
            '/api/carpools/'.$carpoolId,
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken],
        );
        self::assertResponseIsSuccessful();

        $client->request(
            'GET',
            '/api/carpools/mine',
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken],
        );
        self::assertResponseIsSuccessful();
        $emptyList = $this->json($client);
        self::assertSame([], $emptyList['carpools'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function validCarpoolPayload(?int $vehicleId): array
    {
        $tomorrow = (new \DateTimeImmutable('tomorrow'))->format('Y-m-d');

        $payload = [
            'departureCity' => [
                'name' => 'Lyon',
                'code' => '69123',
                'postalCode' => '69001',
            ],
            'arrivalCity' => [
                'name' => 'Marseille',
                'code' => '13055',
                'postalCode' => '13001',
            ],
            'departureDate' => $tomorrow,
            'departureTime' => '08:00',
            'arrivalTime' => '12:00',
            'priceCredits' => 15,
            'seatCount' => 3,
        ];

        if ($vehicleId !== null) {
            $payload['vehicleId'] = $vehicleId;
        }

        return $payload;
    }

    private function activateDriverProfile(KernelBrowser $client): int
    {
        $registrationNumber = $this->uniqueRegistrationNumber();

        $this->jsonRequest($client, 'POST', '/api/vehicles', [
            'registrationNumber' => $registrationNumber,
            'firstRegistrationDate' => '2020-01-15',
            'brand' => 'Renault',
            'model' => 'Clio',
            'color' => 'Blanc',
            'energy' => 'essence',
        ]);
        self::assertResponseStatusCodeSame(201);
        $vehicleData = $this->json($client);
        $vehicleId = (int) ($vehicleData['vehicle']['id'] ?? 0);

        $this->jsonRequest($client, 'PUT', '/api/preferences/standard', [
            'allowSmoking' => false,
            'allowAnimals' => false,
        ]);
        self::assertResponseIsSuccessful();

        $this->jsonRequest($client, 'PUT', '/api/profile/role', [
            'role' => 'driver',
        ]);
        self::assertResponseIsSuccessful();

        return $vehicleId;
    }

    /**
     * @param array<string, mixed> $payload
     */
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
        $user->setProfileType(UserProfileType::Passenger);
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

    private function uniqueRegistrationNumber(): string
    {
        return strtoupper(substr(str_replace('.', '', uniqid('EC', true)), 0, 12));
    }
}
