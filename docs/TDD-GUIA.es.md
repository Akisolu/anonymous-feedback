# Guía para trabajar con TDD y DI en este proyecto

Esta guía está pensada para que puedas trabajar por tu cuenta, siguiendo un flujo claro de pruebas primero (TDD), usando el contenedor de dependencias que ya está configurado en `config/` y respetando la estructura del proyecto.

## 1) ¿Qué es lo que ya está preparado?

Este proyecto ya tiene:

- Configuración de entorno en `config/config.php`
- Contenedor de dependencias en `config/container.php`
- Tests con PHPUnit en `tests/`
- Ejemplos de tests unitarios e integrados

La idea es que no crees clases con instancias manuales si ya existe DI. En cambio, resuelves todo desde el contenedor.

---

## 2) Cómo importar el contenedor DI desde `config/`

El archivo principal del contenedor es:

- `config/container.php`

Este archivo devuelve un objeto contenedor listo para usar:

```php
<?php

$container = require __DIR__ . '/../../config/container.php';
```

Ejemplo real de uso en un test:

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PDO;

final class MiTest extends TestCase
{
    public function test_puedo_resolver_un_servicio_desde_el_container(): void
    {
        $container = require __DIR__ . '/../../config/container.php';

        $pdo = $container->get(PDO::class);

        $this->assertInstanceOf(PDO::class, $pdo);
    }
}
```

### Importante

En este proyecto, el contenedor ya registra cosas como:

- `PDO::class`
- `Predis\Client::class`
- `RateLimiter::class`
- `Capsule::class`
- Interfaces asociadas a implementaciones concretas

Eso te permite pedir dependencias por nombre de clase o por interfaz.

---

## 3) Cómo está montado el DI

Revisa este archivo:

- `config/container.php`

Tiene este esquema básico:

```php
<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client as RedisClient;

use Akisolu\AnonymousFeedback\Services\RateLimiter;

$config = require __DIR__ . '/config.php';

$builder = new ContainerBuilder();
$builder->addDefinitions([
    'config' => $config,

    PDO::class => function (ContainerInterface $c) {
        $db = $c->get('config')['db'];
        $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['database']}";

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    },

    RedisClient::class => function (ContainerInterface $c) {
        return new RedisClient($c->get('config')['redis']);
    },

    RateLimiter::class => function (ContainerInterface $c) {
        return new RateLimiter($c->get(RedisClient::class));
    },
]);

return $builder->build();
```

Esto significa que:

- `config.php` tiene la configuración general
- `container.php` crea y resuelve las dependencias
- tú no construyes manualmente `PDO`, `Redis` o servicios complejos en cada test

---

## 4) Cómo registrar una interfaz con su implementación

Cuando haya una interfaz, normalmente se resuelve así:

```php
<?php

use Akisolu\AnonymousFeedback\Repositories\FeedbackRepository;
use Akisolu\AnonymousFeedback\Repositories\FeedbackRepositoryInterface;
use DI\ContainerBuilder;
use function DI\get;

$builder = new ContainerBuilder();

$builder->addDefinitions([
    FeedbackRepositoryInterface::class => get(FeedbackRepository::class),
]);
```

Esto le dice a PHP-DI:

> “Cuando pidas `FeedbackRepositoryInterface`, crea la clase concreta `FeedbackRepository`”.

### Regla práctica

Si un servicio o repositorio tiene interfaz, no lo instancies a mano. Déjalo que lo resuelva DI.

---

## 5) Flujo de trabajo con TDD

El ciclo recomendado es siempre:

1. Escribe el test primero
2. Ejecuta el test y debe fallar
3. Implementa la mínima lógica necesaria
4. Ejecuta el test y debe pasar
5. Refactoriza si hace falta
6. Repite

### Regla de oro

Nunca escribas la funcionalidad antes del test. Primero define el comportamiento esperado.

---

## 6) Estructura recomendada para tests

Este proyecto ya usa:

- `tests/Unit/` para pruebas unitarias
- `tests/Integration/` para pruebas con base de datos, Redis o sistema real

### Convenciones

- El archivo debe terminar en `Test.php`
- La clase debe terminar en `Test`
- El método debe empezar con `test_` o con `test`.

Ejemplos válidos:

```php
final class RateLimiterTest extends TestCase
{
    public function test_blocks_requests_exceeding_limit(): void
    {
        // ...
    }
}
```

```php
final class FeedbackRepositoryTest extends TestCase
{
    public function test_can_save_feedback(): void
    {
        // ...
    }
}
```

---

## 7) Cómo crear un test nuevo paso a paso

### Paso 1: decide la intención

Por ejemplo:

> “quiero probar que el repositorio puede guardar un feedback”

### Paso 2: crea el archivo

Ubicación sugerida:

- `tests/Integration/FeedbackRepositoryTest.php`

O si es unitario:

- `tests/Unit/MiServicioTest.php`

### Paso 3: escribe el test

Ejemplo base:

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MiServicioTest extends TestCase
{
    public function test_algo_debe_ocurrir(): void
    {
        $resultado = true;

        $this->assertTrue($resultado);
    }
}
```

