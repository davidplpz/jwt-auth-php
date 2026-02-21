<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Model\Email;
use App\Auth\Domain\Model\HashedPassword;
use App\Auth\Domain\Model\User;
use App\Auth\Domain\Model\UserId;
use App\Auth\Infrastructure\Persistence\DoctrineUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineUserRepositoryTest extends KernelTestCase
{
    private DoctrineUserRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = new DoctrineUserRepository($this->em);

        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testSavesAndFindsById(): void
    {
        $id = UserId::generate();
        $user = User::register($id, new Email('test@example.com'), HashedPassword::fromHash('$2y$10$hash'));
        $user->pullDomainEvents();

        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findById($id);

        $this->assertNotNull($found);
        $this->assertSame($id->value(), $found->id()->value());
        $this->assertSame('test@example.com', $found->email()->value());
    }

    public function testFindsByEmail(): void
    {
        $email = new Email('find@example.com');
        $user = User::register(UserId::generate(), $email, HashedPassword::fromHash('$2y$10$hash'));
        $user->pullDomainEvents();

        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findByEmail($email);

        $this->assertNotNull($found);
        $this->assertTrue($email->equals($found->email()));
    }

    public function testReturnsNullWhenNotFoundById(): void
    {
        $this->assertNull($this->repository->findById(UserId::generate()));
    }

    public function testReturnsNullWhenNotFoundByEmail(): void
    {
        $this->assertNull($this->repository->findByEmail(new Email('nobody@example.com')));
    }
}
