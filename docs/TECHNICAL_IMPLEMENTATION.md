# UbugeniPalace - Technical Implementation Details (Updated)

This document outlines the modern technical architecture and implementation details of the UbugeniPalace platform.

## 1. Core Architecture & Environment

### Dockerized Environment
The application is fully containerized using Docker, ensuring a consistent development and production environment.
- **Base Image:** `php:8.2-apache`
- **Extensions:** `pdo_mysql`, `pdo_pgsql` (for Supabase), `gd`, `zip`.
- **Orchestration:** `docker-compose.yml` manages the app service, volume mounts for real-time development, and environment variables.

### Database System (Supabase / PostgreSQL)
We transitioned from local MySQL to **Supabase (PostgreSQL)** for better scalability and cloud capabilities.
- **Connection Logic:** The `Database` class in `config/database.php` automatically detects if the host is Supabase and switches to the `pgsql` driver.
- **Stability:** Implemented connection error suppression to display user-friendly alerts instead of raw PHP errors when the database is unreachable.

## 2. Configuration & Security

### Robust Configuration (`config/config.php`)
- **Environment Variables:** Uses `phpdotenv` to load credentials from a `.env` file, keeping secrets out of the source code.
- **Constant Guarding:** All constants are wrapped in `defined()` checks to prevent "already defined" warnings during multiple inclusions.
- **Cloudinary Integration:** Native support for Cloudinary with a `USE_CLOUDINARY` toggle for easy failover to local assets.

### Security Implementation
- **Clean URLs:** Implemented via `.htaccess` to remove `.php` extensions, improving SEO and providing a more professional user experience.
- **Input Sanitization:** Global `sanitizeInput()` function used on all `$_GET` and `$_POST` data to prevent XSS.
- **Prepared Statements:** 100% usage of PDO prepared statements across the app to eliminate SQL injection risks.
- **Password Security:** Use of `password_hash()` with `PASSWORD_DEFAULT` (currently bcrypt).

## 3. Media & Asset Management

### Cloudinary Integration
Images are no longer served from the local disk but via **Cloudinary CDN**.
- **Helper Function:** `getImageUrl()` dynamically constructs URLs. If an image is a full URL (Cloudinary), it's used directly; otherwise, it falls back to local `/assets/` or `/uploads/` paths.
- **Optimization:** Automatic transformation and fast delivery through Cloudinary's global edge network.

## 4. Error Handling & UX

### Graceful Failure Handling
- **Custom 404/Error Page:** Any invalid URL or missing feature redirects to `pages/under-construction.php`.
- **Consistent Design:** The error page uses the project's primary design system, maintaining a professional look even when things go wrong.
- **Database Resilience:** Pagination and data fetching logic includes checks for boolean results (common on DB failure) to prevent "Trying to access array offset on value of type bool" warnings.

## 5. Directory Structure
```
UbugeniPalace/
├── config/             # DB & App settings (Supabase, Cloudinary)
├── docs/               # Technical & project documentation
├── includes/           # Reusable UI components (header, footer, nav)
├── pages/              # Main application views (clean URL targets)
├── api/                # Backend logic and JSON endpoints
├── assets/             # Local JS, CSS, and fallback images
├── uploads/            # Local storage fallback for user content
├── .htaccess           # URL rewriting & security rules
├── Dockerfile          # Container definition
└── docker-compose.yml  # Local dev orchestration
```

---
*Last Updated: March 2026*
