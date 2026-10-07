# Guidelines for AI Agents (Map-OS)

This document provides instructions and technical guidelines for AI agents working in the Map-OS repository.

## Project Overview

Map-OS is an open-source Service Order and Business Management system built in PHP using the CodeIgniter 3 framework.

## Tech Stack & Requirements

- **Language:** PHP >= 8.5
- **Framework:** CodeIgniter 3 (`application/`)
- **Database:** MySQL / MariaDB (managed via CodeIgniter Query Builder and Migrations in `application/database/migrations/`)
- **Dependency Manager:** Composer (vendor directory configured at `application/vendor`)

## Architecture & Directory Structure

- `application/controllers/`: Request handlers and business logic entry points.
- `application/models/`: Data access layer and database operations.
- `application/views/`: Frontend view templates (Bootstrap / Matrix Admin based).
- `application/libraries/`: Custom libraries, REST controllers, and payment gateway integrations.
- `application/config/`: Configuration files (`config.php`, `database.php`, `routes.php`, etc.).
- `application/database/migrations/`: Database schema migration files.
- `docker/`: Docker Compose configuration for local development.

## Coding Standards & Guidelines

1. **Code Formatting:**
   - Always format PHP code using project standards: `composer format` (which invokes `application/vendor/bin/php-cs-fixer fix`).
2. **Security & Input/Output Handling:**
   - Always validate user input.
   - Always escape output in views using `html_escape()`.
   - Never use raw SQL string concatenation; use CodeIgniter Query Builder or query bindings (`?` or `$this->db->where()`) to prevent SQL injection.
   - Never expose sensitive data (e.g., password hashes) in public models or API responses.
3. **Database Changes:**
   - Schema modifications must be implemented via migrations (`application/database/migrations/`), never by editing `banco.sql` directly.
4. **Commit Messages:**
   - Follow [Conventional Commits](https://www.conventionalcommits.org/): `feat`, `fix`, `docs`, `refactor`, `chore`, etc.

## Security & Integrity Mandates

- **Secrets:** Never log, print, or commit `.env` files, credentials, API keys, or database dumps.
- **Vulnerabilities:** Security issues must be handled following `SECURITY.md` (report privately to `contato@mapos.com.br`).
