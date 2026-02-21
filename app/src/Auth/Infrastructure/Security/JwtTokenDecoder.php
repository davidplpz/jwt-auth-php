<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\TokenDecoder;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Validator;
use Psr\Clock\ClockInterface;

final readonly class JwtTokenDecoder implements TokenDecoder
{
    private Parser $parser;

    public function __construct(
        private string $jwtSecret,
        private ?ClockInterface $clock = null,
    ) {
        $this->parser = new Parser(new JoseEncoder());
    }

    /** @return array{user_id: string, email: string} */
    public function decode(string $token): array
    {
        try {
            $parsed = $this->parser->parse($token);
            assert($parsed instanceof UnencryptedToken);

            $validator = new Validator();
            $key = InMemory::plainText($this->jwtSecret);

            $clock = $this->clock ?? new class implements ClockInterface {
                public function now(): \DateTimeImmutable
                {
                    return new \DateTimeImmutable();
                }
            };

            $validator->assert(
                $parsed,
                new SignedWith(new Sha256(), $key),
                new StrictValidAt($clock),
            );

            $claims = $parsed->claims();

            /** @var string $userId */
            $userId = $claims->get('user_id');
            /** @var string $email */
            $email = $claims->get('email');

            return [
                'user_id' => $userId,
                'email' => $email,
            ];
        } catch (\Throwable) {
            throw InvalidCredentialsException::create();
        }
    }
}
