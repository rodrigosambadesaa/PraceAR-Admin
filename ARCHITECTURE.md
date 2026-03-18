# Arquitectura objetivo (refactor incremental)

Este proyecto ya usa una base legada con ficheros PHP que mezclan lógica de negocio, acceso a datos y renderizado HTML.

## Base introducida en esta refactorización

- `app/Application.php`: punto de orquestación de la app.
- `app/Core/Bootstrap.php`: inicialización transversal (entorno, headers, sesión, constantes y conexión).
- `app/Core/Router.php`: enrutador simple por `page`.
- `app/Http/Request.php`: encapsula lectura de `$_GET`, `$_REQUEST`, `$_SERVER`.
- `app/Controller/LegacyAdminController.php`: adaptador a páginas legacy del panel admin.
- `app/Controller/LegacyAuthController.php`: adaptador de login legacy.
- `index.php`: front controller mínimo.

## Objetivo de migración por fases

1. Mantener compatibilidad completa con rutas actuales.
2. Migrar cada pantalla legacy a patrón Controller + Service + View.
3. Sustituir includes directos por renderizado explícito de vistas.
4. Introducir repositorios para consultas SQL y aislar mysqli del controlador.
5. Unificar validaciones y respuestas HTTP en capas comunes.

## Siguiente fase recomendada

- Migrar `login.php` a:
  - `app/Controller/AuthController.php`
  - `app/Service/AuthService.php`
  - `app/View/auth/login.php`
- Migrar `admin/index.php` a:
  - `app/Controller/Admin/StallController.php`
  - `app/Repository/StallRepository.php`
  - `app/View/admin/stalls/index.php`
