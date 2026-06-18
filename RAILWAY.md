# Despliegue en Railway

Este repositorio queda preparado para desplegar la app principal y un gestor web de base de datos separado.

## Arquitectura recomendada

Railway no ejecuta `docker-compose.yml` directamente; cada servicio del Compose se traduce a un servicio independiente dentro del proyecto de Railway.

Servicios recomendados:

1. `appventurers-web`
   - Repositorio GitHub de este proyecto.
   - Usa `railway.toml`, que apunta a `railway/app/Dockerfile`.
2. `mysql`
   - Base de datos gestionada MySQL de Railway.
3. `adminer`
   - Servicio aparte desde este mismo repo.
   - Configura `RAILWAY_DOCKERFILE_PATH=railway/adminer/Dockerfile`.

## Variables de la app

En `appventurers-web`, define como mínimo:

```env
APP_KEY=base64:...
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.railway.app

DB_CONNECTION=mysql
DATABASE_URL=${{MySQL.MYSQL_URL}}

PASSWORD_PEPPER_CURRENT=usa-un-pepper-largo-y-unico
PASSWORD_PEPPER_LEGACY_JSON=[]

PRACEAR_ADMIN_LOGIN=admin
PRACEAR_ADMIN_PASSWORD=elige-una-contraseña-larga-y-unica

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
PRACEAR_ASSETS_PATH=/var/www/html/laravel-docker/public/assets

VIRUSTOTAL_API_KEY=
```

Notas:

- Si prefieres no usar `DATABASE_URL`, puedes mapear `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` a las variables expuestas por MySQL.
- El contenedor ejecuta automáticamente `php artisan migrate --force` y `php artisan pracear:bootstrap --force` en cada arranque.
- Las imágenes subidas por administración se guardan en `public/assets`.

## Volumen persistente para imágenes

Adjunta un Volume al servicio `appventurers-web` y móntalo en:

```text
/var/www/html/laravel-docker/public/assets
```

Sin ese volumen, las imágenes subidas desde el panel se perderán al redeplegar.

## Adminer

Para `adminer`, define estas variables:

```env
ADMINER_DEFAULT_SERVER=${{MySQL.MYSQLHOST}}
ADMINER_DESIGN=pepa-linha-dark
```

Puedes conectarte usando:

- Servidor: el `MYSQLHOST` del servicio MySQL
- Usuario: `MYSQLUSER`
- Contraseña: `MYSQLPASSWORD`
- Base de datos: `MYSQLDATABASE`

## Flujo sugerido

1. Crear un proyecto vacío en Railway.
2. Añadir la base de datos MySQL gestionada.
3. Añadir el servicio `appventurers-web` desde GitHub.
4. Configurar las variables anteriores.
5. Adjuntar el volumen persistente para `public/assets`.
6. Añadir un segundo servicio `adminer` con `railway/adminer/Dockerfile`.
7. Verificar `/up` y luego el login.

## Estado de la base de datos

La aplicación ya no depende de un dump SQL versionado. Ahora usa migraciones específicas para:

- `naves`
- `usuarios`
- `puestos`
- `puestos_traducciones`
- `old_passwords`
- `accesos`

Y se han eliminado las migraciones Laravel genéricas que no aportaban valor a este proyecto:

- `users`
- `cache`
- `jobs`
