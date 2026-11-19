<?php
/*
 * Copy this file to includes/config.php and fill in your own values.
 * config.php is listed in .gitignore - NEVER commit real credentials.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');            // use a strong password on the live server
define('DB_CHARSET', 'utf8mb4');

// Show detailed errors only on your own computer while developing.
define('APP_DEBUG', false);

// One-time key that unlocks admin/setup.php so only YOU can create the
// first administrator. Use a long random string, then clear it (set to '')
// and delete admin/setup.php after the account is created.
define('ADMIN_SETUP_KEY', '');
