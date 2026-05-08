<?php
require_once __DIR__ . '/includes/auth.php';
session_destroy();
header('Location: /php-cyber/login.php');
exit;
