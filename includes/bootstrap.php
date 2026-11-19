<?php
/*
 * Loaded at the top of every page.
 * Admin pages define('IN_ADMIN', true) BEFORE including this file.
 */

if (!defined('IN_ADMIN')) {
    define('IN_ADMIN', false);
}

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', IN_ADMIN ? '../' : '');          // prefix for assets/links
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);          // 2 MB per image

date_default_timezone_set('Asia/Manila');

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('The site is not configured yet. Copy includes/config.sample.php to includes/config.php.');
}
require $configFile;

require __DIR__ . '/security.php';
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/auth.php';

init_security();
