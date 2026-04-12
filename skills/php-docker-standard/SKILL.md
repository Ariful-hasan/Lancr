---
name: php-docker-standard
description: Industry-standard Dockerization for PHP applications (Symfony/Laravel). Use when starting a new project or migrating an existing one to a professional, multi-stage Docker environment with UID/GID synchronization, optimized Nginx/FPM configs, and robust health checks.
---

# PHP Docker Standard Skill

This skill provides a professional, production-ready Docker infrastructure for PHP applications.

## Workflows

### 1. Initialize Docker Infrastructure
When the user asks to "dockerize" a PHP project:
1.  **Detect Project Type**: Identify if it's Symfony (look for `bin/console`, `public/index.php`) or Laravel (look for `artisan`, `public/index.php`).
2.  **Generate Files**:
    - Copy `assets/Dockerfile` to `docker/Dockerfile`.
    - Copy `assets/docker-compose.yml` to the root.
    - Copy `assets/nginx/default.conf` to `docker/nginx/conf.d/default.conf`.
    - Copy `assets/php/fpm.conf` to `docker/php/fpm.conf`.
    - Copy `assets/php/opcache.ini` to `docker/php/opcache.ini`.
3.  **Adjust Placeholders**:
    - Replace `${PHP_VERSION}` in the `Dockerfile` based on `composer.json`.
    - Replace `${APP_NAME}` in `docker-compose.yml` with the project name.
4.  **Update .env.example**: Add the necessary DB and Docker environment variables.

## Core Features
- **Multi-Stage Build**: Separates `php_base`, `php_dev`, `php_build`, and `php_prod` for minimal production images.
- **Permission Harmony**: Uses `shadow` in the `php_dev` stage to sync container UID/GID with the host user.
- **Performance**: Optimized OPcache and Nginx settings (1000 keepalive requests, large fastcgi buffers).
- **Self-Healing**: Robust health checks for Database (mysqladmin), App (fcgi /ping), and Web (wget).

## Usage Notes
- **Local Dev**: Use the `php_dev` target in `docker-compose.yml`.
- **Production**: Build with the `php_prod` target.
- **Composer**: Always use `--no-scripts` during Docker builds to prevent failures due to missing runtime dependencies (DB, etc.).
