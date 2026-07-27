# Docker

`docker-compose.yml` levanta tres servicios de aplicación:

- `frontend`: Nginx, único puerto publicado.
- `backend`: PHP-FPM con Laravel, GD y configuración endurecida.
- `db`: MySQL 8.4 en red interna, sin puerto del host.

phpMyAdmin es una herramienta administrativa local y optativa. No participa en
el despliegue normal, no acepta servidores arbitrarios, no permite iniciar sesión
como `root` y publica su puerto exclusivamente en `127.0.0.1`.

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

Para administrar la base de datos local:

```powershell
docker compose --profile admin-tools up -d phpmyadmin
Start-Process http://127.0.0.1:8083
docker compose --profile admin-tools stop phpmyadmin
```

Inicia sesión con el usuario `appventurers` y el valor local de `DB_PASSWORD`.
No copies ese valor al repositorio ni lo pases como argumento en la terminal.

Los health checks de MySQL, PHP-FPM y Laravel controlan el orden de arranque. El
contenedor backend se detiene si faltan claves, pepper, host permitido, HTTPS seguro
en producción, usuario administrador o permisos de almacenamiento.
