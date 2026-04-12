# Lancr Project Overview

## Business Model & Workflow
Lancr is a freelancer platform with the following core lifecycle:
1. **Creation**: Client creates a `WorkOrder`.
2. **Assignment**: Client assigns the `WorkOrder` to a `Freelancer`.
3. **Acceptance**: `Freelancer` accepts the assignment.
4. **Milestones**: Client adds one or more `Milestones` to the `WorkOrder`.
5. **Submission**: `Freelancer` submits work for a `Milestone`.
6. **Approval**: Client approves the `Milestone`.
7. **Payment**: Payment is released for the approved `Milestone`.
8. **Completion**: `WorkOrder` automatically switches to `COMPLETED` once all milestones are paid.

## Tech Stack
- **PHP**: 8.4+ (utilizing latest features like property hooks and constructor property promotion).
- **Framework**: Symfony 8.0 (Service-Oriented Architecture).
- **Database**: PostgreSQL (via Doctrine ORM).
- **Authentication**: JWT (LexikJWTAuthenticationBundle).
- **Architecture**: Domain-Driven Design (DDD) principles with DTOs and Repository Interfaces.

## Engineering Standards

### 1. Data Mapper (Doctrine) vs. Active Record (Eloquent)
- Unlike Laravel's Eloquent, entities are "dumb" POPOs (Plain Old PHP Objects).
- **Never** perform database operations inside entities.
- Use the **Unit of Work**: Changes are tracked by the `EntityManager` and persisted only when `flush()` is called.
- Use **Repositories** for fetching data and **Services** for business logic.

### 2. DTOs & Validation
- Use **DTOs** (`src/Dto`) for all request payloads to decouple the API contract from the Entity schema.
- Validate DTOs using Symfony's `Validator` component before mapping them to Entities.

### 3. Dependency Injection (DI)
- Favor **Constructor Injection** over any other form of DI.
- Leverage Symfony's **Autowiring** and **Autoconfiguration**.

### 4. Messaging & Events
- Use **Symfony Messenger** for asynchronous tasks (e.g., sending emails, background processing).
- Use **Domain Events** (or Messenger messages) to trigger side effects when an entity's status changes.

### 5. Type Safety
- Strict typing is mandatory: `declare(strict_types=1);` in every file.
- Use PHP Enums for all statuses and roles.

## Developer Context
The lead developer is a senior PHP expert transitioning from Laravel. 
When explaining concepts, use analogies to Laravel (e.g., "Think of a Voter as a Policy/Gate").
Focus on "The Symfony Way": explicit configuration, strong typing, and decoupled components.
