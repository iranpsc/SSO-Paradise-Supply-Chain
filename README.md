# SSO - Paradise Supply Chain

A centralized **Single Sign-On (SSO)** application built with Laravel for the Paradise Supply Chain ecosystem.

This project provides a secure authentication gateway that enables users to access multiple Paradise Supply Chain services using a single account. It centralizes authentication, session management, and authorization while delivering a consistent login experience across integrated applications.

---

## Features

- Single Sign-On (SSO)
- Secure User Authentication
- Session Management
- Role-Based Authorization
- Protected Routes
- REST API Integration
- CSRF Protection
- Form Validation
- Responsive User Interface
- Error Handling

---

## Tech Stack

- Laravel
- PHP
- Blade
- Tailwind CSS
- Vite
- JavaScript
- MySQL
- Composer
- npm

---

## Requirements

Local (without Docker):

- PHP 8.4 or later
- Composer
- Node.js 18 or later
- npm
- MySQL / MariaDB

Docker / Dokploy:

- Docker Engine 24+ with Compose V2
- For production: a Dokploy-managed Linux server with `/opt/sso` available on the host

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/iranpsc/SSO-Paradise-Supply-Chain.git
```

```bash
cd SSO-Paradise-Supply-Chain
```

### 2. Install dependencies

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

### 3. Configure environment

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Update your database configuration inside the `.env` file.

### 4. Run database migrations

```bash
php artisan migrate
```

```bash
php artisan passport:keys
```

### 5. Start the development servers

Run Laravel:

```bash
php artisan serve
```

Run Vite:

```bash
npm run dev
```

The application will be available at:

```
http://127.0.0.1:8000
```

---

## Docker

The Compose stack runs Apache (no nginx), MySQL 8.4, Redis, a queue worker, the scheduler, and Mailpit. Vite assets are compiled in the image build.

Persistent data is bind-mounted to **`/opt/sso`** on the host so it survives Dokploy redeploys that wipe named volumes and container filesystems:

| Host path | Purpose |
|---|---|
| `/opt/sso/storage` | Laravel storage: uploads, media, Passport keys, logs |
| `/opt/sso/mysql` | MySQL data directory |
| `/opt/sso/redis` | Redis AOF persistence |

Override the root with `SSO_DATA_DIR` if you cannot use `/opt/sso` (for example on a local machine).

### Local Docker

Create the host directories (Linux / WSL / Docker Linux engine):

```bash
sudo mkdir -p /opt/sso/storage /opt/sso/mysql /opt/sso/redis
sudo chown -R 33:33 /opt/sso/storage
```

`33` is `www-data` inside the app image. MySQL will take ownership of `/opt/sso/mysql` on first start.

Copy `.env.example` to `.env` and set `APP_KEY` (`php artisan key:generate` on any machine with the project, then paste the value). Compose overrides `DB_HOST`, Redis, cache, session, and queue so the containers talk to each other.

Start the stack:

```bash
docker compose up -d --build
```

The app is available at `http://localhost:8080`. Laravel's health endpoint is `/up`. Mailpit UI is `http://localhost:8025`.

Useful commands:

```bash
docker compose ps
docker compose logs -f app
docker compose exec app php artisan migrate --force
docker compose down
```

`docker compose down` does **not** delete `/opt/sso`. Do not put database dumps or uploads only inside the image or named volumes.

---

## Deploy with Dokploy

Dokploy rebuilds containers on every deploy. Bind mounts under `/opt/sso` keep MySQL, Redis, OAuth keys, and uploaded files.

### 1. Prepare the server

SSH into the Dokploy host **before the first deploy**:

```bash
sudo mkdir -p /opt/sso/storage /opt/sso/mysql /opt/sso/redis
sudo chown -R 33:33 /opt/sso/storage
sudo chmod 755 /opt/sso
```

Leave `/opt/sso/mysql` and `/opt/sso/redis` for the database images to initialize. Do not delete `/opt/sso` when redeploying.

### 2. Create the application

In Dokploy:

1. Create a **Compose** application (not a single Dockerfile-only service).
2. Connect this Git repository and set the compose file to `docker-compose.yml`.
3. Set the build context to the repository root.

### 3. Environment variables

Set these in the Dokploy environment UI (they are injected at runtime; do not bake secrets into the image). Generate `APP_KEY` once and keep it forever.

```env
APP_NAME="SSO Paradise Supply Chain"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sso.example.com
APP_KEY=base64:...your-generated-key...

DOCKER_DB_DATABASE=sso
DOCKER_DB_USERNAME=sso
DOCKER_DB_PASSWORD=change-me
DB_ROOT_PASSWORD=change-me-root

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Optional: `SSO_DATA_DIR=/opt/sso` (this is already the Compose default).

On production, point `MAIL_*` at a real SMTP server. Mailpit in Compose is for local capture only.

### 4. Domain and ports

- Attach your domain to the **`app`** service.
- Forward Traefik / Dokploy to **container port `80`** (Apache). Do not put nginx in front of the app; Dokploy already terminates TLS.
- Keep MySQL (`3306`) and Redis (`6379`) off the public internet if the host firewall allows it. Prefer connecting to them only on the Docker network.

### 5. Deploy and verify

Deploy from Dokploy. The `app` entrypoint waits for MySQL, runs migrations, creates Passport keys if they are missing, and links `public/storage`.

Check:

- `https://sso.example.com/up` returns **Application up**
- Login page loads
- After a second deploy, existing users, media, and OAuth keys are still present under `/opt/sso`

If `/up` fails, inspect `app`, `mysql`, and `redis` logs in Dokploy. A missing or changing `APP_KEY` will invalidate sessions and encrypted data.

---

## Environment Variables

Example configuration:

```env
APP_NAME="SSO Paradise Supply Chain"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

---

## Project Structure

```
app/
bootstrap/
config/
database/
public/
resources/
├── css/
├── js/
└── views/
routes/
storage/
tests/
vendor/
```

---

## Authentication Flow

```
          User
            │
            ▼
       Login Page
            │
            ▼
 Authentication Service
            │
            ▼
   Session / Access Token
            │
            ▼
   Protected Applications
```

---

## Available Commands

### Backend

Start the development server:

```bash
php artisan serve
```

Run database migrations:

```bash
php artisan migrate
```

Clear all caches:

```bash
php artisan optimize:clear
```

Run tests:

```bash
php artisan test
```

### Frontend

Start the Vite development server:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

---

## Development

During development, run both services simultaneously:

Backend:

```bash
php artisan serve
```

Frontend:

```bash
npm run dev
```

Laravel handles the backend application while Vite compiles frontend assets and provides Hot Module Replacement (HMR).

---

## Security

This project follows Laravel's built-in security best practices, including:

- CSRF Protection
- Input Validation
- Authentication Middleware
- Authorization
- Secure Session Handling
- Encrypted Configuration
- XSS Protection

---

## Contributing

Contributions are welcome.

1. Fork the repository.

2. Create a feature branch.

```bash
git checkout -b feature/your-feature
```

3. Commit your changes.

```bash
git commit -m "Add your feature"
```

4. Push the branch.

```bash
git push origin feature/your-feature
```

5. Open a Pull Request.

---

## License

This project is proprietary software developed for the Paradise Supply Chain ecosystem. Unauthorized distribution, modification, or commercial use is prohibited unless explicitly permitted by the project owner.
