import fs from "node:fs";
import path from "node:path";

const root = path.resolve(import.meta.dirname, "..");
const mappings: ReadonlyArray<{ view: string; css: string; href: string }> = [
    {
        view: "laravel-docker/resources/views/auth/login.blade.php",
        css: "laravel-docker/public/css/login_shell.css",
        href: "{{ rtrim($baseUrl, '/') . '/css/login_shell.css' }}",
    },
    {
        view: "laravel-docker/resources/views/admin/index.blade.php",
        css: "laravel-docker/public/admin/css/index_modal.css",
        href: "{{ rtrim($baseUrl, '/') . '/admin/css/index_modal.css' }}",
    },
];

for (const mapping of mappings) {
    const viewPath = path.join(root, mapping.view);
    const cssPath = path.join(root, mapping.css);
    const view = fs.readFileSync(viewPath, "utf8");
    const match = view.match(/<style>\s*([\s\S]*?)\s*<\/style>/);

    if (!match) {
        continue;
    }

    fs.mkdirSync(path.dirname(cssPath), { recursive: true });
    fs.writeFileSync(cssPath, `${match[1]!.trim()}\n`, "utf8");

    const link = `<link rel="stylesheet" href="${mapping.href}">`;
    fs.writeFileSync(viewPath, view.replace(match[0], link), "utf8");
}

const viewRoot = path.join(root, "laravel-docker/resources/views");
const violations: string[] = [];

function visit(directory: string): void {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        const target = path.join(directory, entry.name);
        if (entry.isDirectory()) {
            visit(target);
        } else if (entry.name.endsWith(".blade.php")) {
            const content = fs.readFileSync(target, "utf8");
            for (const match of content.matchAll(/<style\b[^>]*>([\s\S]*?)<\/style>/gi)) {
                const lines = match[1]!.split(/\r?\n/).filter((line) => line.trim() !== "").length;
                if (lines > 5) {
                    violations.push(`${path.relative(root, target)}: bloque <style> de ${lines} líneas`);
                }
            }
        }
    }
}

visit(viewRoot);

if (violations.length > 0) {
    process.stderr.write(`${violations.join("\n")}\n`);
    process.exitCode = 1;
}
