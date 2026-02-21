<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Model\User;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;

final readonly class JwtTokenGenerator implements TokenGenerator
{
    private const TTL_SECONDS = 3600;

    public function __construct(private string $jwtSecret)
    {
    }

    public function generate(User $user): string
    {
        $now = new \DateTimeImmutable();
        $builder = Builder::new(new JoseEncoder(), ChainedFormatter::withUnixTimestampDates());

        $token = $builder
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify(sprintf('+%d seconds', self::TTL_SECONDS)))
            ->withClaim('user_id', $user->id()->value())
            ->withClaim('email', $user->email()->value())
            ->getToken(new Sha256(), InMemory::plainText($this->jwtSecret));

        return $token->toString();
    }
}
