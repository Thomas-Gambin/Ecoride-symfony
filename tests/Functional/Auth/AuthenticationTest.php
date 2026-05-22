<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @requires extension mongodb
 */
final class AuthenticationTest extends WebTestCase
{
    private const PASSWORD = 'SecurePass1!';

    public function testLoginWithInvalidCredentialsReturns401(): void
    {
        $client = static::createClient();
        $this->createVerifiedUser($client, 'invalid@example.com');

        $client->request(
            'POST',
            '/api/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'invalid@example.com',
                'password' => 'WrongPass1!',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertSame('INVALID_CREDENTIALS', $data['code'] ?? null);
    }

    public function testLoginWithUnverifiedAccountReturns403(): void
    {
        $client = static::createClient();
        $email = 'unverified-'.uniqid('', true).'@example.com';
        $this->createUser($client, $email, isVerified: false);

        $client->request(
            'POST',
            '/api/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => $email,
                'password' => self::PASSWORD,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(403);
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertSame('EMAIL_NOT_VERIFIED', $data['code'] ?? null);
    }

    public function testLoginSuccessReturnsTokensAndUser(): void
    {
        $client = static::createClient();
        $email = 'verified-'.uniqid('', true).'@example.com';
        $this->createVerifiedUser($client, $email);

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
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($data);
        self::assertArrayHasKey('token', $data);
        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('user', $data);
        self::assertSame($email, $data['user']['email'] ?? null);
        self::assertArrayNotHasKey('password', $data['user']);
    }

    public function testRefreshTokenReturnsNewAccessTokenAndUser(): void
    {
        $client = static::createClient();
        $email = 'refresh-'.uniqid('', true).'@example.com';
        $this->loginAs($client, $email);

        $client->request(
            'POST',
            '/api/token/refresh',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{}',
        );

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($data);
        self::assertArrayHasKey('token', $data);
        self::assertArrayHasKey('user', $data);
        self::assertSame($email, $data['user']['email'] ?? null);
    }

    public function testMeWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertSame('UNAUTHENTICATED', $data['code'] ?? null);
    }

    public function testMeAfterLoginReturnsProfile(): void
    {
        $client = static::createClient();
        $email = 'me-'.uniqid('', true).'@example.com';
        $tokens = $this->loginAs($client, $email);

        $client->request(
            'GET',
            '/api/me',
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$tokens['token']],
        );

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertSame($email, $data['user']['email'] ?? null);
        self::assertArrayHasKey('credits', $data['user']);
        self::assertArrayHasKey('roles', $data['user']);
        self::assertArrayHasKey('profileType', $data['user']);
    }

    public function testProtectedRouteRequiresBearerToken(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/vehicles');

        self::assertResponseStatusCodeSame(401);
    }

    public function testLogoutRevokesRefreshToken(): void
    {
        $client = static::createClient();
        $email = 'logout-'.uniqid('', true).'@example.com';
        $this->loginAs($client, $email);

        $client->request(
            'POST',
            '/api/logout',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{}',
        );
        self::assertResponseIsSuccessful();

        $client->request(
            'POST',
            '/api/token/refresh',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{}',
        );

        self::assertResponseStatusCodeSame(401);
    }

    /**
     * @return array{token: string, user: array<string, mixed>}
     */
    private function loginAs(KernelBrowser $client, string $email): array
    {
        $this->createVerifiedUser($client, $email);

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
        $data = json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($data);

        return $data;
    }

    private function createVerifiedUser(KernelBrowser $client, string $email): User
    {
        return $this->createUser($client, $email, isVerified: true);
    }

    private function createUser(KernelBrowser $client, string $email, bool $isVerified): User
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
        $user->setIsVerified($isVerified);
        $user->setPassword($hasher->hashPassword($user, self::PASSWORD));

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
