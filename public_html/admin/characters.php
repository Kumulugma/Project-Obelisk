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
$character = new Character();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_character':
                $id = intval($_POST['character_id']);
                $fields = ['health', 'max_health', 'stamina', 'max_stamina', 'damage', 'dexterity', 'agility', 'armor', 'max_armor', 'level', 'experience'];
                
                $updateParts = [];
                $params = [];
                
                foreach ($fields as $field) {
                    if (isset($_POST[$field])) {
                        $updateParts[] = "$field = ?";
                        $params[] = intval($_POST[$field]);
                    }
                }
                
                if (!empty($updateParts)) {
                    $params[] = $id;
                    $sql = "UPDATE characters SET " . implode(', ', $updateParts) . " WHERE id = ?";
                    $db->query($sql, $params);
                    $message = 'Postać została zaktualizowana.';
                    $messageType = 'success';
                }
                break;
                
            case 'delete_character':
                $id = intval($_POST['character_id']);
                $db->query("DELETE FROM characters WHERE id = ?", [$id]);
                $message = 'Postać została usunięta.';
                $messageType = 'success';
                break;
        }
    }
}

$where = "1=1";
$params = [];
$orderBy = "id DESC";

if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where .= " AND (name LIKE ? OR pin LIKE ?)";
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['min_level'])) {
    $where .= " AND level >= ?";
    $params[] = intval($_GET['min_level']);
}

if (!empty($_GET['sort'])) {
    $allowedSorts = ['name', 'level', 'experience', 'last_login', 'created_at'];
    if (in_array($_GET['sort'], $allowedSorts)) {
        $orderBy = $_GET['sort'] . ' ' . ($_GET['order'] === 'asc' ? 'ASC' : 'DESC');
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalQuery = "SELECT COUNT(*) as count FROM characters WHERE $where";
$total = $db->fetchOne($totalQuery, $params)['count'];
$totalPages = ceil($total / $perPage);

$charactersQuery = "SELECT * FROM characters WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$characters = $db->fetchAll($charactersQuery, $params);

$smarty->assign('characters', $characters);
$smarty->assign('message', $message);
$smarty->assign('message_type', $messageType);
$smarty->assign('current_page', $page);
$smarty->assign('total_pages', $totalPages);
$smarty->assign('total_characters', $total);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('characters.tpl');
?>