<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth\Http;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegisterControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase();
    }

    public function testRegistersUserSuccessfully(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'new@example.com', 'password' => 'Str0ng!Pass']));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame('User registered successfully.', $data['message']);
    }

    public function testRejectsDuplicateEmail(): void
    {
        $payload = json_encode(['email' => 'dup@example.com', 'password' => 'Str0ng!Pass']);

        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
        $this->assertResponseStatusCodeSame(409);
    }

    public function testRejectsWeakPassword(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'weak@example.com', 'password' => 'weak']));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'not-email', 'password' => 'Str0ng!Pass']));

        $this->assertResponseStatusCodeSame(400);
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $em->getConnection()->executeStatement('TRUNCATE TABLE users CASCADE');
    }
}
