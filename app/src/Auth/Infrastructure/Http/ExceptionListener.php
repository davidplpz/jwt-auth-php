<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http;

use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\Domain\Exception\InvalidEmailException;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Exception\WeakPasswordException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        [$statusCode, $type] = match (true) {
            $exception instanceof InvalidEmailException => [Response::HTTP_BAD_REQUEST, 'invalid-email'],
            $exception instanceof WeakPasswordException => [Response::HTTP_BAD_REQUEST, 'weak-password'],
            $exception instanceof InvalidCredentialsException => [Response::HTTP_UNAUTHORIZED, 'invalid-credentials'],
            $exception instanceof UserAlreadyExistsException => [Response::HTTP_CONFLICT, 'user-already-exists'],
            $exception instanceof \DomainException => [Response::HTTP_NOT_FOUND, 'not-found'],
            default => [null, null],
        };

        if (null === $statusCode) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'type' => $type,
            'title' => Response::$statusTexts[$statusCode] ?? 'Error',
            'status' => $statusCode,
            'detail' => $exception->getMessage(),
        ], $statusCode, ['Content-Type' => 'application/problem+json']));
    }
}
