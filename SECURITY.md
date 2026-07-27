# Seguridad

La defensa es por capas y toma como referencia OWASP Top 10 2025.

- Control de acceso: middleware de administración, sesiones cifradas, tiempo máximo
  absoluto y por inactividad, rotación de identificador y cierre solo por POST.
- Configuración segura: validación de `Host`, proxies explícitos, CSP con nonce,
  HSTS en HTTPS, cabeceras anti-embedding/MIME, errores sin detalles y comprobación
  obligatoria de configuración antes del arranque.
- Cadena de suministro: Laravel y dependencias soportadas, lockfile, `composer audit`,
  `npm audit`, imágenes versionadas y build que copia solo la aplicación canónica.
- Criptografía: Argon2id sobre HMAC-SHA-384 con pepper de al menos 32 caracteres.
  `PASSWORD_PEPPER_PREVIOUS` permite una rotación gradual sin duplicar ficheros.
- Inyección e integridad: Query Builder parametrizado, salida Blade escapada, CSP,
  validación de longitudes y formatos, y respuestas JSON generadas por Laravel.
- Diseño seguro: privilegio mínimo, base de datos sin puerto público, límites de
  cuerpo, rate limiting por IP/cuenta/operación y operaciones sensibles separadas.
- Autenticación: mensajes genéricos, verificación de coste constante para usuarios
  inexistentes, CAPTCHA, política de contraseña, historial y rehash automático.
- Integridad de datos: imágenes JPEG se validan por MIME y dimensiones, se analizan
  opcionalmente con VirusTotal y se decodifican/recodifican para eliminar metadatos
  y contenido añadido.
- Registro: accesos y cambios sensibles quedan auditados sin registrar contraseñas,
  pepper, tokens ni mensajes internos de excepción en producción.
- Condiciones excepcionales: transacciones, fallo cerrado en análisis obligatorio,
  health checks y arranque detenido ante configuración insegura.

## Secretos

Solo existe `laravel-docker/config/pepper.php` y nunca contiene el valor secreto.
Los valores reales se inyectan mediante variables de entorno:

- `APP_KEY`
- `DB_PASSWORD` y `MYSQL_ROOT_PASSWORD`
- `PASSWORD_PEPPER`
- `VIRUSTOTAL_API_KEY`, si se habilita el análisis
- `PRACEAR_BOOTSTRAP_ADMIN_PASSWORD`, solo durante el primer alta

Aunque el historial se sanee, cualquier secreto que hubiera estado publicado debe
revocarse y rotarse: reescribir Git no invalida credenciales copiadas previamente.

## Prevención en futuras actualizaciones

- `.gitignore` y `.dockerignore` excluyen secretos, dumps, claves y datos subidos.
- `npm install` configura un `pre-commit` que revisa rutas prohibidas, firmas de
  credenciales, CSS embebido y la implementación única.
- GitHub Actions ejecuta Gitleaks 8.30.1 sobre todo el historial de cada push/PR,
  además de PHPUnit y las reglas del repositorio.
- El workflow usa permisos de solo lectura, checkout sin credenciales persistentes,
  acciones fijadas por SHA y un binario Gitleaks validado por checksum.

## Notificación

No abras incidencias públicas con credenciales o datos personales. Comunica el
problema de forma privada al responsable del repositorio e incluye impacto,
reproducción mínima y versión afectada.
