<?php
/*
 * Administrator authentication: password_verify(), session regeneration,
 * idle timeout and protection against repeated login attempts.
 */

const LOGIN_MAX_ATTEMPTS = 5;      // failed attempts allowed ...
const LOGIN_WINDOW       = 900;    // ... within 15 minutes (per IP address)
const SESSION_IDLE       = 1800;   // log out after 30 minutes of inactivity

function admin_logged_in(): bool
{
    if (empty($_SESSION['admin_id'])) {
        return false;
    }
    if (time() - (int)($_SESSION['last_activity'] ?? 0) > SESSION_IDLE) {
        admin_logout();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        redirect('login.php');
    }
}

function admin_logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);   // old session ID becomes useless
}

/** Seconds the visitor must still wait, or 0 if they may try. */
function login_lockout_seconds(): int
{
    $pdo = db();
    $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')
        ->execute([time() - LOGIN_WINDOW]);

    $st = $pdo->prepare('SELECT COUNT(*), MAX(attempted_at) FROM login_attempts WHERE ip_address = ?');
    $st->execute([client_ip()]);
    [$count, $last] = $st->fetch(PDO::FETCH_NUM);

    if ((int)$count >= LOGIN_MAX_ATTEMPTS) {
        return max(1, ((int)$last + LOGIN_WINDOW) - time());
    }
    return 0;
}

/** @return array{ok:bool,error:string} */
function attempt_login(string $username, string $password): array
{
    $wait = login_lockout_seconds();
    if ($wait > 0) {
        $mins = (int)ceil($wait / 60);
        return ['ok' => false, 'error' => "Too many failed attempts. Please try again in $mins minute(s)."];
    }

    $st = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $st->execute([$username]);
    $admin = $st->fetch();

    // Always run password_verify (even for unknown users) so timing does not reveal valid usernames.
    $hash = $admin['password_hash'] ?? ('$2y$10$' . str_repeat('a', 53));
    $ok   = password_verify($password, $hash) && $admin;

    if (!$ok) {
        db()->prepare('INSERT INTO login_attempts (ip_address, attempted_at) VALUES (?, ?)')
            ->execute([client_ip(), time()]);
        $left = max(0, LOGIN_MAX_ATTEMPTS - failed_attempt_count());
        $msg = 'Invalid username or password.';
        if ($left > 0) {
            $msg .= " $left attempt(s) left before a temporary lockout.";
        } else {
            $msg .= ' Too many failed attempts - login is temporarily locked.';
        }
        return ['ok' => false, 'error' => $msg];
    }

    // Success
    db()->prepare('DELETE FROM login_attempts WHERE ip_address = ?')->execute([client_ip()]);

    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    }

    session_regenerate_id(true);               // prevents session fixation
    $_SESSION['admin_id']      = (int)$admin['id'];
    $_SESSION['admin_user']    = $admin['username'];
    $_SESSION['last_activity'] = time();
    $_SESSION['csrf']          = bin2hex(random_bytes(32));

    return ['ok' => true, 'error' => ''];
}

function failed_attempt_count(): int
{
    $st = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at >= ?');
    $st->execute([client_ip(), time() - LOGIN_WINDOW]);
    return (int)$st->fetchColumn();
}
