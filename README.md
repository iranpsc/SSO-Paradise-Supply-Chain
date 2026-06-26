<div align="center">

# SSO Paradise Supply Chain

Secure, Scalable & Modern Single Sign-On (SSO) Service for Paradise Supply Chain

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Node.js](https://img.shields.io/badge/Node.js-20+-339933?logo=node.js)]()
[![Next.js](https://img.shields.io/badge/Next.js-15-black?logo=next.js)]()
[![TypeScript](https://img.shields.io/badge/TypeScript-5+-3178C6?logo=typescript)]()

</div>

---

# 📖 Overview

**SSO Paradise Supply Chain** is a centralized authentication and authorization service designed for the Paradise Supply Chain ecosystem.

It enables users to authenticate once and securely access multiple applications while providing a unified identity management experience.

The project focuses on:

- Secure Authentication
- Centralized User Management
- Token-based Authorization
- Scalable Architecture
- Enterprise-ready Security

---

# ✨ Features

- 🔐 Secure Login & Authentication
- 👤 User Identity Management
- 🎫 JWT Access & Refresh Tokens
- 🔄 Token Refresh Mechanism
- 🛡️ Role-Based Access Control (RBAC)
- 🔑 Password Recovery
- 📧 Email Verification
- 📱 Responsive User Interface
- ⚡ Built with Modern Web Technologies
- 🌍 RESTful API Integration

---

# 🏗 Architecture

```
                +----------------+
                |    Client App  |
                +-------+--------+
                        |
                        |
                 Authentication
                        |
                        ▼
             +----------------------+
             |      SSO Server      |
             +----------+-----------+
                        |
        +---------------+----------------+
        |                                |
        ▼                                ▼
 User Database                   Token Service
        |
        ▼
 Protected Applications
```

---

# 🛠 Tech Stack

| Technology | Description |
|------------|-------------|
| Next.js | React Framework |
| React | UI Library |
| TypeScript | Type Safety |
| Tailwind CSS | Styling |
| Axios | HTTP Client |
| JWT | Authentication |
| REST API | Communication |

---

# 📂 Project Structure

```
src/
│
├── app/
├── components/
│   ├── common/
│   ├── layout/
│   └── features/
│
├── hooks/
├── services/
├── utils/
├── types/
├── store/
├── styles/
└── assets/
```

---

# 🚀 Getting Started

## Clone Repository

```bash
git clone https://github.com/iranpsc/SSO-Paradise-Supply-Chain.git
```

```bash
cd SSO-Paradise-Supply-Chain
```

---

## Install Dependencies

Using npm

```bash
npm install
```

or

```bash
yarn
```

or

```bash
pnpm install
```

---

## Environment Variables

Create a `.env.local` file.

```env
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_APP_NAME=Paradise SSO
```

---

## Development

```bash
npm run dev
```

Open

```
http://localhost:3000
```

---

## Production Build

```bash
npm run build
```

```bash
npm start
```

---

# 🔑 Authentication Flow

```text
User
 │
 ▼
Login
 │
 ▼
SSO Server
 │
 ├── Validate Credentials
 ├── Generate JWT
 └── Generate Refresh Token
 │
 ▼
Client
 │
 ▼
Protected APIs
```

---

# 📡 API

Example Login Request

```http
POST /auth/login
```

Request

```json
{
  "username": "user@example.com",
  "password": "********"
}
```

Response

```json
{
  "accessToken": "...",
  "refreshToken": "...",
  "expiresIn": 3600
}
```

---

# 🔒 Security

- JWT Authentication
- Refresh Tokens
- Protected Routes
- HTTPS Ready
- Secure Cookie Support
- Input Validation
- Authentication Middleware

---

# 📜 Available Scripts

```bash
npm run dev
npm run build
npm run start
npm run lint
```

---

# 🤝 Contributing

Contributions are welcome.

1. Fork the repository
2. Create your feature branch

```bash
git checkout -b feature/amazing-feature
```

3. Commit your changes

```bash
git commit -m "Add amazing feature"
```

4. Push to the branch

```bash
git push origin feature/amazing-feature
```

5. Open a Pull Request

---

# 📄 License

This project is licensed under the MIT License.

---

<div align="center">

Made with ❤️ by Paradise Supply Chain Team

</div>
