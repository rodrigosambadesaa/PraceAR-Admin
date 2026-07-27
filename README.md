# PraceAR Admin

Aplicación administrativa unificada. La única implementación ejecutable vive en
`laravel-docker/`; Docker, Railway y las pruebas usan exactamente ese mismo código.

## Arranque local

Requisitos: Docker Desktop y Node.js 20 o posterior.

```powershell
npm run env:init
docker compose up --build -d
```

La web queda disponible en `http://localhost:8081`. Si el puerto está ocupado:

```powershell
$env:FRONTEND_PORT = "8082"
docker compose up --build -d
```

`env:init` crea `.env` con secretos aleatorios y permisos restrictivos. Ese archivo,
los volcados SQL, certificados, claves y ficheros de pepper están ignorados por Git.
No copies valores reales a archivos versionados.

## Verificación

```powershell
npm run verify
cd laravel-docker
php vendor/bin/phpunit
cd ..
docker compose exec -T backend php artisan pracear:security-check
docker compose exec -T backend composer audit
```

El panel usa un volumen persistente para las imágenes normalizadas. La base de datos
no publica puertos al host y no se incluye una interfaz web de administración.

Consulta `SECURITY.md` para el modelo de seguridad y `RAILWAY.md` para producción.
