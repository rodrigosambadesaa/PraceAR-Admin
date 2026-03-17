<?php
declare(strict_types=1);

function start_secure_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            "lifetime" => $cookieParams["lifetime"],
            "path" => $cookieParams["path"],
            "domain" => $cookieParams["domain"],
            "secure" => isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on",
            "httponly" => true,
            "samesite" => "Strict",
        ]);
        session_start();
    }
}
