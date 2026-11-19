<?php
/*
 * General helpers: output escaping, settings, flash messages,
 * validation helpers and secure image uploads.
 */

/** Escape text for safe HTML output (prevents XSS). */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ---------- Settings (stored in the database) ---------- */

function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings();
    return (isset($all[$key]) && $all[$key] !== '') ? $all[$key] : $default;
}

function set_setting(string $key, string $value): void
{
    $pdo = db();
    $st = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE setting_key = ?');
    $st->execute([$key]);
    if ((int)$st->fetchColumn() > 0) {
        $pdo->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?')->execute([$value, $key]);
    } else {
        $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)')->execute([$key, $value]);
    }
}

/* ---------- Flash messages ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function render_flash(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $class = $f['type'] === 'success' ? 'alert-success' : 'alert-error';
        $html .= '<div class="alert ' . $class . '" role="alert">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

/* ---------- Display helpers ---------- */

/** Turn plain text with blank lines into escaped <p> paragraphs. */
function paragraphs(string $text): string
{
    $out = '';
    foreach (preg_split('/\R{2,}/', trim($text)) as $para) {
        if (trim($para) !== '') {
            $out .= '<p>' . nl2br(e(trim($para))) . '</p>';
        }
    }
    return $out;
}

/** Build a safe src for a stored image path (assets/... or uploads/...). */
function img_src(string $path): string
{
    if (!preg_match('#^(assets/images|uploads)/[A-Za-z0-9_.\-]+$#', $path)) {
        return '';
    }
    return e(BASE_URL . $path);
}

/** Inline CSS that applies the accent colour chosen in Website Settings. */
function accent_style(): string
{
    $hex = setting('accent_color', '#22d3ee');
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
        return '';
    }
    [$r, $g, $b] = [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    $dim = sprintf('#%02x%02x%02x', (int)($r * 0.7), (int)($g * 0.7), (int)($b * 0.7));
    return '<style>html.dark-mode,html:not(.dark-mode){'
        . "--accent:$hex;--accent-dim:$dim;"
        . "--accent-glow:rgba($r,$g,$b,0.35);--accent-soft:rgba($r,$g,$b,0.12);"
        . '--border-accent:rgba(' . "$r,$g,$b" . ',0.35);}</style>';
}

/* ---------- Input validation helpers ---------- */

/** Trim and strip control characters (keeps line breaks and tabs). */
function clean_text($value): string
{
    if (!is_string($value)) {
        return '';
    }
    $value = preg_replace('/[^\P{C}\n\r\t]/u', '', $value) ?? '';
    return trim($value);
}

function valid_url(string $url): bool
{
    if ($url === '') {
        return true;   // optional
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    // Only http/https - blocks javascript:, data:, etc.
    return in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

/* ---------- Secure image uploads ---------- */

/**
 * Validates and stores an uploaded image.
 * @return array{0:?string,1:?string} [relative path or null, error or null]
 *         (null, null) means no file was chosen.
 */
function handle_image_upload(string $field, string $prefix = 'img'): array
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'The image could not be uploaded (error code ' . (int)$file['error'] . ').'];
    }
    if ($file['size'] <= 0 || $file['size'] > MAX_UPLOAD_BYTES) {
        return [null, 'Image must be 2 MB or smaller.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'Invalid upload.'];
    }

    // Check the REAL file content, not the browser-supplied name or MIME type.
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG, WEBP or GIF images are allowed.'];
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !isset($allowed[$info['mime']]) || $info[0] < 1 || $info[1] < 1 || $info[0] > 6000 || $info[1] > 6000) {
        return [null, 'The file is not a valid image (or is larger than 6000 px).'];
    }

    // Safe, unique filename generated by the server; extension comes from the detected type.
    $name = $prefix . '_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $dest = UPLOAD_DIR . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [null, 'The image could not be saved on the server.'];
    }
    @chmod($dest, 0644);

    return ['uploads/' . $name, null];
}

/** Delete a previously uploaded file (only inside uploads/, never assets/). */
function delete_upload(?string $path): void
{
    if ($path && preg_match('#^uploads/[A-Za-z0-9_]+\.(jpg|png|webp|gif)$#', $path)) {
        $full = UPLOAD_DIR . '/' . basename($path);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
