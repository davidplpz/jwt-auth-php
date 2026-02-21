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

## Plan de Iteraciones

### Iteración 0 — Setup del proyecto ✅
> Preparar el entorno, dependencias y configuración base.

- [x] Instalar dependencias PHP:
  - `symfony/security-bundle` (autenticación Symfony)
  - `doctrine/orm`, `doctrine/doctrine-bundle`, `doctrine/doctrine-migrations-bundle` (persistencia)
  - `lcobucci/jwt 5.6` (generación/validación JWT standalone)
  - `symfony/uid` (UUIDs)
  - `phpunit/phpunit 12.5` (testing, require-dev)
  - `symfony/test-pack` (testing con Symfony, require-dev)
- [x] Eliminar bundles de frontend innecesarios (`ux-turbo`, `webpack-encore`)
- [x] Eliminar servicio `node` de `docker-compose.yml`
- [x] Configurar `phpunit.dist.xml` con suites: Unit, Integration, Functional
- [x] Configurar `services.yaml` para escanear la estructura hexagonal
- [x] Configurar Doctrine mapping XML apuntando a `Auth/Infrastructure/Persistence/Mapping`
- [x] Crear directorios base de toda la arquitectura
- [x] Verificar que Docker levanta correctamente con las nuevas dependencias
- [x] Verificar PHPUnit (smoke test OK, PHPUnit 12.5.14, PHP 8.3.30)

### Iteración 1 — Shared Kernel ✅
> Abstracciones base reutilizables. TDD: tests primero.

- [x] **Test**: `StringValueObjectTest` — igualdad, inmutabilidad, valor vacío (5 tests)
- [x] **Impl**: `StringValueObject` — clase abstracta base
- [x] **Test**: `UuidValueObjectTest` — formato válido, generación, igualdad (7 tests)
- [x] **Impl**: `UuidValueObject` — clase abstracta con validación UUID (usa `symfony/uid`)
- [x] **Impl**: `DomainEvent` — interfaz con `occurredOn()`, `eventName()`
- [x] **Impl**: `AggregateRoot` — clase abstracta con `record()` y `pullDomainEvents()`

### Iteración 2 — Auth Domain: Value Objects
> Modelar los bloques fundamentales del dominio. TDD estricto.

- [ ] **Test**: `UserIdTest` — creación, igualdad, formato UUID
- [ ] **Impl**: `UserId extends UuidValueObject`
- [ ] **Test**: `EmailTest` — email válido, email inválido lanza excepción, igualdad case-insensitive
- [ ] **Impl**: `Email` — validación con regex, normalización lowercase
- [ ] **Test**: `PlainPasswordTest` — longitud mínima 8, requiere mayúscula, número y especial
- [ ] **Impl**: `PlainPassword` — validaciones de fuerza en constructor
- [ ] **Test**: `HashedPasswordTest` — creación desde hash, no puede estar vacío
- [ ] **Impl**: `HashedPassword` — wrapper inmutable sobre el hash

### Iteración 3 — Auth Domain: Aggregate Root y Puertos
> Entidad User como aggregate root con sus invariantes. TDD.

- [ ] **Test**: `UserTest` — creación con factory method `register()`, emite `UserRegistered`
- [ ] **Test**: `UserTest` — no permite email duplicado (a nivel de dominio, invariantes)
- [ ] **Test**: `UserTest` — expone datos via value objects
- [ ] **Impl**: `User` — Aggregate Root con `register()` static factory, propiedades privadas
- [ ] **Impl**: `UserRegistered` domain event
- [ ] **Impl**: `UserAuthenticated` domain event
- [ ] **Impl**: `UserRepository` — interfaz (puerto) con `save()`, `findByEmail()`, `findById()`
- [ ] **Impl**: Excepciones de dominio:
  - `InvalidCredentialsException`
  - `UserAlreadyExistsException`
  - `InvalidEmailException`
  - `WeakPasswordException`

### Iteración 4 — Auth Application: Use Cases
> Casos de uso orquestando dominio a través de puertos. TDD con mocks.

- [ ] **Impl**: Puertos secundarios (interfaces):
  - `PasswordHasher` — `hash(PlainPassword): HashedPassword`, `verify(PlainPassword, HashedPassword): bool`
  - `TokenGenerator` — `generate(User): string`
  - `TokenDecoder` — `decode(string): array` (payload)
- [ ] **Impl**: `AuthTokenResponse` DTO — `token`, `expiresIn`
- [ ] **Impl**: `UserProfileResponse` DTO — `id`, `email`
- [ ] **Test**: `RegisterUserCommandHandlerTest`:
  - Registro exitoso devuelve void (o DTO)
  - Email duplicado lanza `UserAlreadyExistsException`
  - Password débil lanza `WeakPasswordException`
