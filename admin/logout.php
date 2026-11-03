<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if (admin_logged_in()) {
    log_admin((int) $_SESSION['admin_id'], 'logout');
}
logout_admin();
redirect('/admin/login.php');
