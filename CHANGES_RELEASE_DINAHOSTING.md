# Cambios de la rama release/dinahosting-sin-docker

Fecha: 2026-03-17

## Objetivo

Preparar una variante del proyecto orientada a despliegue real en hosting tradicional (PHP + MySQL), sin contenedores.

## Cambios principales

- Eliminados archivos y configuración de Docker del versionado:
  - `.dockerignore`
  - `docker-compose.yml`
  - `docker/backend/Dockerfile`
  - `docker/backend/php.ini`
  - `docker/frontend/default.conf`
- Ajustada la documentación general en `README.md` para flujo sin Docker.
- Añadida guía específica de despliegue en Dinahosting:
  - `DEPLOYMENT_DINAHOSTING.md`
- Ampliada plantilla de entorno para producción y compatibilidad local/prod:
  - `.env.example`

## Notas de despliegue

- Esta rama asume servidor web tradicional (Apache/Nginx con PHP-FPM) y base de datos gestionada por el hosting.
- Variables críticas en producción:
  - `APP_ENV`
  - `APP_BASE_URL`
  - `PRACEAR_DB_HOST`
  - `PRACEAR_DB_USER`
  - `PRACEAR_DB_PASSWORD`
  - `PRACEAR_DB_NAME`
- Secretos no versionados requeridos:
  - `pepper2.php`
  - `virustotal_api_key.php`

## Estado

Rama recomendada para despliegue en entorno real sin soporte Docker.
