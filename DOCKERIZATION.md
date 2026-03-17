# Dockerización de PraceAR

Fecha: 2026-03-17

## Objetivo

Ejecutar la aplicación completa con un único comando, separando frontend y backend.

## Arquitectura

La dockerización se compone de 3 servicios en `docker-compose.yml`:

- `frontend`:
  - Imagen `nginx:1.27-alpine`
  - Expone el puerto configurable `${FRONTEND_PORT:-8081}`
  - Sirve estáticos y reenvía peticiones PHP a `backend:9000`
  - Configuración en `docker/frontend/default.conf`
- `backend`:
  - Imagen propia desde `docker/backend/Dockerfile`
  - Base `php:8.2-fpm-alpine`
  - Extensiones instaladas: `mysqli`, `pdo`, `pdo_mysql`
  - Carga ajustes de PHP desde `docker/backend/php.ini`
- `db`:
  - Imagen `mysql:8.0`
  - Inicializa base de datos desde `dbs13217995.sql`
  - Persistencia en volumen `db_data`

## Comando único

```bash
docker compose up --build -d
```

Acceso por defecto:

- `http://localhost:8081`

Parada del entorno:

```bash
docker compose down
```

## Variables relevantes

Definidas/consumidas en `docker-compose.yml` y en la app:

- `FRONTEND_PORT` (por defecto `8081`)
- `APP_ENV=development`
- `APP_BASE_URL=http://localhost:${FRONTEND_PORT:-8081}/`
- `PRACEAR_DB_HOST=db`
- `PRACEAR_DB_USER=appventurers`
- `PRACEAR_DB_PASSWORD=appventurers`
- `PRACEAR_DB_NAME=dbs13217995`

## Ajustes realizados en la app para Docker

En `constants.php`:

- Soporte para `APP_BASE_URL` para construir URLs correctas fuera de XAMPP.
- Fallback de variables de base de datos en entorno local:
  - Si no existen `*_LOCAL`, utiliza `PRACEAR_DB_*`.

Esto evita errores de rutas y conexión al ejecutar en contenedores.

## Resolución de problemas

- Puerto ocupado:
  - Ejecutar con otro puerto, por ejemplo:
    - PowerShell: `$env:FRONTEND_PORT=8090; docker compose up --build -d`
- Ver logs:
  - `docker compose logs -f frontend`
  - `docker compose logs -f backend`
  - `docker compose logs -f db`
- Reinicio limpio:
  - `docker compose down`
  - `docker compose up --build -d`
