# Saneamiento del historial

El repositorio se audita con Gitleaks antes y después de `git filter-repo`. La
reescritura elimina de todos los commits los `.env`, volcados SQL, claves heredadas,
binarios de limpieza y las implementaciones retiradas, incluyendo los antiguos
`pepper.php` y `pepper2.php` de la raíz.

La implementación vigente conserva solo `laravel-docker/config/pepper.php`, que no
almacena secretos. Tras una reescritura se deben forzar todas las ramas y etiquetas,
pedir a cada colaborador un clon nuevo y rotar todas las credenciales que alguna vez
hayan estado publicadas.

Comprobaciones reproducibles:

```powershell
gitleaks git . --redact
git log --all -- .env pepper.php pepper2.php virustotal_api_key.php
npm run verify
```
