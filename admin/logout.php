<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
if (!empty($_SESSION['admin_id'])) audit('logout', 'admin_users', (int) $_SESSION['admin_id']);
$_SESSION = [];
session_destroy();
redirect('login.php');
