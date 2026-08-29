# Sistema de Feedback Anónimo

Plataforma web para enviar, almacenar y gestionar comentarios anónimos. Está construida con **PHP nativo** en una estructura modular, usa **PostgreSQL** para persistencia y auditoría, y usa **Redis** para aplicar límites de frecuencia de envío sin comprometer el anonimato.

## 🚀 Descripción

El proyecto permite a cualquier usuario no autenticado enviar feedback de forma anónima. El administrador (`Owner`) accede a un panel seguro para revisar mensajes, cambiar estados y administrar registros. El sistema no almacena direcciones IP en texto plano y usa Redis para aplicar un límite temporal de envíos.

> [!NOTE]
> 🚧 **Trabajo en progreso**  
> Este proyecto se encuentra en una fase activa de desarrollo y refactorización. La arquitectura base, los esquemas de PostgreSQL/Redis y los diagramas de flujo ya están estructurados, pero el código fuente completo sigue recibiendo actualizaciones periódicas.

## 🧩 Tecnologías utilizadas

- PHP 8.4.1+
- PostgreSQL 15+
- Redis 6+
- Composer
- PHPUnit
- FastRoute
- PHP-DI
- Symfony HttpFoundation

## 🧾 Detalles del comportamiento

- Los envíos de feedback son anónimos y no requieren autenticación.
- El administrador puede acceder a la interfaz protegida para leer y gestionar mensajes.
- Los cambios de estado se auditan en `feedback_records` mediante un trigger de PostgreSQL.
- Redis aplica un límite de tasa a los envíos (máximo 10 mensajes cada 10 minutos por identificador anónimo).

## 🛠 Requisitos previos

Asegúrate de tener instaladas las siguientes dependencias del sistema y de PHP:

- PHP `>= 8.4.1` con extensiones: `pdo_pgsql`, `redis`, `mbstring`, `json`, `xml`
- Composer `>= 2.0`
- PostgreSQL `>= 15`
- Redis Server `>= 6.0`
- `php-cli`, `php-xml`, `php-mbstring`, `php-pgsql`, `php-curl`
- `unzip`, `curl`, `postgresql-contrib`, `redis-server`

## 📦 Dependencias del proyecto

Este proyecto usa dependencias de PHP instaladas con Composer para soportar la arquitectura y las pruebas:

- `FastRoute` — enrutamiento de peticiones HTTP y manejo de rutas.
- `PHP-DI` — inyección de dependencias para desacoplar controladores, repositorios y servicios.
- `Symfony HttpFoundation` — capa de abstracción para peticiones y respuestas HTTP.
- `PHPUnit` — framework de pruebas para validar el comportamiento del backend.

## 🧭 Qué hace cada tecnología en este proyecto

- **PHP**: lenguaje del lado del servidor que procesa solicitudes entrantes, valida formularios, ejecuta la lógica de negocio y genera respuestas HTTP.
- **PostgreSQL**: almacena la información persistente del sistema: usuarios, feedbacks, estados y registros de auditoría. El trigger `feedback_state_change()` mantiene el historial de cambios de estado en `feedback_records`.
- **Redis**: servicio en memoria que controla la frecuencia de envíos anónimos con limitación de tasa, sin persistir direcciones IP ni datos sensibles del remitente.

## 📁 Estructura de carpetas (placeholder)

- `docs/` – documentación del proyecto.
- `scripts/` – scripts para gestionar PostgreSQL, Redis y la configuración inicial.
- `src/` – TBD
- `schema.sql` – definiciones de tablas, relaciones y trigger de auditoría.
- `seed.sql` – datos iniciales de la base de datos.
- `.env.example` – plantilla de variables de entorno.
- `composer.json` - archivo de configuración donde se definen las dependencias y versiones del proyecto.
- `composer.lock` - se encarga de "congelar" y registrar las versiones exactas de las dependencias.

## ⚙️ Configuración del entorno

Copia `.env.example` a `.env` y completa los valores para tu entorno:

```env
DATABASE_NAME=
DATABASE_USER=
DATABASE_PASSWORD=
DATABASE_PORT=
REDIS_PORT=
```

## 🚀 Inicio rápido: elige tu tipo de instalación

Hay dos formas soportadas de ejecutar este proyecto. Elige solo una según cómo quieras levantar el entorno.

- Si usas Docker, los contenedores se encargan de PostgreSQL y Redis por ti.
- Si no usas Docker, debes instalar y ejecutar PostgreSQL y Redis localmente en tu máquina.

> [!IMPORTANT]
> Docker y la instalación local no están pensados para mezclarse en la misma sesión. Si ya has levantado los servicios con Docker, no ejecutes `scripts/database.sh` ni `scripts/redis.sh` en tu máquina a menos que quieras un entorno local adicional y separado.

## 🧪 Opción A: Instalación con Docker (Recomendada)

