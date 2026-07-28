import { randomBytes } from "node:crypto";
import { existsSync, writeFileSync } from "node:fs";
import { resolve } from "node:path";

const target = resolve(import.meta.dirname, "..", ".env");
const force = process.argv.includes("--force");

if (existsSync(target) && !force) {
    process.stderr.write(".env already exists; use --force only if you intend to rotate the local environment.\n");
    process.exit(1);
}

const secret = (bytes = 32) => randomBytes(bytes).toString("base64url");
const adminPassword = `A!z9#${secret(24)}`;
const lines = [
    "FRONTEND_PORT=8081",
    `APP_KEY=base64:${randomBytes(32).toString("base64")}`,
    `DB_PASSWORD=${secret(32)}`,
    `MYSQL_ROOT_PASSWORD=${secret(32)}`,
    `PASSWORD_PEPPER_CURRENT=${secret(48)}`,
    "PRACEAR_ADMIN_LOGIN=admin",
    `PRACEAR_ADMIN_PASSWORD=${adminPassword}`,
    "",
];

writeFileSync(target, lines.join("\n"), { encoding: "utf8", mode: 0o600, flag: force ? "w" : "wx" });
process.stdout.write("Secure local .env created. The generated admin credential remains only in that ignored file.\n");
