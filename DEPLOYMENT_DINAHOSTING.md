# Despliegue en Dinahosting (sin Docker)

Esta guía está orientada a hosting PHP tradicional.

## 1) Requisitos mínimos

- PHP 8.1 o superior (recomendado 8.2)
- MySQL/MariaDB
- Extensiones PHP: `mysqli`, `curl`, `mbstring`, `fileinfo`, `openssl`
- HTTPS activo en el dominio

## 2) Preparar el paquete de despliegue

En local:

```bash
npm ci
npm run build
```

Sube el proyecto al directorio público del dominio, excluyendo:

- `.git/`
- `node_modules/`
- `scripts/`
- `ts/`
- `admin/ts/`
- `*.md`

## 3) Configuración de entorno en servidor

Crea `.env` en la raíz del proyecto con valores reales:

```env
APP_ENV="production"
APP_BASE_URL="https://example.com/"

PRACEAR_DB_HOST="localhost"
PRACEAR_DB_USER="TU_USUARIO_BD"
PRACEAR_DB_PASSWORD="TU_PASSWORD_BD"
PRACEAR_DB_NAME="dbs13217995"
```

## 4) Secretos requeridos (no versionar)

Crea estos archivos en raíz del proyecto:

- `pepper2.php`
- `virustotal_api_key.php`

Ejemplo de `pepper2.php`:

```php
<?php
return [
  [
    "PASSWORD_PEPPER" => "TU_PEPPER_LARGO_Y_ALEATORIO",
    "last_used" => "9999-12-31",
  ],
];
```

Notas sobre pepper:

- Longitud recomendada: 32+ caracteres aleatorios.
- No usar espacios al principio/final.
- Guardarlo fuera de repositorio y respaldarlo de forma segura.

## 5) Base de datos

- En una migración desde versión anterior: hacer backup antes de actualizar.
- En instalación nueva: importar `dbs13217995.sql`.
- Verificar usuario administrador en tabla `usuarios`.

## 6) Apache y seguridad

- Mantener `.htaccess` en la raíz del proyecto.
- Verificar que está bloqueando acceso a `.env`, `pepper*.php`, claves API y `.sql`.
- Activar HTTPS forzado desde panel de hosting (recomendado).

Opcional para limitar tamaño del POST en login:

- Revisar y adaptar `config/apache/login_limit.conf` si tu plan permite incluir reglas extra.

## 7) Checklist de validación

- `https://example.com/` carga correctamente.
- Login funciona con usuario administrador.
- Cambio de contraseña funciona (Argon2id + pepper).
- Subida de imágenes funciona y VirusTotal responde.
- No hay errores críticos en logs de PHP.

## 8) Estrategia de actualización segura

1. Backup de ficheros y base de datos.
2. Subir nueva versión de código.
3. Ejecutar comprobaciones del checklist.
4. Si algo falla, rollback a backup anterior.
