# Anonymous Feedback System

Web platform for sending, storing, and managing anonymous comments. It is built with **native PHP** in a modular structure, uses **PostgreSQL** for persistence and audit logging, and uses **Redis** to enforce submission rate limiting while preserving anonymity.

## 🚀 Description

The project allows any unauthenticated user to submit feedback anonymously. The administrator (`Owner`) accesses a secure dashboard to review messages, change statuses, and manage records. The system does not store IP addresses in plain text and uses Redis for temporary submission rate limiting.

> [!NOTE]
> 🚧 **Work in Progress**  
> This project is currently in an active development and refactoring phase. The base architecture, PostgreSQL/Redis schemas, and flowcharts are already structured, but the complete source code continues to receive regular updates.

## 🧩 Technologies used

- PHP 8.1+
- PostgreSQL 13+
- Redis 6+
- Composer
- PHPUnit
- FastRoute
- PHP-DI
- Symfony HttpFoundation

## 🧾 Behavior details

- Feedback submissions are anonymous and do not require authentication.
- The administrator can access the protected interface to read and manage messages.
- Feedback status changes are audited in `feedback_records` using a PostgreSQL trigger.
- Redis applies a rate limit to submissions (maximum 10 messages per 10 minutes per anonymous identifier).

## 🛠 Prerequisites

Make sure the following system and PHP dependencies are installed:

- PHP `>= 8.4` with extensions: `pdo_pgsql`, `redis`, `mbstring`, `json`, `xml`
- Composer `>= 2.0`
- PostgreSQL `>= 13`
- Redis Server `>= 6.0`
- `php-cli`, `php-xml`, `php-mbstring`, `php-pgsql`, `php-curl`
- `unzip`, `curl`, `postgresql-contrib`, `redis-server`

## 📦 Project dependencies

This project uses PHP dependencies installed with Composer to support the architecture and testing:

- `FastRoute` — HTTP request routing and route handling.
- `PHP-DI` — dependency injection to decouple controllers, repositories, and services.
- `Symfony HttpFoundation` — abstraction layer for HTTP requests and responses.
- `PHPUnit` — testing framework to validate backend behavior.

## 🧭 What each technology does in this project

- **PHP**: server-side language that processes incoming requests, validates forms, executes business logic, and generates HTTP responses.
- **PostgreSQL**: stores the system's persistent information: users, feedbacks, states, and change audit logs. The `feedback_state_change()` trigger maintains the state change history in `feedback_records`.
- **Redis**: in-memory service that controls anonymous submission frequency with rate limiting, without persisting IP addresses or sensitive sender data.

## 📁 Folder structure (placeholder)

- `docs/` – project documentation.
- `scripts/` – scripts to manage PostgreSQL, Redis, and setup.
- `src/` – TBD
- `schema.sql` – table definitions, relationships, and audit trigger.
- `seed.sql` – initial database data.
- `.env.example` – environment variable template.
- `composer.json` - configuration file where project dependencies and versions are defined.
- `composer.lock` - handles "freezing" and registering the exact versions of dependencies.

## ⚙️ Environment configuration

Copy `.env.example` to `.env` and complete the values for your environment:

```env
DATABASE_NAME=
DATABASE_USER=
DATABASE_PASSWORD=
DATABASE_PORT=
REDIS_PORT=
```

## 🧪 Quick installation

1. Install system dependencies (the `scripts/setup.sh` script installs common Debian/Ubuntu packages):

```bash
bash scripts/setup.sh
```

2. Install PHP dependencies with Composer:

```bash
composer install
```

## 🗄️ PostgreSQL configuration

### 1. Create the database and the user

Access PostgreSQL as the superuser:

```bash
sudo -u postgres psql
```

Create the database and application user:

```sql
CREATE DATABASE <database_name>;
CREATE USER <database_user> WITH PASSWORD '<secure_password>';
```

### 2. Grant permissions

```sql
GRANT CONNECT ON DATABASE <database_name> TO <database_user>;
GRANT USAGE ON SCHEMA public TO <database_user>;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO <database_user>;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO <database_user>;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO <database_user>;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON ALL SEQUENCES TO <database_user>;
```

### 3. Run the schema and initial data

Connect to the database and apply the schema:

```bash
psql -h 127.0.0.1 -U <database_user> -d <database_name> -f schema.sql
```

If you want to preload initial data:

```bash
psql -h 127.0.0.1 -U <database_user> -d <database_name> -f seed.sql
```

## 🔥 Redis configuration

This project uses Redis to control the rate of message submissions. The `scripts/redis.sh` script can start, stop, and check the service.

Start Redis:

```bash
bash scripts/redis.sh --start
```

Check status:

```bash
bash scripts/redis.sh --status
```

Enter the Redis CLI:

```bash
bash scripts/redis.sh --cli
```

## 🧭 Usage

1. Start PostgreSQL:

```bash
bash scripts/database.sh --start
```

2. Start Redis:

```bash
bash scripts/redis.sh --start
```

3. Run your PHP server or the local development server according to the application configuration.

## 📌 Notes

- Make sure `.env` contains the correct values before starting the application.
- The audit trigger `feedback_state_change()` records state changes in `feedback_records`.
- The system is designed to preserve anonymity and not persist identity-linked data associated with messages.
