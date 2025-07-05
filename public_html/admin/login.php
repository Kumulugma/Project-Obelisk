<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Character.php';
require_once '../includes/Battle.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR . 'admin/');
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Podaj nazwę użytkownika i hasło.';
    } else {
        $db = Database::getInstance();
        $admin = $db->fetchOne(
            "SELECT * FROM admin_users WHERE username = ?",
            [$username]
        );
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Nieprawidłowe dane logowania.';
        }
    }
}

$smarty->assign('error', $error);
$smarty->display('login.tpl');