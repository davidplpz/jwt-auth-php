# JWT Authentication Service

JWT authentication service built with Symfony 7.4, PHP 8.3 and Docker.

**Principles**: TDD, SOLID, DDD, Hexagonal Architecture, Screaming Architecture, Value Objects.

---

## Architecture

### Screaming Architecture

The directory structure **screams** the domain's purpose. The top level of `src/` reflects
bounded contexts, not technical concepts:

```
src/
├── Auth/                               # Bounded Context: Authentication
│   ├── Domain/                         # Core — zero external dependencies
│   │   ├── Model/
│   │   │   ├── User.php                # Aggregate Root
│   │   │   ├── UserId.php              # Value Object (UUID)
│   │   │   ├── Email.php               # Value Object
│   │   │   ├── HashedPassword.php      # Value Object
│   │   │   └── PlainPassword.php       # Value Object (pre-hash validation)
│   │   ├── Event/
│   │   │   ├── UserRegistered.php      # Domain Event
│   │   │   └── UserAuthenticated.php   # Domain Event
│   │   ├── Exception/
│   │   │   ├── InvalidCredentialsException.php
│   │   │   ├── UserAlreadyExistsException.php
│   │   │   ├── InvalidEmailException.php
│   │   │   └── WeakPasswordException.php
│   │   └── Port/
│   │       └── UserRepository.php      # Port (interface)
│   │
│   ├── Application/
│   │   ├── Port/
│   │   │   ├── PasswordHasher.php      # Secondary port (interface)
│   │   │   ├── TokenGenerator.php      # Secondary port (interface)
│   │   │   └── TokenDecoder.php        # Secondary port (interface)
│   │   ├── Command/
│   │   │   ├── RegisterUser/
│   │   │   │   ├── RegisterUserCommand.php
│   │   │   │   └── RegisterUserCommandHandler.php
│   │   │   └── AuthenticateUser/
│   │   │       ├── AuthenticateUserCommand.php
│   │   │       └── AuthenticateUserCommandHandler.php
│   │   ├── Query/
│   │   │   └── GetUserProfile/
│   │   │       ├── GetUserProfileQuery.php
│   │   │       └── GetUserProfileQueryHandler.php
│   │   └── DTO/
│   │       ├── AuthTokenResponse.php
│   │       └── UserProfileResponse.php
│   │
│   └── Infrastructure/
│       ├── Persistence/
│       │   ├── DoctrineUserRepository.php      # Adapter
│       │   └── Mapping/
│       │       └── User.orm.xml                # Doctrine Mapping
│       ├── Security/
│       │   ├── JwtTokenGenerator.php           # Adapter
│       │   ├── JwtTokenDecoder.php             # Adapter
│       │   ├── SymfonyPasswordHasher.php       # Adapter
│       │   └── JwtAuthenticator.php            # Symfony Security
│       └── Http/
│           └── Controller/
│               ├── RegisterController.php
│               ├── LoginController.php
│               └── ProfileController.php
│
└── Shared/
    └── Domain/
        ├── ValueObject/
        │   ├── StringValueObject.php           # Abstract base
        │   └── UuidValueObject.php             # Abstract base (UUID)
        ├── AggregateRoot.php                   # Base with domain events
        └── DomainEvent.php                     # Base interface
```

### Hexagonal Layers

```
┌─────────────────────────────────────────────────────┐
│                   INFRASTRUCTURE                     │
│  Controllers, Doctrine, JWT lib, Symfony Security    │
│                                                     │
│  ┌─────────────────────────────────────────────┐    │
│  │              APPLICATION                     │    │
│  │  Commands, Queries, Handlers, DTOs           │    │
│  │  Secondary ports (interfaces)                │    │
│  │                                              │    │
│  │  ┌──────────────────────────────────────┐   │    │
│  │  │            DOMAIN                     │   │    │
│  │  │  Aggregates, Value Objects, Events    │   │    │
│  │  │  Exceptions, Primary ports            │   │    │
│  │  │  *** ZERO external dependencies ***   │   │    │
│  │  └──────────────────────────────────────┘   │    │
│  └─────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

**Dependency rule**: inner layers NEVER depend on outer ones. Infrastructure
depends on application and domain, application depends on domain, domain depends on nothing.

### Tests (Mirror Structure)

```
tests/
├── Unit/
│   ├── Auth/
│   │   ├── Domain/
│   │   │   └── Model/
│   │   │       ├── UserTest.php
│   │   │       ├── UserIdTest.php
│   │   │       ├── EmailTest.php
│   │   │       ├── HashedPasswordTest.php
│   │   │       └── PlainPasswordTest.php
│   │   └── Application/
│   │       ├── Command/
│   │       │   ├── RegisterUserCommandHandlerTest.php
│   │       │   └── AuthenticateUserCommandHandlerTest.php
│   │       └── Query/
│   │           └── GetUserProfileQueryHandlerTest.php
│   └── Shared/
│       └── Domain/
│           └── ValueObject/
│               ├── StringValueObjectTest.php
│               └── UuidValueObjectTest.php
├── Integration/
│   └── Auth/
│       └── Infrastructure/
│           └── Persistence/
│               └── DoctrineUserRepositoryTest.php
└── Functional/
    └── Auth/
        └── Http/
            ├── RegisterControllerTest.php
            ├── LoginControllerTest.php
            └── ProfileControllerTest.php
```

---

## Endpoints (Summary)

| Method | Path                   | Auth     | Description              |
|--------|------------------------|----------|--------------------------|
| POST   | `/api/auth/register`   | No       | Register new user        |
| POST   | `/api/auth/login`      | No       | Authenticate, get JWT    |
| GET    | `/api/auth/profile`    | Bearer   | View user profile        |
| POST   | `/api/auth/refresh`    | No       | Refresh JWT token        |
| GET    | `/api/health`          | No       | Health check             |

---

## Tech Stack

| Component        | Technology                     |
|------------------|--------------------------------|
| Framework        | Symfony 7.4                    |
| PHP              | 8.3-FPM                       |
| Database         | PostgreSQL 16                  |
| ORM              | Doctrine ORM                   |
| JWT              | lcobucci/jwt                   |
| Testing          | PHPUnit                        |
| Web server       | Nginx (Alpine)                 |
| Containers       | Docker + Docker Compose        |

---

## Development Commands

```bash
# Start environment
docker compose up -d

# Run tests (all)
docker compose exec php bin/phpunit

# Run unit tests only
docker compose exec php bin/phpunit --testsuite=Unit

# Run integration tests only
docker compose exec php bin/phpunit --testsuite=Integration

# Run functional tests only
docker compose exec php bin/phpunit --testsuite=Functional

# Create migration
docker compose exec php bin/console doctrine:migrations:diff

# Run migrations
docker compose exec php bin/console doctrine:migrations:migrate

# Symfony console
docker compose exec php bin/console
```

---
