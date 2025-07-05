<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/Character.php';
require_once '../includes/Battle.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR . 'admin/');
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$db = Database::getInstance();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_weapon':
                $name = sanitizeInput($_POST['name']);
                $damage = intval($_POST['damage']);
                $penetration = intval($_POST['armor_penetration'] ?? 0);
                $dropChance = floatval($_POST['drop_chance']);
                $imagePath = sanitizeInput($_POST['image_path']);
                
                if (!empty($name) && $damage > 0) {
                    $sql = "INSERT INTO weapons (name, damage, armor_penetration, drop_chance, image_path) VALUES (?, ?, ?, ?, ?)";
                    $db->query($sql, [$name, $damage, $penetration, $dropChance, $imagePath]);
                    $message = 'Broń została dodana.';
                    $messageType = 'success';
                } else {
                    $message = 'Wypełnij wszystkie wymagane pola.';
                    $messageType = 'error';
                }
                break;
                
            case 'update_weapon':
                $id = intval($_POST['weapon_id']);
                $name = sanitizeInput($_POST['name']);
                $damage = intval($_POST['damage']);
                $penetration = intval($_POST['armor_penetration'] ?? 0);
                $dropChance = floatval($_POST['drop_chance']);
                $imagePath = sanitizeInput($_POST['image_path']);
                
                if (!empty($name) && $damage > 0) {
                    $sql = "UPDATE weapons SET name = ?, damage = ?, armor_penetration = ?, drop_chance = ?, image_path = ? WHERE id = ?";
                    $db->query($sql, [$name, $damage, $penetration, $dropChance, $imagePath, $id]);
                    $message = 'Broń została zaktualizowana.';
                    $messageType = 'success';
                }
                break;
                
            case 'delete_weapon':
                $id = intval($_POST['weapon_id']);
                if ($id > 1) {
                    $db->query("DELETE FROM weapons WHERE id = ?", [$id]);
                    $message = 'Broń została usunięta.';
                    $messageType = 'success';
                } else {
                    $message = 'Nie można usunąć domyślnej broni.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

$weapons = $db->fetchAll("SELECT * FROM weapons ORDER BY id");

$smarty->assign('weapons', $weapons);
$smarty->assign('message', $message);
$smarty->assign('message_type', $messageType);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('weapons.tpl');
?>