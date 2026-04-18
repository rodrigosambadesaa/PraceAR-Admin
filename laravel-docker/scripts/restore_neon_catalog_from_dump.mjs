import fs from 'node:fs';
import path from 'node:path';
import pg from 'pg';

function parseEnv(filePath) {
    if (!fs.existsSync(filePath)) {
        return {};
    }

    const result = {};
    const content = fs.readFileSync(filePath, 'utf8');

    for (const rawLine of content.split(/\r?\n/)) {
        const line = rawLine.trim();
        if (!line || line.startsWith('#')) {
            continue;
        }

        const separator = line.indexOf('=');
        if (separator < 1) {
            continue;
        }

        const key = line.slice(0, separator).trim();
        let value = line.slice(separator + 1).trim();

        if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
            value = value.slice(1, -1);
        }

        result[key] = value;
    }

    return result;
}

function sqlNormalize(mysqlInsertSql) {
    return mysqlInsertSql
        .replace(/`/g, '')
        .replace(/\\'/g, "''")
        .replace(/\r\n/g, '\n');
}

function extractInsertStatements(dumpContent, tableName) {
    const pattern = new RegExp('INSERT INTO `' + tableName + '`[\\s\\S]*?;\\r?\\n\\r?\\n--', 'g');
    return Array.from(dumpContent.matchAll(pattern), (m) => {
        const raw = m[0].replace(/\r?\n\r?\n--$/, '');
        return sqlNormalize(raw);
    });
}

const envLatest = parseEnv('.env.vercel.production.latest');
const envProd = parseEnv('.env.vercel.production');

const databaseUrl =
    process.env.DATABASE_URL ||
    envLatest.DATABASE_URL ||
    envLatest.DB_URL ||
    envProd.DATABASE_URL ||
    envProd.DB_URL;

if (!databaseUrl) {
    throw new Error('No se encontro DATABASE_URL/DB_URL en variables de entorno ni en archivos .env.vercel.production*');
}

const dumpPath = path.resolve('..', 'dbs13217995.sql');
if (!fs.existsSync(dumpPath)) {
    throw new Error(`No existe el dump SQL esperado en ${dumpPath}`);
}

const dumpContent = fs.readFileSync(dumpPath, 'utf8');

const navesInserts = extractInsertStatements(dumpContent, 'naves');
const puestosInserts = extractInsertStatements(dumpContent, 'puestos');
const traduccionesInserts = extractInsertStatements(dumpContent, 'puestos_traducciones');

if (navesInserts.length === 0 || puestosInserts.length === 0 || traduccionesInserts.length === 0) {
    throw new Error('No se pudieron extraer inserts de naves/puestos/puestos_traducciones desde el dump SQL');
}

const client = new pg.Client({
    connectionString: databaseUrl,
    ssl: { rejectUnauthorized: false },
});

await client.connect();

try {
    await client.query('BEGIN');

    // El dump legacy usa 0/1 en `activo`; convertimos temporalmente a entero para importar sin errores.
    await client.query('ALTER TABLE puestos ALTER COLUMN activo DROP DEFAULT');
    await client.query('ALTER TABLE puestos ALTER COLUMN activo TYPE integer USING (CASE WHEN activo THEN 1 ELSE 0 END)');

    await client.query('TRUNCATE TABLE puestos_traducciones, puestos, naves RESTART IDENTITY CASCADE');

    for (const sql of navesInserts) {
        await client.query(sql);
    }

    for (const sql of puestosInserts) {
        await client.query(sql);
    }

    for (const sql of traduccionesInserts) {
        await client.query(sql);
    }

    await client.query("ALTER TABLE puestos ALTER COLUMN activo TYPE boolean USING (activo <> 0)");
    await client.query('ALTER TABLE puestos ALTER COLUMN activo SET DEFAULT true');

    await client.query(`
    SELECT setval(pg_get_serial_sequence('naves', 'id'), COALESCE((SELECT MAX(id) FROM naves), 1), true);
    SELECT setval(pg_get_serial_sequence('puestos', 'id'), COALESCE((SELECT MAX(id) FROM puestos), 1), true);
    SELECT setval(pg_get_serial_sequence('puestos_traducciones', 'id'), COALESCE((SELECT MAX(id) FROM puestos_traducciones), 1), true);
  `);

    await client.query('COMMIT');

    const counts = {};
    for (const table of ['naves', 'puestos', 'puestos_traducciones']) {
        const result = await client.query(`SELECT COUNT(*)::int AS count FROM ${table}`);
        counts[table] = result.rows[0].count;
    }

    console.log(JSON.stringify({ restored: true, counts }, null, 2));
} catch (error) {
    await client.query('ROLLBACK');
    throw error;
} finally {
    await client.end();
}
