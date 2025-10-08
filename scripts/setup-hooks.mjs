import { spawn } from "node:child_process";

const gitProcess = spawn("git", ["config", "core.hooksPath", ".githooks"], { stdio: "inherit" });

gitProcess.on("exit", code => {
    process.exit(code ?? 0);
});
