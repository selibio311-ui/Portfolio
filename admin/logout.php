<?php
define('IN_ADMIN', true);
require __DIR__ . '/../includes/bootstrap.php';

// Logout only works through a POST form with a valid CSRF token,
// so another website cannot log the administrator out.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    admin_logout();
    flash('success', 'You have been logged out.');
}

redirect('login.php');
