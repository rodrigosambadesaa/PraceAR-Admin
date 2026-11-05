# Seguridad

La defensa es por capas y toma como referencia OWASP Top 10 2025.

- Control de acceso: middleware de administración y validación compartida en todas
  las rutas (incluidas las rutas heredadas / y /index.php), sesiones cifradas,
  tiempo máximo absoluto y por inactividad comprobado en el servidor,
  revocación de sesiones caducadas o de usuarios eliminados, rotación de
  identificador y cierre solo por POST.
- Configuración segura: validación de `Host`, proxies explícitos, CSP sin scripts
  inline,
  HSTS en HTTPS, cabeceras anti-embedding/MIME, errores sin detalles y comprobación
  obligatoria de configuración antes del arranque.
- Cadena de suministro: Laravel y dependencias soportadas, lockfile, `composer audit`,
  `npm audit`, imágenes versionadas y build que copia solo la aplicación canónica.
- Criptografía: Argon2id sobre HMAC-SHA-384 con pepper de al menos 32 caracteres.
  `PASSWORD_PEPPER_LEGACY_JSON` permite una rotación gradual sin duplicar ficheros.
- Inyección e integridad: Query Builder parametrizado, salida Blade escapada, CSP,
  validación de longitudes y formatos, y respuestas JSON generadas por Laravel.
- Diseño seguro: privilegio mínimo, base de datos sin puerto público, límites de
  cuerpo, rate limiting por IP/cuenta/operación y operaciones sensibles separadas.
- Administración de base de datos: phpMyAdmin es optativo, se fija por digest,
  escucha solo en `127.0.0.1`, restringe el servidor y la base permitidos,
  bloquea el acceso `root` y no conserva sesiones en un volumen persistente.
- Autenticación: mensajes genéricos, verificación de coste constante para usuarios
  inexistentes, CAPTCHA, política de contraseña, historial y rehash automático.
  Las contraseñas nuevas se comprueban en el servidor frente a filtraciones con
  k-anonymity; nunca se transmite la contraseña ni su hash completo.
- Validación sin JavaScript: búsquedas, acceso, edición de puestos, traducciones,
  imágenes y cambios de contraseña se vuelven a validar y autorizar en Laravel.
  TypeScript solo aporta mejoras de interfaz y no constituye una frontera de
  seguridad.
- Código cliente: todo el código propio se mantiene en TypeScript y se compila a
  JavaScript reproducible para el navegador. Las dependencias de terceros se fijan
  por versión, integridad y licencia.
- Integridad de datos: imágenes JPEG se validan por MIME y dimensiones usando una
  única copia de los bytes recibidos, se decodifican/recodifican para eliminar
  metadatos y contenido añadido. Es obligatorio el antivirus local en dos fases:
  escaneo de la copia privada de entrada ANTES de decodificar con GD y escaneo
  del JPEG normalizado ANTES de publicarlo. Además, VirusTotal analiza el JPEG
  normalizado cuando está habilitado (obligatorio si así está configurado). La imagen temporal se
  crea de forma exclusiva, con permisos privados; después del análisis se verifica
  su hash SHA-256 y su identidad (dispositivo/inodo) antes de publicarla mediante
  un renombrado atómico. Cualquier error conserva la imagen anterior y borra el
  archivo temporal. El directorio de imágenes debe ser de confianza y no permitir
  escrituras de usuarios o procesos no autorizados. No se puede desactivar
  el antivirus local: si no está instalado, faltan firmas o el resultado no es
  concluyente, se rechaza la subida. Windows utiliza Microsoft Defender mediante
  MpCmdRun con examen personalizado sin remediación; Linux/macOS requieren
  ClamAV (XProtect no dispone de una CLI documentada para análisis bajo demanda).
  Los antivirus reducen el riesgo pero no garantizan detección del 100 %.
  La comprobación manual del endpoint verify_malicious_photo.php también exige
  escaneo local previo, un snapshot privado del archivo y control de integridad
  antes de comunicar el resultado remoto.
- Catálogo inicial: el fixture versionado usa una lista blanca de campos públicos.
  Excluye contacto, teléfono, imagen, usuarios, hashes, accesos, IP y credenciales;
  solo se carga cuando la tabla de puestos está completamente vacía.
- Registro: accesos y cambios sensibles quedan auditados sin registrar contraseñas,
  pepper, tokens ni mensajes internos de excepción en producción.
- Condiciones excepcionales: transacciones, fallo cerrado en análisis obligatorio,
  health checks y arranque detenido ante configuración insegura.

## Secretos

Solo existe `laravel-docker/config/pepper.php` y nunca contiene el valor secreto.
Los valores reales se inyectan mediante variables de entorno:

- `APP_KEY`
- `DB_PASSWORD` y `MYSQL_ROOT_PASSWORD`
- `PASSWORD_PEPPER_CURRENT`
- `PASSWORD_PEPPER_LEGACY_JSON`, solo durante una rotación
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
