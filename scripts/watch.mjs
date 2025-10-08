import { spawn } from "node:child_process";

const projects = ["tsconfig.json", "admin/tsconfig.json"];
const childProcesses = [];

function runWatcher(config) {
    const processArgs = ["node_modules/typescript/bin/tsc", "-p", config, "--watch"];
    const child = spawn(process.execPath, processArgs, { stdio: "inherit" });
    childProcesses.push(child);

    child.on("close", code => {
        if (code !== null && code !== 0) {
            console.error(`Watcher for ${config} exited with code ${code}`);
        }
    });
}

projects.forEach(runWatcher);

function handleTermination(signal) {
    console.log(`\nRecibida señal ${signal}. Deteniendo watchers...`);
    childProcesses.forEach(child => {
        child.kill("SIGTERM");
    });
    process.exit(0);
}

process.on("SIGINT", () => handleTermination("SIGINT"));
process.on("SIGTERM", () => handleTermination("SIGTERM"));
