<?php

declare(strict_types=1);

/**
 * Build and emit baseline security headers for all HTML responses.
 * Keeps compatibility with current inline scripts/styles while adding hardening.
 */
function apply_security_headers(array $securityConfig): void
{
    if (headers_sent()) {
        return;
    }

    $headersConfig = $securityConfig["headers"] ?? [];
    if (($headersConfig["enabled"] ?? true) !== true) {
        return;
    }

    $cspDirectives = $headersConfig["content_security_policy"] ?? [];
    $csp = build_content_security_policy($cspDirectives);
    if ($csp !== "") {
        header("Content-Security-Policy: " . $csp);
    }

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Permitted-Cross-Domain-Policies: none");
    header("Cross-Origin-Resource-Policy: same-origin");
    header("Referrer-Policy: " . ($headersConfig["referrer_policy"] ?? "strict-origin-when-cross-origin"));
    header("Permissions-Policy: " . ($headersConfig["permissions_policy"] ?? "geolocation=(), microphone=(), camera=(), payment=(), usb=()"));

    // HSTS solo cuando la conexión es HTTPS.
    if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

function build_content_security_policy(array $directives): string
{
    $parts = [];

    foreach ($directives as $directive => $sources) {
        if (!is_string($directive) || $directive === "") {
            continue;
        }

        if (!is_array($sources) || $sources === []) {
            $parts[] = $directive;
            continue;
        }

        $cleanSources = [];
        foreach ($sources as $source) {
            if (!is_string($source) || $source === "") {
                continue;
            }

            $cleanSources[] = $source;
        }

        if ($cleanSources === []) {
            $parts[] = $directive;
            continue;
        }

        $parts[] = $directive . " " . implode(" ", $cleanSources);
    }

    return implode("; ", $parts);
}
