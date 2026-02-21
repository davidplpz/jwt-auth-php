<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth\Http;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RefreshControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->resetDatabase();
    }

    public function testRefreshesTokenSuccessfully(): void
    {
        $loginData = $this->registerAndLogin('refresh@example.com', 'Str0ng!Pass');

        $this->client->request('POST', '/api/auth/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['refresh_token' => $loginData['refresh_token']]));

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['token']);
        $this->assertNotSame($loginData['refresh_token'], $data['refresh_token']);
    }

    public function testRejectsInvalidRefreshToken(): void
    {
        $this->client->request('POST', '/api/auth/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['refresh_token' => 'invalid-token']));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRejectsAlreadyUsedRefreshToken(): void
    {
        $loginData = $this->registerAndLogin('reuse@example.com', 'Str0ng!Pass');
        $refreshToken = $loginData['refresh_token'];

        $this->client->request('POST', '/api/auth/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['refresh_token' => $refreshToken]));
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', '/api/auth/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['refresh_token' => $refreshToken]));
        $this->assertResponseStatusCodeSame(401);
    }

    /** @return array{token: string, refresh_token: string} */
    private function registerAndLogin(string $email, string $password): array
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => $email, 'password' => $password]));

        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => $email, 'password' => $password]));

        return json_decode((string) $this->client->getResponse()->getContent(), true);
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $em->getConnection()->executeStatement('TRUNCATE TABLE refresh_tokens CASCADE');
        $em->getConnection()->executeStatement('TRUNCATE TABLE users CASCADE');
    }
}
