# Transicion a Laravel

- Modo: docker
- Esta app Laravel convive temporalmente con el codigo PHP legado.
- Rutas de puente habilitadas:
  - /legacy -> index.php legado
  - /legacy/login -> login.php legado
  - /legacy/admin y /legacy/admin/{path} -> admin legado
- Siguiente paso recomendado: migrar autenticacion y panel admin por modulos sin romper estas rutas de compatibilidad.

## Arranque

- Copia .env.example a .env y ajusta base de datos.
- Configura LEGACY_BASE_URL segun tu entorno docker (por ejemplo host accesible desde contenedor).
- Ejecuta: php artisan key:generate
- Arranca con el flujo docker definido para este proyecto.
