<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Model\RefreshToken;
use App\Auth\Domain\Port\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineRefreshTokenRepository implements RefreshTokenRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(RefreshToken $token): void
    {
        $this->em->persist($token);
        $this->em->flush();
    }

    public function findByToken(string $token): ?RefreshToken
    {
        return $this->em->find(RefreshToken::class, $token);
    }
}
