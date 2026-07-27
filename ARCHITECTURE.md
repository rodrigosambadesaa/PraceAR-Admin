# Arquitectura

Existe una sola aplicación Laravel en `laravel-docker/`.

- `laravel-docker/app`: controladores, middleware y servicios.
- `laravel-docker/config/pepper.php`: única configuración de pepper; solo lee el entorno.
- `laravel-docker/public`: CSS, JavaScript e imágenes públicas.
- `laravel-docker/resources/views`: plantillas Blade sin bloques CSS extensos.
- `docker`: PHP-FPM y proxy Nginx para desarrollo/servidor propio.
- `railway/app`: imagen Apache de la misma aplicación para Railway.

Las rutas históricas `.php` necesarias para Unity son adaptadores de rutas Laravel,
no una segunda implementación. Todas las escrituras administrativas requieren
sesión, CSRF, validación y límites de frecuencia.
