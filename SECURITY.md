# Security Policy - AppVenturers

## Alcance

Este documento describe la postura de seguridad actual del proyecto y el mínimo de cobertura aplicado respecto a OWASP Top 10 (2021).

## Controles implementados

- Gestión de sesión endurecida:
  - Cookies `HttpOnly`, `SameSite=Strict` y `Secure` cuando hay HTTPS.
  - Regeneración de sesión tras login (`session_regenerate_id(true)`).
- Autenticación:
  - Hash de contraseñas con `Argon2id` + `pepper`.
  - Mensaje de error genérico en login para evitar enumeración de usuarios.
  - Rate limiting por IP/cuenta y captcha en login.
- Protección CSRF:
  - Token CSRF en formularios críticos y validación en servidor.
- Mitigación de inyección:
  - Uso de sentencias preparadas para consultas SQL.
  - Validación/sanitización de entradas en helpers.
- Endurecimiento HTTP:
  - `Content-Security-Policy`.
  - `X-Content-Type-Options: nosniff`.
  - `X-Frame-Options: SAMEORIGIN`.
  - `Referrer-Policy: strict-origin-when-cross-origin`.
  - `Permissions-Policy` restrictiva.
  - `Cross-Origin-Resource-Policy: same-origin`.
  - `Strict-Transport-Security` cuando la conexión es HTTPS.
- Mitigación de Host Header Injection:
  - Normalización de `HTTP_HOST` y lista blanca opcional `APP_ALLOWED_HOSTS`.
- Manejo seguro de errores:
  - En producción (`APP_ENV=production`) no se muestran errores detallados.
- Carga de archivos:
  - Validaciones de tipo y verificación de ficheros maliciosos vía integración externa.
- Logging básico de eventos de seguridad:
  - Intentos fallidos de login y eventos de control de velocidad.

## Mapeo OWASP Top 10 (2021)

### A01: Broken Access Control

- Control de acceso por sesión para rutas de administración.
- Validación de sesión en flujo principal.

### A02: Cryptographic Failures

- Contraseñas con `Argon2id` + `pepper`.
- Cookies de sesión endurecidas.
- HSTS en HTTPS.

### A03: Injection

- Sentencias preparadas (`mysqli->prepare`) en operaciones de base de datos.
- Validación y normalización de entrada.

### A04: Insecure Design

- Defensa en profundidad en autenticación: captcha + rate limiting + CSRF.
- Límites de tamaño de petición en login.

### A05: Security Misconfiguration

- Cabeceras de seguridad centralizadas.
- Errores ocultos en producción.
- Bloqueo de acceso a archivos sensibles mediante configuración web.

### A06: Vulnerable and Outdated Components

- Dependencias de frontend gestionadas por `npm`.
- Recomendación operativa: ejecutar auditorías periódicas (`npm audit`) en CI/CD.

### A07: Identification and Authentication Failures

- Política de contraseñas robustas.
- Argon2id + pepper.
- Mensajes de login genéricos para evitar enumeración.
- Regeneración de sesión post-login.

### A08: Software and Data Integrity Failures

- Validaciones server-side antes de persistir datos sensibles.
- Recomendación operativa: firma/validación de artefactos en pipeline de despliegue.

### A09: Security Logging and Monitoring Failures

- Registro de fallos de autenticación y eventos de rate limit.
- Recomendación operativa: centralizar logs y alertado en entorno productivo.

### A10: Server-Side Request Forgery (SSRF)

- No se aceptan URLs arbitrarias de usuario para fetch de recursos internos.
- Integración externa acotada a endpoints conocidos en funcionalidades concretas.

## Configuración mínima recomendada

En `.env` de producción:

```env
APP_ENV="production"
APP_BASE_URL="https://tu-dominio/"
APP_ALLOWED_HOSTS="tu-dominio,www.tu-dominio"
```

Y mantener fuera de repositorio:

- `pepper2.php`
- `virustotal_api_key.php`

## Vulnerability Disclosure

Si detectas una vulnerabilidad, no la publiques en abierto de inmediato. Envíala por canal privado al mantenedor del proyecto con:

- Descripción
- Impacto
- Pasos para reproducir
- Prueba de concepto mínima
- Propuesta de remediación

## English Summary

This project implements a baseline OWASP Top 10 coverage with hardening controls: secure sessions, Argon2id + pepper, CSRF, rate limiting, captcha, generic auth errors, SQL prepared statements, security headers (including CSP), host header validation, and production-safe error handling. Additional operational controls (CI dependency scanning, centralized logging, artifact integrity checks) are recommended for production environments.