Usa esta opción si quieres una configuración rápida y no quieres preparar PostgreSQL y Redis manualmente.

1. Instala Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

2. Instala Docker y Docker Compose.

> [!NOTE]
> 🚧 **Instalación con Docker**  
> Los pasos exactos dependen del sistema operativo. Sigue la documentación oficial de Docker para tu SO antes de continuar.

3. Construye y levanta los contenedores:

```bash
docker-compose up --build -d
```

4. Accede a la aplicación:

- Aplicación web: `http://localhost:8000`
- PostgreSQL dentro de Docker: `docker compose exec db psql -U feedback_user -d feedback`

5. Si los contenedores ya están en ejecución, normalmente no necesitas ejecutar los scripts locales que aparecen más abajo.

### Notas específicas de Docker

- PostgreSQL es gestionado por el contenedor de Docker.
- Redis es gestionado por el contenedor de Docker.
- El proyecto debe configurarse con los puertos expuestos por Docker, no con una instalación local del equipo.
- Mantén los valores de `.env` alineados con la configuración del contenedor.

## 🧪 Opción B: Instalación local (sin Docker)

Usa esta opción si quieres ejecutar los servicios directamente en tu máquina.

1. Instala las dependencias del sistema (el script `scripts/setup.sh` instala paquetes comunes en Debian/Ubuntu):

```bash
bash scripts/setup.sh
```

2. Instala las dependencias de PHP con Composer:

```bash
composer install
```

3. Inicia PostgreSQL:

```bash
bash scripts/database.sh --start
```

4. Inicia Redis:

```bash
bash scripts/redis.sh --start
```

5. Ejecuta tu servidor PHP o el servidor de desarrollo local según la configuración de la aplicación.

### Notas de la instalación local

- Esta opción asume que PostgreSQL y Redis están instalados y funcionando en tu máquina host.
- Debes administrarlos manualmente con los scripts dentro de `scripts/`.
- Esta es la opción correcta si no estás usando contenedores Docker para la base de datos ni la caché.

## 🗄️ Guía de configuración de PostgreSQL (solo para instalación local)

Esta sección no es necesaria si usas Docker, porque la base de datos se crea en el servicio de Docker.

### 1. Crear base de datos y usuario

Accede a PostgreSQL como superusuario:

```bash
sudo -u postgres psql
```

Crea la base de datos y el usuario de la aplicación:

```sql
CREATE DATABASE <database-name>;
CREATE USER <database_user> WITH PASSWORD 'secure_password_here';
```

### 2. Conceder permisos

Conéctate a la base de datos recién creada:

```sql
\c <database_name>
```

Otorga los permisos del esquema necesarios y los privilegios predeterminados:

```sql
-- Conceder permisos para la creación y el uso del esquema (requerido para PostgreSQL 15+)
GRANT ALL ON SCHEMA public TO <database_user>;
GRANT CONNECT ON DATABASE <database_name> TO <database_user>;
GRANT USAGE, CREATE ON SCHEMA public TO <database_user>;

-- Conceder privilegios DML sobre objetos existentes
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO <database_user>;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO <database_user>;

-- Establecer privilegios predeterminados para tablas y secuencias futuras
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO <database_user>;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO <database_user>;
```

*(Alternativa opcional: `ALTER SCHEMA public OWNER TO <database_user>;`)*

### 3. Aplicar esquema y datos iniciales

Ejecuta las migraciones de la base de datos y los archivos de inicialización usando el enlace TCP (`-h 127.0.0.1`) para forzar autenticación por contraseña:

```bash
psql -h 127.0.0.1 -U <database_user> -d <database_name> -f schema.sql
psql -h 127.0.0.1 -U <database_user> -d <database_name> -f seed.sql
```

## 🔥 Configuración de Redis

Este proyecto usa Redis para controlar la frecuencia de envíos de mensajes. El script `scripts/redis.sh` permite iniciar, detener y comprobar el servicio.

### Si usas Docker

Redis ya se levanta con el entorno de Docker. Normalmente no necesitas ejecutar comandos de Redis en tu máquina host.

### Si no usas Docker

Inicia Redis:

```bash
bash scripts/redis.sh --start
```

Comprueba el estado:

```bash
bash scripts/redis.sh --status
```

Accede a la consola de Redis:

```bash
bash scripts/redis.sh --cli
```

## 📌 Notas

- Asegúrate de que `.env` contenga los valores correctos antes de iniciar la aplicación.
- El trigger de auditoría `feedback_state_change()` registra cambios de estado en `feedback_records`.
- El sistema está diseñado para preservar el anonimato y no guardar datos de identidad vinculados a los mensajes.
- Si usas Docker, normalmente no necesitas iniciar manualmente los servicios locales de base de datos ni caché.
- Si no usas Docker, debes iniciar PostgreSQL y Redis localmente antes de arrancar la aplicación.
