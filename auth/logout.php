<?php
// auth/logout.php - Session destruction and redirect
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';

logoutUser();
session_start();
setFlash('info', 'You have been successfully signed out.');
header('Location: ' . BASE_URL . '/index.php');
exit;
