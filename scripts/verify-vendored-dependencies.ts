import { readFile } from "node:fs/promises";
import { resolve } from "node:path";

const root = resolve(import.meta.dirname, "..");
const filePairs: ReadonlyArray<{
    installedPath: string;
    vendoredPath: string;
    normalizeLineEndings?: boolean;
}> = [
    {
        installedPath: "node_modules/big-integer/BigInteger.min.js",
        vendoredPath: "laravel-docker/public/admin/third-party/big-integer/BigInteger.min.js",
    },
    {
        installedPath: "node_modules/big-integer/LICENSE",
        vendoredPath: "laravel-docker/public/admin/third-party/big-integer/LICENSE",
        normalizeLineEndings: true,
    },
];

for (const { installedPath, vendoredPath, normalizeLineEndings } of filePairs) {
    const [installed, vendored] = await Promise.all([
        readFile(resolve(root, installedPath)),
        readFile(resolve(root, vendoredPath)),
    ]);

    const matches = normalizeLineEndings
        ? installed.toString("utf8").replace(/\r\n/g, "\n")
            === vendored.toString("utf8").replace(/\r\n/g, "\n")
        : installed.equals(vendored);

    if (!matches) {
        throw new Error(`La copia vendorizada no coincide con ${installedPath}.`);
    }
}

console.log("[vendor-check] OK: big-integer 1.6.36 coincide con la copia pública fijada.");
