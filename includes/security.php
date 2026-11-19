<?php
/*
 * Security helpers: secure sessions, HTTP security headers, CSRF tokens.
 */

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
}

function init_security(): void
{
    // Never show PHP errors to visitors (they can leak paths / SQL details).
    ini_set('display_errors', (defined('APP_DEBUG') && APP_DEBUG) ? '1' : '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);

    set_exception_handler(function (Throwable $ex) {
        error_log('Unhandled exception: ' . $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine());
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo 'Something went wrong. Please try again later.';
    });

    send_security_headers();
    start_secure_session();
}

function send_security_headers(): void
{
    header_remove('X-Powered-By');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; "
        . "style-src 'self' 'unsafe-inline'; script-src 'self'; "
        . "frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

function start_secure_session(): void
{
    ini_set('session.use_strict_mode', '1');   // reject unknown session IDs
    ini_set('session.use_only_cookies', '1');  // no session IDs in URLs
    session_name('PFSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),   // cookie only over HTTPS when available
        'httponly' => true,         // JavaScript cannot read the cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- CSRF protection ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function require_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $real = $_SESSION['csrf'] ?? '';
    if (!is_string($sent) || $real === '' || !hash_equals($real, $sent)) {
        http_response_code(403);
        exit('Invalid or expired form token. Please go back, refresh the page and try again.');
    }
}

function client_ip(): string
{
    // Only REMOTE_ADDR is trusted; X-Forwarded-For can be forged by visitors.
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}