- [ ] **Impl**: `RegisterUserCommand` + `RegisterUserCommandHandler`
- [ ] **Test**: `AuthenticateUserCommandHandlerTest`:
  - Login exitoso devuelve `AuthTokenResponse`
  - Email inexistente lanza `InvalidCredentialsException`
  - Password incorrecta lanza `InvalidCredentialsException` (mismo error, no revelar qué falló)
- [ ] **Impl**: `AuthenticateUserCommand` + `AuthenticateUserCommandHandler`
- [ ] **Test**: `GetUserProfileQueryHandlerTest`:
  - Devuelve `UserProfileResponse` con datos correctos
  - Usuario no encontrado lanza excepción
- [ ] **Impl**: `GetUserProfileQuery` + `GetUserProfileQueryHandler`

### Iteración 5 — Auth Infrastructure: Adaptadores
> Implementaciones concretas de los puertos. Tests de integración.

- [ ] **Impl**: `SymfonyPasswordHasher` — adaptador usando `PasswordHasherInterface` de Symfony
- [ ] **Test**: Test unitario del hasher (hash y verify)
- [ ] **Impl**: `JwtTokenGenerator` — adaptador usando `lcobucci/jwt`
- [ ] **Impl**: `JwtTokenDecoder` — adaptador usando `lcobucci/jwt`
- [ ] **Test**: Test unitario de generación y decodificación JWT
- [ ] **Impl**: `DoctrineUserRepository` — adaptador Doctrine ORM
- [ ] **Impl**: Mapping XML de Doctrine para `User` aggregate
- [ ] **Impl**: Migración de base de datos (tabla `users`)
- [ ] **Test**: `DoctrineUserRepositoryTest` — test de integración con DB real (PostgreSQL)
- [ ] Configurar `services.yaml` — binding de interfaces a implementaciones

### Iteración 6 — Auth Infrastructure: HTTP (Controllers)
> Endpoints REST + autenticación JWT. Tests funcionales.

- [ ] **Impl**: `RegisterController` — `POST /api/auth/register`
  - Request: `{ "email": "...", "password": "..." }`
  - Response: `201 Created`
- [ ] **Impl**: `LoginController` — `POST /api/auth/login`
  - Request: `{ "email": "...", "password": "..." }`
  - Response: `200 { "token": "...", "expires_in": 3600 }`
- [ ] **Impl**: `ProfileController` — `GET /api/auth/profile`
  - Header: `Authorization: Bearer <token>`
  - Response: `200 { "id": "...", "email": "..." }`
- [ ] **Impl**: `JwtAuthenticator` — Custom authenticator de Symfony Security
- [ ] **Impl**: Configurar `security.yaml` — firewall con JWT stateless
- [ ] **Impl**: Manejo de errores HTTP (exception listener/subscriber)
- [ ] **Test**: `RegisterControllerTest` — registro exitoso, email duplicado, validación
- [ ] **Test**: `LoginControllerTest` — login exitoso, credenciales inválidas
- [ ] **Test**: `ProfileControllerTest` — con token válido, sin token, token expirado

### Iteración 7 — Refresh Token
> Mecanismo de refresh token para renovar JWTs sin re-autenticar.

- [ ] **Impl**: `RefreshToken` Value Object / Entidad en Domain
- [ ] **Impl**: `RefreshTokenRepository` puerto en Domain
- [ ] **Test + Impl**: `RefreshTokenCommand` + Handler en Application
- [ ] **Impl**: `DoctrineRefreshTokenRepository` adaptador
- [ ] **Impl**: `RefreshController` — `POST /api/auth/refresh`
  - Request: `{ "refresh_token": "..." }`
  - Response: `200 { "token": "...", "refresh_token": "...", "expires_in": 3600 }`
- [ ] **Test**: Tests funcionales del flujo de refresh
- [ ] **Impl**: Migración para tabla `refresh_tokens`

### Iteración 8 — Hardening y Calidad
> Robustez, observabilidad y documentación de la API.

- [ ] Rate limiting en endpoints de auth (Symfony RateLimiter)
- [ ] Logging estructurado de eventos de autenticación
- [ ] Estandarización de respuestas de error (RFC 7807 Problem Details)
- [ ] Health check endpoint (`GET /api/health`)
- [ ] Configuración de CORS
- [ ] Revisión de seguridad (headers, HTTPS, secrets)
- [ ] Documentación OpenAPI/Swagger (nelmio/api-doc-bundle o manual)

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

## Estado Actual

> **Iteración activa**: Iteración 2 — Auth Domain: Value Objects

| Iteración | Estado        |
|-----------|---------------|
| 0         | ✅ Completada |
| 1         | ✅ Completada |
| 2         | ⬜ Pendiente  |
| 3         | ⬜ Pendiente  |
| 4         | ⬜ Pendiente  |
| 5         | ⬜ Pendiente  |
| 6         | ⬜ Pendiente  |
| 7         | ⬜ Pendiente  |
| 8         | ⬜ Pendiente  |
