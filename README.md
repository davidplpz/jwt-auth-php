# JWT Authentication Service

Servicio de autenticación JWT construido con Symfony 7.4, PHP 8.3 y Docker.

**Principios**: TDD, SOLID, DDD, Arquitectura Hexagonal, Screaming Architecture, Value Objects.

---

## Arquitectura

### Screaming Architecture

La estructura de directorios **grita** el propósito del dominio. El nivel superior de `src/` refleja
bounded contexts, no conceptos técnicos:

```
src/
├── Auth/                               # Bounded Context: Autenticación
│   ├── Domain/                         # Núcleo — cero dependencias externas
│   │   ├── Model/
│   │   │   ├── User.php                # Aggregate Root
│   │   │   ├── UserId.php              # Value Object (UUID)
│   │   │   ├── Email.php               # Value Object
│   │   │   ├── HashedPassword.php      # Value Object
│   │   │   └── PlainPassword.php       # Value Object (validación pre-hash)
│   │   ├── Event/
│   │   │   ├── UserRegistered.php      # Domain Event
│   │   │   └── UserAuthenticated.php   # Domain Event
│   │   ├── Exception/
│   │   │   ├── InvalidCredentialsException.php
│   │   │   ├── UserAlreadyExistsException.php
│   │   │   ├── InvalidEmailException.php
│   │   │   └── WeakPasswordException.php
│   │   └── Port/
│   │       └── UserRepository.php      # Puerto (interfaz)
│   │
│   ├── Application/
│   │   ├── Port/
│   │   │   ├── PasswordHasher.php      # Puerto secundario (interfaz)
│   │   │   ├── TokenGenerator.php      # Puerto secundario (interfaz)
│   │   │   └── TokenDecoder.php        # Puerto secundario (interfaz)
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
│       │   ├── DoctrineUserRepository.php      # Adaptador
│       │   └── Mapping/
│       │       └── User.orm.xml                # Mapping Doctrine
│       ├── Security/
│       │   ├── JwtTokenGenerator.php           # Adaptador
│       │   ├── JwtTokenDecoder.php             # Adaptador
│       │   ├── SymfonyPasswordHasher.php       # Adaptador
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
        │   ├── StringValueObject.php           # Base abstracta
        │   └── UuidValueObject.php             # Base abstracta (UUID)
        ├── AggregateRoot.php                   # Base con domain events
        └── DomainEvent.php                     # Interfaz base
```

### Capas Hexagonales

```
┌─────────────────────────────────────────────────────┐
│                   INFRASTRUCTURE                     │
│  Controllers, Doctrine, JWT lib, Symfony Security    │
│                                                     │
│  ┌─────────────────────────────────────────────┐    │
│  │              APPLICATION                     │    │
│  │  Commands, Queries, Handlers, DTOs           │    │
│  │  Puertos secundarios (interfaces)            │    │
│  │                                              │    │
│  │  ┌──────────────────────────────────────┐   │    │
│  │  │            DOMAIN                     │   │    │
│  │  │  Aggregates, Value Objects, Events    │   │    │
│  │  │  Excepciones, Puertos primarios       │   │    │
│  │  │  *** CERO dependencias externas ***   │   │    │
│  │  └──────────────────────────────────────┘   │    │
│  └─────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

**Regla de dependencia**: las capas internas NUNCA dependen de las externas. La infraestructura
depende de aplicación y dominio, aplicación depende de dominio, dominio no depende de nada.

### Tests (Estructura espejo)

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

## Endpoints (Resumen)

| Método | Ruta                   | Auth     | Descripción              |
|--------|------------------------|----------|--------------------------|
| POST   | `/api/auth/register`   | No       | Registrar nuevo usuario  |
| POST   | `/api/auth/login`      | No       | Autenticarse, obtener JWT|
| GET    | `/api/auth/profile`    | Bearer   | Ver perfil del usuario   |
| POST   | `/api/auth/refresh`    | No       | Renovar JWT con refresh  |
| GET    | `/api/health`          | No       | Health check             |

---

## Stack Tecnológico

| Componente       | Tecnología                     |
|------------------|--------------------------------|
| Framework        | Symfony 7.4                    |
| PHP              | 8.3-FPM                       |
| Base de datos    | PostgreSQL 16                  |
| ORM              | Doctrine ORM                   |
| JWT              | lcobucci/jwt                   |
| Testing          | PHPUnit                        |
| Servidor web     | Nginx (Alpine)                 |
| Contenedores     | Docker + Docker Compose        |

---

## Comandos de Desarrollo

```bash
# Levantar entorno
docker compose up -d

# Ejecutar tests (todos)
docker compose exec php bin/phpunit

# Ejecutar solo tests unitarios
docker compose exec php bin/phpunit --testsuite=Unit

# Ejecutar solo tests de integración
docker compose exec php bin/phpunit --testsuite=Integration

# Ejecutar solo tests funcionales
docker compose exec php bin/phpunit --testsuite=Functional

# Crear migración
docker compose exec php bin/console doctrine:migrations:diff

# Ejecutar migraciones
docker compose exec php bin/console doctrine:migrations:migrate

# Consola Symfony
docker compose exec php bin/console
```

---