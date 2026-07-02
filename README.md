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

- PHP 8.2 or later
- Composer
- Node.js 18 or later
- npm
- MySQL / MariaDB

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
