import { spawnSync } from "node:child_process";

const BLOCKED_PATH_PATTERNS = [
  /(^|\/)\.env$/i,
  /(^|\/)\.env\.(local|production|development|staging)$/i,
  /(^|\/)pepper[^/]*\.php$/i,
  /(^|\/)virustotal_api_key\.php$/i,
  /(^|\/)admin\/js\/helpers\/api_key\.(js|ts)$/i,
];

const SECRET_PATTERNS = [
  { name: "generic secret assignment", regex: /(api[_-]?key|secret|token|password)\s*[:=]\s*["'][^"'\n]{8,}["']/i },
  { name: "github token", regex: /\bgh[pousr]_[A-Za-z0-9_]{20,}\b/ },
  { name: "aws access key id", regex: /\bAKIA[0-9A-Z]{16}\b/ },
  { name: "private key block", regex: /-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----/ },
  { name: "bearer token", regex: /\bBearer\s+[A-Za-z0-9._-]{20,}\b/i },
];

function runGit(args) {
  const result = spawnSync("git", args, { encoding: "utf8" });
  if (result.status !== 0) {
    const stderr = (result.stderr || "").trim();
    throw new Error(`git ${args.join(" ")} failed${stderr ? `: ${stderr}` : ""}`);
  }
  return result.stdout || "";
}

function fail(message) {
  console.error(`\n[secret-scan] BLOCKED: ${message}`);
  process.exit(1);
}

let stagedRaw = "";
try {
  stagedRaw = runGit(["diff", "--cached", "--name-only", "--diff-filter=ACMR"]);
} catch (error) {
  fail(error.message);
}

const stagedFiles = stagedRaw
  .split(/\r?\n/)
  .map(v => v.trim())
  .filter(Boolean);

if (stagedFiles.length === 0) {
  process.exit(0);
}

for (const filePath of stagedFiles) {
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

  for (const rule of SECRET_PATTERNS) {
    if (rule.regex.test(content)) {
      fail(`possible ${rule.name} in staged file: ${filePath}`);
    }
  }
}

console.log("[secret-scan] OK: no blocked paths or obvious secrets detected in staged files.");
