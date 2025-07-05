<?php
// Zamień zawartość public_html/admin/login.php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// POPRAWNE ŚCIEŻKI - według explore.php
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
require_once '../../rpg-game/includes/functions.php';
require_once '../../rpg-game/vendor/autoload.php';

// Sprawdź czy już zalogowany
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
        try {
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
        } catch (Exception $e) {
            $error = 'Błąd bazy danych: ' . $e->getMessage();
        }
    }
}

$smarty->assign('error', $error);
$smarty->display('login.tpl');
?>