### Paso 4: ejecuta solo ese test

```bash
./vendor/bin/phpunit tests/Unit/MiServicioTest.php
```

O con filtro:

```bash
./vendor/bin/phpunit --filter test_algo_debe_ocurrir
```

### Paso 5: si falla, implementa el código mínimo

Después de ver la falla real, corrige solo lo necesario.

### Paso 6: ejecuta otra vez

```bash
./vendor/bin/phpunit tests/Unit/MiServicioTest.php
```

---

## 8) Ejemplos reales según el proyecto

### Test de integración con contenedor

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PDO;

final class DatabaseConnectionTest extends TestCase
{
    public function testDatabaseConnectionIsSuccessful(): void
    {
        $container = require __DIR__ . '/../../config/container.php';
        $pdo = $container->get(PDO::class);

        $this->assertInstanceOf(PDO::class, $pdo);
    }
}
```

### Test con servicio resuelto desde DI

```php
<?php

declare(strict_types=1);

use Akisolu\AnonymousFeedback\Services\RateLimiter;
use PHPUnit\Framework\TestCase;
use Predis\Client as RedisClient;

final class RateLimiterTest extends TestCase
{
    public function testRateLimiterCanBeResolvedFromContainer(): void
    {
        $container = require __DIR__ . '/../../config/container.php';
        $rateLimiter = $container->get(RateLimiter::class);

        $this->assertInstanceOf(RateLimiter::class, $rateLimiter);
    }
}
```

### Test para repositorio con interface

```php
<?php

declare(strict_types=1);

use Akisolu\AnonymousFeedback\Repositories\FeedbackRepositoryInterface;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use function DI\get;

final class FeedbackRepositoryBindingTest extends TestCase
{
    public function testInterfaceResolvesConcreteImplementation(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            FeedbackRepositoryInterface::class => get(\Akisolu\AnonymousFeedback\Repositories\FeedbackRepository::class),
        ]);

        $container = $builder->build();

        $this->assertInstanceOf(
            \Akisolu\AnonymousFeedback\Repositories\FeedbackRepository::class,
            $container->get(FeedbackRepositoryInterface::class)
        );
    }
}
```

---

## 9) Buenas prácticas para no equivocarte

### ✅ Usa el contenedor

Cuando necesites un servicio, no hagas esto:

```php
$pdo = new PDO(...);
```

Haz esto:

```php
$container = require __DIR__ . '/../../config/container.php';
$pdo = $container->get(PDO::class);
```

### ✅ Haz pruebas pequeñas

Cada test debe validar una sola cosa.

### ✅ Nombra los tests con intención

En lugar de:

```php
public function test1(): void
```

usa:

```php
public function test_can_create_feedback_repository(): void
```

### ✅ Usa asserts específicos

Ejemplos de asserts útiles:

```php
$this->assertTrue($condicion);
$this->assertFalse($condicion);
$this->assertInstanceOf(Clase::class, $objeto);
$this->assertSame('valor esperado', $valor);
$this->assertEquals(10, $valor);
```

---

## 10) Comandos útiles para validar el trabajo

Ejecuta todos los tests:

```bash
./vendor/bin/phpunit
```

Ejecuta una carpeta concreta:

```bash
./vendor/bin/phpunit tests/Unit
```

Ejecuta un archivo concreto:

```bash
./vendor/bin/phpunit tests/Integration/DatabaseConnectionTest.php
```

Ejecuta por nombre de método:

```bash
./vendor/bin/phpunit --filter test_can_save_feedback
```

---

## 11) Recomendación de flujo real para trabajar solo

Usa este patrón cada vez que agregues una funcionalidad nueva:

1. Entiende el requisito
2. Escribe el test que describa el comportamiento esperado
3. Ejecuta ese test y confirma que falla
4. Implementa solo la lógica necesaria
5. Ejecuta el test de nuevo
6. Revisa si hay regresiones con un grupo relacionado
7. Refactoriza con cuidado

Esto mantiene el proyecto estable y te ayuda a razonar mejor antes de escribir código.

---

## 12) Resumen rápido

- El DI está en `config/container.php`
- Importas el contenedor con `require __DIR__ . '/../../config/container.php';`
- Resuelves servicios con `$container->get(...)`
- Los tests van en `tests/Unit/` y `tests/Integration/`
- El flujo correcto es TDD: test -> falla -> código -> pasa -> refactor
- Si hay una interfaz, usa `DI\get()` para mapearla a la implementación concreta

---

## 13) Plantilla lista para copiar

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MiNuevoTest extends TestCase
{
    public function test_comportamiento_esperado(): void
    {
        $container = require __DIR__ . '/../../config/container.php';

        // Arrange
        // Act
        // Assert

        $this->assertTrue(true);
    }
}
```

Si quieres, en el siguiente paso te puedo dejar una segunda guía más avanzada con:

- cómo crear tests para repositorios reales,
- cómo probar Redis y PostgreSQL,
- y cómo hacer un flujo TDD completo para una nueva funcionalidad del proyecto.
