# Docker

`docker-compose.yml` levanta tres servicios:

- `frontend`: Nginx, único puerto publicado.
- `backend`: PHP-FPM con Laravel, GD y configuración endurecida.
- `db`: MySQL 8.4 en red interna, sin puerto del host.

Los secretos se leen desde el `.env` local ignorado. El frontend sirve únicamente
`laravel-docker/public`; las rutas `.php` conocidas de Unity se envían al front
controller y cualquier otro script queda bloqueado.

Comandos habituales:

```powershell
npm run env:init
docker compose up --build -d
docker compose ps
docker compose logs --tail 100 backend
docker compose down
```

Los health checks de MySQL, PHP-FPM y Laravel controlan el orden de arranque. El
contenedor backend se detiene si faltan claves, pepper, host permitido, HTTPS seguro
en producción, usuario administrador o permisos de almacenamiento.
