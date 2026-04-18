import fs from 'node:fs';
import { spawnSync } from 'node:child_process';
import pg from 'pg';

function readEnvFile(path) {
    if (!fs.existsSync(path)) {
        return {};
    }

    const content = fs.readFileSync(path, 'utf8');
    const env = {};
    for (const rawLine of content.split(/\r?\n/)) {
        const line = rawLine.trim();
        if (!line || line.startsWith('#')) {
            continue;
        }

        const eqIndex = line.indexOf('=');
        if (eqIndex < 1) {
            continue;
        }

        const key = line.slice(0, eqIndex).trim();
        let value = line.slice(eqIndex + 1).trim();
        if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
            value = value.slice(1, -1);
        }
        env[key] = value;
    }

    return env;
}

const envLatest = readEnvFile('.env.vercel.production.latest');
const envProd = readEnvFile('.env.vercel.production');
const databaseUrl = process.env.DATABASE_URL || envLatest.DATABASE_URL || envLatest.DB_URL || envProd.DATABASE_URL || envProd.DB_URL;

if (!databaseUrl) {
    throw new Error('No se encontro DATABASE_URL/DB_URL en .env.vercel.production.latest ni .env.vercel.production');
}

const client = new pg.Client({
    connectionString: databaseUrl,
    ssl: { rejectUnauthorized: false },
});

await client.connect();

const usersResult = await client.query('SELECT id, login FROM usuarios ORDER BY id');
const users = usersResult.rows;

if (users.length === 0) {
    console.log(JSON.stringify({ updated: [], message: 'No hay usuarios' }, null, 2));
    await client.end();
    process.exit(0);
}

const updated = [];

for (const user of users) {
    const id = Number(user.id);
    const login = String(user.login);
    const sanitizedLogin = login.replace(/[^A-Za-z0-9]/g, '');
    const plainPassword = `PraceAR_${sanitizedLogin}_${id}!2026`;

    const hashProc = spawnSync('php', ['scripts/hash_password_with_pepper.php', plainPassword], {
        cwd: process.cwd(),
        encoding: 'utf8',
    });

    if (hashProc.status !== 0) {
        throw new Error(`No se pudo hashear password para ${login}: ${hashProc.stderr || hashProc.stdout}`);
    }

    const passwordHash = hashProc.stdout.trim();
    if (!passwordHash.startsWith('$argon2id$')) {
        throw new Error(`Hash inesperado para ${login}: ${passwordHash}`);
    }

    await client.query('UPDATE usuarios SET password = $1 WHERE id = $2', [passwordHash, id]);

    updated.push({
        id,
        login,
        plain_password: plainPassword,
        password_hash: passwordHash,
    });
}

await client.end();

console.log(JSON.stringify({ updated }, null, 2));
