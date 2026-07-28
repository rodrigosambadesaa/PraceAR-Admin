import { spawnSync } from "node:child_process";

const BLOCKED_PATH_PATTERNS = [
  /(^|\/)\.env(?:\..+)?$/i,
  /(^|\/)pepper\d*\.php$/i,
  /(^|\/)virustotal_api_key\.php$/i,
  /(^|\/)admin\/js\/helpers\/api_key\.(js|ts)$/i,
  /\.(sql|sqlite3?|dump|pem|key|p12|pfx|jks|keystore|kdbx)$/i,
  /(^|\/)(id_rsa|id_ed25519)(?:\.pub)?$/i,
  /(^|\/)(credentials|secrets?)\.(json|ya?ml|toml)$/i,
];

const SECRET_PATTERNS = [
  { name: "generic secret assignment", regex: /(api[_-]?key|secret|token|password)\s*[:=]\s*["'][^"'\n]{8,}["']/i },
  { name: "github token", regex: /\bgh[pousr]_[A-Za-z0-9_]{20,}\b/ },
  { name: "aws access key id", regex: /\bAKIA[0-9A-Z]{16}\b/ },
  { name: "private key block", regex: /-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----/ },
  { name: "bearer token", regex: /\bBearer\s+[A-Za-z0-9._-]{20,}\b/i },
  { name: "json web token", regex: /\beyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\b/ },
  { name: "credential in connection url", regex: /\b(?:mysql|postgres(?:ql)?|mongodb(?:\+srv)?):\/\/[^:\s/@]+:[^@\s/]{8,}@/i },
];

function runGit(args: string[]): string {
  const result = spawnSync("git", args, { encoding: "utf8" });
  if (result.status !== 0) {
    const stderr = (result.stderr || "").trim();
    throw new Error(`git ${args.join(" ")} failed${stderr ? `: ${stderr}` : ""}`);
  }
  return result.stdout || "";
}

function fail(message: string): never {
  console.error(`\n[secret-scan] BLOCKED: ${message}`);
  process.exit(1);
}

let stagedRaw = "";
try {
  stagedRaw = runGit(["diff", "--cached", "--name-only", "--diff-filter=ACMR"]);
} catch (error) {
  fail(error instanceof Error ? error.message : String(error));
}

const stagedFiles = stagedRaw
  .split(/\r?\n/)
  .map(v => v.trim())
  .filter(Boolean);

if (stagedFiles.length === 0) {
  process.exit(0);
}

for (const filePath of stagedFiles) {
  if (
    filePath === ".env.example"
    || filePath === "laravel-docker/.env.example"
    || filePath === "laravel-docker/config/pepper.php"
  ) {
    continue;
  }

  for (const pattern of BLOCKED_PATH_PATTERNS) {
    if (pattern.test(filePath)) {
      fail(`staged file matches blocked path policy: ${filePath}`);
    }
  }
}

for (const filePath of stagedFiles) {
  let content = "";
  try {
    content = runGit(["show", `:${filePath}`]);
  } catch {
    continue;
  }

  const scanContent = content
    .replace(/\$\{[^}\r\n]+\}/g, "<env>")
    .replace(/\b(?:change-me|example|placeholder|replace-me|your-[a-z0-9_-]+)\b/gi, "<x>");

  for (const rule of SECRET_PATTERNS) {
    if (rule.regex.test(scanContent)) {
      fail(`possible ${rule.name} in staged file: ${filePath}`);
    }
  }
}

console.log("[secret-scan] OK: no blocked paths or obvious secrets detected in staged files.");
