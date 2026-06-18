# Limpieza de historial sensible

No he ejecutado `git filter-repo` automáticamente porque este repositorio ya tenía cambios locales sin confirmar y un rewrite de historial en ese estado sería arriesgado.

## Rutas a purgar del historial

Estas son las rutas que conviene eliminar del histórico remoto antes de volver a publicar:

```text
bfg-1.15.0.jar
dbs13217995.sql
virustotal_api_key.php
pepper.php
pepper2.php
.env
```

## Procedimiento recomendado

1. Haz una copia de seguridad local o un clon espejo.
2. Asegúrate de trabajar con el árbol limpio.
3. Ejecuta:

```bash
git filter-repo --force ^
  --invert-paths ^
  --path bfg-1.15.0.jar ^
  --path dbs13217995.sql ^
  --path virustotal_api_key.php ^
  --path pepper.php ^
  --path pepper2.php ^
  --path .env
```

4. Revisa el resultado:

```bash
git log --stat -- .env virustotal_api_key.php pepper.php pepper2.php dbs13217995.sql bfg-1.15.0.jar
```

5. Fuerza la publicación del historial limpio:

```bash
git push origin --force --all
git push origin --force --tags
```

## Después del rewrite

- Revoca y rota cualquier secreto que haya estado en el historial.
- Vuelve a configurar `APP_KEY`, `PASSWORD_PEPPER_CURRENT`, `PRACEAR_ADMIN_PASSWORD` y `VIRUSTOTAL_API_KEY` solo como variables del entorno.
- No vuelvas a versionar dumps SQL reales ni utilidades binarias temporales.
