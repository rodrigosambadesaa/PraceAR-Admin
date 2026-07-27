# Despliegue en Railway

Usa `railway/app/Dockerfile`. La imagen copia únicamente `laravel-docker/`, configura
Apache con `public/` como raíz, aplica el `php.ini` endurecido y ejecuta migraciones,
bootstrap idempotente y `app:security-check` antes de servir tráfico.

Variables obligatorias:

```text
APP_KEY=base64:...
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
PASSWORD_PEPPER=...
APP_ALLOWED_HOSTS=tu-dominio.example
```

Configura además `APP_ENV=production`, `APP_DEBUG=false` y
`SESSION_SECURE_COOKIE=true`. Railway aporta `PORT` y `RAILWAY_PUBLIC_DOMAIN`; el
entrypoint deriva la URL pública y confía en sus proxies. Si se exige análisis de
archivos, define `VIRUSTOTAL_REQUIRED=true` y `VIRUSTOTAL_API_KEY`.

Para el primer administrador se pueden proporcionar temporalmente
`PRACEAR_BOOTSTRAP_ADMIN_LOGIN` y `PRACEAR_BOOTSTRAP_ADMIN_PASSWORD`. Elimina la
contraseña del entorno después del primer arranque correcto.

No despliegues Adminer, no expongas la base de datos a Internet y monta almacenamiento
persistente en `PRACEAR_ASSETS_PATH` para las imágenes.
