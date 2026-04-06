import { cpSync, chmodSync, existsSync, mkdirSync } from "node:fs";
import { join } from "node:path";
import { spawnSync } from "node:child_process";

const hooksDir = ".githooks";
const templatesDir = join("scripts", "hooks");

if (!existsSync(hooksDir)) {
    mkdirSync(hooksDir, { recursive: true });
}

const hookTemplates = [
    { source: join(templatesDir, "pre-commit.sh"), dest: join(hooksDir, "pre-commit") },
    { source: join(templatesDir, "post-merge.sh"), dest: join(hooksDir, "post-merge") },
];

for (const hook of hookTemplates) {
    cpSync(hook.source, hook.dest);
    try {
        chmodSync(hook.dest, 0o755);
    } catch {
        // On Windows this may fail depending on filesystem; git can still execute hooks.
    }
}

const gitConfig = spawnSync("git", ["config", "core.hooksPath", hooksDir], { stdio: "inherit" });
process.exit(gitConfig.status ?? 0);
