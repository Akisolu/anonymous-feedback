# 📝 Anonymous Feedback System

Plataforma web para enviar, almacenar y gestionar comentarios anónimos. Está desarrollada en **PHP nativo** con una estructura modular, aprovecha **PostgreSQL** para persistencia y audit logging, y usa **Redis** para control de frecuencia de envíos sin comprometer el anonimato.

## 🚀 Descripción

El proyecto permite a cualquier usuario no autenticado enviar feedback de forma anónima. El administrador (`Owner`) accede a un panel seguro para revisar mensajes, cambiar estados y administrar los registros. El sistema no guarda IP en texto plano y utiliza Redis para un rate limiting temporal de envíos.

> [!NOTE]
> 🚧 **En desarrollo**
Este proyecto se encuentra actualmente en una fase activa de desarrollo y refactorización. La arquitectura base, los esquemas de PostgreSQL/Redis y los diagramas de flujo ya están estructurados, pero el código fuente completo se actualiza periódicamente.

## 🧩 Tecnologías usadas

- PHP 8.4.1+
- PostgreSQL 15+
- Redis 6+
- Composer
- PHPUnit
- FastRoute
- PHP-DI
- Symfony HttpFoundation

## 🧾 Detalles de funcionamiento

- Los envíos de feedback son anónimos y no requieren autenticación.
- El administrador accede a la interfaz protegida para leer y gestionar mensajes.
- Los cambios de estado de `feedbacks` se auditan en `feedback_records` mediante un trigger PostgreSQL.
- Redis aplica un límite de tasa de envíos (máximo 10 mensajes por cada 10 minutos por identificador anónimo).

## 🛠 Requisitos previos

Asegúrate de tener instaladas las siguientes dependencias de sistema y PHP:

- PHP `>= 8.4.1` con extensiones: `pdo_pgsql`, `redis`, `mbstring`, `json`, `xml`
- Composer `>= 2.0`
- PostgreSQL `>= 15`
- Redis Server `>= 6.0`
- `php-cli`, `php-xml`, `php-mbstring`, `php-pgsql`, `php-curl`
- `unzip`, `curl`, `postgresql-contrib`, `redis-server`

## 📦 Dependencias del proyecto

En este proyecto se usan dependencias PHP instaladas con Composer para soportar la arquitectura y las pruebas:

- `FastRoute` — enrutamiento de solicitudes HTTP y manejo de rutas.
- `PHP-DI` — inyección de dependencias para desacoplar controladores, repositorios y servicios.
- `Symfony HttpFoundation` — abstracción de peticiones y respuestas HTTP.
- `PHPUnit` — framework de pruebas para validar el comportamiento del backend.

## 🧭 Qué hace cada tecnología en este proyecto

- **PHP**: lenguaje de servidor que procesa las solicitudes entrantes, valida formularios, ejecuta la lógica de negocio y genera las respuestas HTTP.
- **PostgreSQL**: almacena la información persistente del sistema: usuarios, feedbacks, estados y auditoría de cambios. El trigger `feedback_state_change()` mantiene el historial de cambios de estado en `feedback_records`.
- **Redis**: servicio en memoria que controla la frecuencia de envíos anónimos con rate limiting, sin persistir direcciones IP ni datos sensibles de los remitentes.

## 📁 Estructura de carpetas (placeholder)

- `docs/` – documentación del proyecto.
- `scripts/` – scripts para administrar PostgreSQL, Redis y setup.
- `src/` - POR DEFINIR
- `schema.sql` – definición de tablas, relaciones y trigger de auditoría.
- `seed.sql` – datos iniciales para la base de datos.
- `.env.example` – plantilla de variables de entorno.
- `composer.json` - configuración donde se definen las dependencias y versiones del proyecto.
- `composer.lock` - se encarga de "congelar" y registrar las versiones exactas de las dependencias

## ⚙️ Configuración de entorno

Copia `.env.example` a `.env` y completa los valores según tu entorno:

```env
DATABASE_NAME=
DATABASE_USER=
DATABASE_PASSWORD=
DATABASE_PORT=
REDIS_PORT=
```

## 🧪 Instalación rápida

1. Instala dependencias del sistema (el script `scripts/setup.sh` instala paquetes comunes en Debian/Ubuntu):

```bash
bash scripts/setup.sh
```

2. Instala dependencias PHP con Composer:

```bash
composer install
```

## 🗄️ Guía de configuración de PostgreSQL

### 1. Crear base de datos y usuario

Acceda a PostgreSQL como superusuario:

```bash
sudo -u postgres psql
```

Cree la base de datos y el usuario de la aplicación:

```sql
CREATE DATABASE <nombre_de_la_base_de_datos>;
CREATE USER <usuario_de_la_base_de_datos> WITH PASSWORD '<contraseña_segura>';
```

### 2. Otorgar permisos

Conéctese a la base de datos recién creada:

```sql
\c <nombre_de_la_base_de_datos>
```

Otorgue los permisos de esquema necesarios y los privilegios predeterminados:

```sql
-- Otorgar permisos para la creación y el uso del esquema (Requerido para PostgreSQL 15+)
GRANT ALL ON SCHEMA public TO <usuario_de_la_base_de_datos>;
GRANT CONNECT ON DATABASE <nombre_de_la_base_de_datos> TO <usuario_de_la_base_de_datos>; GRANT USAGE, CREATE ON SCHEMA public TO <usuario_base_de_datos>;

-- Otorgar privilegios DML sobre objetos existentes
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO <usuario_base_de_datos>;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO <usuario_base_de_datos>;

-- Establecer privilegios predeterminados para tablas y secuencias futuras
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO <usuario_base_de_datos>;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO <usuario_base_de_datos>;
```

*(Alternativa opcional: `ALTER SCHEMA public OWNER TO <usuario_base_de_datos>;`)*

### 3. Aplicar esquema y datos iniciales

Ejecutar las migraciones de la base de datos y los archivos de inicialización mediante el enlace de host TCP (`-h 127.0.0.1`) para aplicar la autenticación por contraseña:

```bash
psql -h 127.0.0.1 -U <usuario_base_de_datos> -d <nombre_base_de_datos> -f schema.sql
psql -h 127.0.0.1 -U <usuario_base_de_datos> -d <nombre_base_de_datos> -f seed.sql
```

## 🔥 Configuración de Redis

Este proyecto usa Redis para el control de frecuencia de envío de mensajes. El script `scripts/redis.sh` permite iniciar, detener y consultar el servicio.

Iniciar Redis:

```bash
bash scripts/redis.sh --start
```

Verificar estado:

```bash
bash scripts/redis.sh --status
```

Ingresar a la consola de Redis:

```bash
bash scripts/redis.sh --cli
```

## 🧭 Uso

1. Inicia PostgreSQL:

```bash
bash scripts/database.sh --start
```

2. Inicia Redis:

```bash
bash scripts/redis.sh --start
```

3. Ejecuta tu servidor PHP o el servidor de desarrollo local según la configuración de la aplicación.

## 📌 Notas

- Asegúrate de que `.env` contenga los datos correctos antes de iniciar la aplicación.
- El trigger de auditoría `feedback_state_change()` registra cambios de estado en `feedback_records`.
- El sistema está diseñado para preservar el anonimato y no persistir datos de identidad asociados a los mensajes.
