# RabetXRPFlow ERP

A modern ERP (Enterprise Resource Planning) application built with Laravel 12 and Docker.

## 🚀 Features

- **User Management** - Role-based access control with authentication
- **Dashboard** - Real-time analytics and business insights
- **Inventory Management** - Track stock, products, and supplies
- **Finance & Accounting** - Invoices, payments, and financial reports
- **HR Management** - Employee records, attendance, and payroll
- **Project Management** - Task tracking and team collaboration
- **Customer Relations** - CRM capabilities for client management
- **Document Management** - File storage and sharing
- **XRP Integration** - Blockchain-powered payment capabilities

## 🛠️ Tech Stack

- **Backend:** Laravel 12 (PHP 8.4)
- **Frontend:** Blade Templates + Vite
- **Database:** MySQL (MariaDB)
- **Cache:** Redis
- **Queue:** Database driver
- **Docker:** PHP-FPM, Nginx, MySQL, Redis

## 📋 Requirements

- Docker & Docker Compose
- At least 4GB RAM available

## 🔧 Installation

1. Clone the repository:
```bash
git clone <repository-url>
```

2. Start Docker containers:
```bash
docker compose up -d
```

3. Install dependencies:
```bash
docker compose run --rm composer install
```

4. Generate application key:
```bash
docker compose run --rm artisan key:generate
```

5. Run migrations:
```bash
docker compose run --rm artisan migrate
```

6. Access the application:
```
http://localhost:8000
```

## 📁 Project Structure

```
├── docker/               # Docker configuration files
│   ├── nginx/           # Nginx configs
│   └── php/             # PHP configs and Dockerfile
├── src/                 # Laravel application
│   ├── app/             # Application code
│   ├── config/          # Configuration files
│   ├── database/        # Migrations, factories, seeders
│   ├── resources/       # Views, assets
│   └── routes/          # Route definitions
├── docker-compose.yml   # Development environment
└── docker-compose.prod.yml  # Production environment
```

## 🔒 Security

- Environment variables are stored in `.env` (not committed)
- Use strong passwords in production
- Keep Laravel and dependencies updated

## 📄 License

MIT License