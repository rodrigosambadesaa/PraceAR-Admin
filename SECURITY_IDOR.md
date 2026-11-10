# Object-level access control (IDOR/BOLA)

A login session alone does **not** authorize arbitrary `puestos.id` values. The
administrative catalogue is scoped by the authenticated account, on the server,
for both modern /admin endpoints and legacy `/?page=...` aliases.

- `usuarios.es_superadmin=1`: full catalogue administration. Existing users
  are promoted during the authorization migration for backward compatibility;
  the initial bootstrap account is also explicitly a superadmin.
- `usuarios.es_superadmin=0`: access only to naves explicitly assigned in
  `usuario_naves(usuario_id,nave_id)`. **New users default to zero grants.**
- Direct edits, translations, quick-edit, previews, suggestions and lists all
  apply nave-scoped checks. Unauthorized record lookups answer 404 to avoid
  leaking record existence. Changing a stall's nave or parent must stay within
  the authorized scope. Server-side checks never trust hidden form inputs.
- The `/unity/*` endpoints intentionally expose **active public catalogue**
  records to the Unity client; those endpoints are not administrative object
  access. Review the public fields separately if they should be private.
  Static public catalogue images remain public by design.

## Provision a restricted account

After `php artisan migrate --force`, create an account with a secure Argon2id
password using the project's established pepper/password policies. For an
**existing** user account, the following SQL illustrates how an operator can
scope it to nave 1:

```sql
START TRANSACTION;
UPDATE usuarios SET es_superadmin = 0 WHERE login = 'scoped_editor';
INSERT INTO usuario_naves (usuario_id, nave_id)
SELECT id, 1 FROM usuarios WHERE login = 'scoped_editor';
COMMIT;
```

Verify the user and target nave before executing; configure multiple grants as
needed. Revoking a grant removes access at the next request, including when
the session remains active. A session cannot elevate its own rights.

## Regression tests

Run `cd laravel-docker && ./vendor/bin/phpunit --filter=InsecureDirectObjectReferenceTest`.
The CI workflow `idor-authorization.yml` executes the full PHPUnit suite.
Test cross-user access to both reads and writes, including the legacy root
dispatcher, translations, AJAX operations, suggestion/list enumeration, changing
the nave and invalid object IDs.
