<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth\Http;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->resetDatabase();
    }

    public function testReturnsProfileWithValidToken(): void
    {
        $token = $this->registerAndLogin('profile@example.com', 'Str0ng!Pass');

        $this->client->request('GET', '/api/auth/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('profile@example.com', $data['email']);
    }

    public function testRejectsRequestWithoutToken(): void
    {
        $this->client->request('GET', '/api/auth/profile');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRejectsInvalidToken(): void
    {
        $this->client->request('GET', '/api/auth/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid.token.here',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    private function registerAndLogin(string $email, string $password): string
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => $email, 'password' => $password]));

        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => $email, 'password' => $password]));

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        return $data['token'];
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $em->getConnection()->executeStatement('TRUNCATE TABLE users CASCADE');
    }
}
