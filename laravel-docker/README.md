# PraceAR Laravel

Esta es la versión preparada para despliegue de PraceAR.

## Estado

- Autenticación y panel administrativo migrados a Laravel.
- Migraciones propias para `naves`, `usuarios`, `puestos`, `puestos_traducciones`, `old_passwords` y `accesos`.
- Soporte de secretos por variables de entorno:
  - `PASSWORD_PEPPER_CURRENT`
  - `PASSWORD_PEPPER_LEGACY_JSON`
  - `VIRUSTOTAL_API_KEY`
- Bootstrap automático del catálogo base y del usuario admin opcional:
  - `php artisan pracear:bootstrap --force`

## Despliegue

- Producción recomendada: Railway.
- Documentación: `../RAILWAY.md`

## Variables mínimas

```env
APP_KEY=base64:...
APP_URL=https://tu-app
DB_CONNECTION=mysql
DATABASE_URL=mysql://...
PASSWORD_PEPPER_CURRENT=...
PRACEAR_ADMIN_LOGIN=admin
PRACEAR_ADMIN_PASSWORD=...
```
