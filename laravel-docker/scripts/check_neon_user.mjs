import fs from 'node:fs';
import pg from 'pg';

const match = fs.readFileSync('.env.vercel.production', 'utf8').match(/^DATABASE_URL="([^"]+)"$/m);
if (!match) {
    throw new Error('DATABASE_URL no encontrada');
}

const client = new pg.Client({
    connectionString: match[1],
    ssl: { rejectUnauthorized: false },
});

await client.connect();

const users = await client.query('SELECT id, login, password, length(password) AS password_length FROM usuarios ORDER BY id');
console.log('usuarios:', users.rows);

await client.end();
