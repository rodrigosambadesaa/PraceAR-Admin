# Transicion a Laravel

- Modo: no-docker
- Esta app Laravel convive temporalmente con el codigo PHP legado.
- Rutas de puente habilitadas:
	- /legacy -> index.php legado
	- /legacy/login -> login.php legado
	- /legacy/admin y /legacy/admin/{path} -> admin legado
- Siguiente paso recomendado: migrar autenticacion y panel admin por modulos sin romper estas rutas de compatibilidad.

## Arranque

- Copia .env.example a .env y ajusta base de datos.
- Configura LEGACY_BASE_URL segun tu entorno (ejemplo local: http://127.0.0.1).
- Ejecuta: php artisan key:generate
- Ejecuta: php artisan serve --host=127.0.0.1 --port=9000
