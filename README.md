# Lancr | High-Performance Freelance Management API

**Lancr** is a robust, Domain-Driven Design (DDD) backend built with **Symfony 8.0** and **PHP 8.4+**. It manages the end-to-end lifecycle of freelancer engagements, from work order creation to milestone-based automated payments.

---

## 🎯 Project Objective
The goal of this repository is to demonstrate a "Senior-Level" approach to a fintech-adjacent platform. It prioritizes **data integrity**, **strict type safety**, and **architectural decoupling** to ensure the system is maintainable, testable, and audit-ready.

### Core Business Workflow
1. **Engagement**: Clients create a `WorkOrder` and assign it to a `Freelancer`.
2. **Milestones**: Projects are broken down into granular `Milestones` with individual budgets.
3. **Execution**: Freelancers submit work; Clients review and approve.
4. **Automated Settlement**: Approval triggers an immediate `Payment` record.
5. **Auto-Completion**: The system automatically closes a `WorkOrder` once the total budget is fully settled.

---

## 🏗️ Architectural Highlights (The "Senior Way")

### 1. Domain-Driven Design (DDD)
Entities are kept as "dumb" POPOs (Plain Old PHP Objects). Business logic is strictly contained within **Services**, while data access is abstracted behind **Repository Interfaces**.

### 2. Immutable Data Transfer Objects (DTOs)
- **Request DTOs**: Payload validation is handled via Symfony's `MapRequestPayload`, ensuring only valid, typed data reaches the services.
- **Response DTOs**: Entities are **never** exposed directly to the API. Every response uses `final readonly` DTOs to prevent data leakage and decouple the API contract from the database schema.

### 3. Financial Precision (BCMath)
To avoid floating-point errors common in financial applications, all amount calculations (budgeting, sums, and payments) are performed using the **BCMath** extension with 2-decimal precision.

### 4. Standardized API Enveloping
Every API response follows a consistent "Enveloped" format (`data`, `message`, `meta`) via a centralized `ApiResponder` trait, providing a predictable contract for frontend consumers.

### 5. Event-Driven Architecture
State changes (like milestone approval) dispatch asynchronous messages via **Symfony Messenger**, allowing for decoupled side effects like email notifications or audit logging without slowing down the main request.

---

## 🛠️ Tech Stack
- **Language**: PHP 8.4 (utilizing Property Hooks, Readonly Classes, and Constructor Promotion)
- **Framework**: Symfony 8.0 (Service-Oriented Architecture)
- **Database**: PostgreSQL via Doctrine ORM (Data Mapper Pattern)
- **Security**: JWT Authentication (LexikJWTAuthenticationBundle)
- **Queue**: Symfony Messenger

---

## 🚀 Quick Start (Docker)

```bash
# Clone the repository
git clone https://github.com/your-username/lancr.git

# Start the environment
docker-compose up -d

# Install dependencies
docker-compose exec php composer install

# Run migrations
docker-compose exec php bin/console doctrine:migrations:migrate
```

---

## 🧑‍💻 Engineering Standards
- **Strict Typing**: `declare(strict_types=1);` in every file.
- **Inversion of Control**: Services depend on Interfaces, not concrete Repositories.
- **Validation**: Centralized `ExceptionListener` for unified error reporting (422 for validation, 403 for access, etc.).
