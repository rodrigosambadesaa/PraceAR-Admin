import { readFile } from "node:fs/promises";
import { resolve } from "node:path";

const root = resolve(import.meta.dirname, "..");
const filePairs: ReadonlyArray<readonly [string, string]> = [
    [
        "node_modules/big-integer/BigInteger.min.js",
        "laravel-docker/public/admin/third-party/big-integer/BigInteger.min.js",
    ],
    [
        "node_modules/big-integer/LICENSE",
        "laravel-docker/public/admin/third-party/big-integer/LICENSE",
    ],
];

for (const [installedPath, vendoredPath] of filePairs) {
    const [installed, vendored] = await Promise.all([
        readFile(resolve(root, installedPath)),
        readFile(resolve(root, vendoredPath)),
    ]);

    if (!installed.equals(vendored)) {
        throw new Error(`La copia vendorizada no coincide con ${installedPath}.`);
    }
}

console.log("[vendor-check] OK: big-integer 1.6.36 coincide con la copia pública fijada.");
