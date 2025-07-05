<?php
session_start();
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
require_once '../../rpg-game/includes/Character.php';
require_once '../../rpg-game/includes/Battle.php';
require_once '../../rpg-game/includes/functions.php';
require_once '../../rpg-game/vendor/autoload.php';

// Sprawdź autoryzację
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
$error = '';

// Paginacja
$page = (int)($_GET['page'] ?? 1);
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filtry
$search = sanitizeInput($_GET['search'] ?? '');
$sortBy = sanitizeInput($_GET['sort'] ?? 'created_at');
$sortOrder = sanitizeInput($_GET['order'] ?? 'DESC');

$allowedSorts = ['name', 'level', 'experience', 'created_at', 'last_login'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';
if (!in_array($sortOrder, ['ASC', 'DESC'])) $sortOrder = 'DESC';

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_character'])) {
        $id = (int)$_POST['character_id'];
        try {
            $db->execute("DELETE FROM characters WHERE id = ?", [$id]);
            $message = 'Postać została usunięta.';
        } catch (Exception $e) {
            $error = 'Błąd usuwania postaci: ' . $e->getMessage();
        }
    } elseif (isset($_POST['edit_character'])) {
        $id = (int)$_POST['character_id'];
        $data = [
            'health' => (int)$_POST['health'],
            'max_health' => (int)$_POST['max_health'],
            'stamina' => (int)$_POST['stamina'],
            'max_stamina' => (int)$_POST['max_stamina'],
            'damage' => (int)$_POST['damage'],
            'dexterity' => (int)$_POST['dexterity'],
            'agility' => (int)$_POST['agility'],
            'armor' => (int)$_POST['armor'],
            'max_armor' => (int)$_POST['max_armor'],
            'armor_penetration' => (int)$_POST['armor_penetration'],
            'level' => (int)$_POST['level'],
            'experience' => (int)$_POST['experience'],
            'energy_points' => (int)$_POST['energy_points'],
            'challenge_points' => (int)$_POST['challenge_points']
        ];
        
        try {
            $sql = "UPDATE characters SET " . 
                   "health = ?, max_health = ?, stamina = ?, max_stamina = ?, " .
                   "damage = ?, dexterity = ?, agility = ?, armor = ?, max_armor = ?, " .
                   "armor_penetration = ?, level = ?, experience = ?, " .
                   "energy_points = ?, challenge_points = ? WHERE id = ?";
            
            $db->execute($sql, array_merge(array_values($data), [$id]));
            $message = 'Postać została zaktualizowana.';
        } catch (Exception $e) {
            $error = 'Błąd edycji postaci: ' . $e->getMessage();
        }
    }
}

// Pobierz postaci
$whereClause = '';
$params = [];
if (!empty($search)) {
    $whereClause = 'WHERE name LIKE ?';
    $params[] = '%' . $search . '%';
}

$sql = "SELECT c.*, w.name as weapon_name,
               (SELECT COUNT(*) FROM battles WHERE attacker_id = c.id OR defender_id = c.id) as total_battles,
               (SELECT COUNT(*) FROM battles WHERE winner_id = c.id) as won_battles
        FROM characters c 
        LEFT JOIN weapons w ON c.equipped_weapon_id = w.id 
        $whereClause
        ORDER BY $sortBy $sortOrder 
        LIMIT $perPage OFFSET $offset";

$characters = $db->fetchAll($sql, $params);

// Pobierz łączną liczbę postaci
$countSql = "SELECT COUNT(*) as count FROM characters $whereClause";
$totalCount = $db->fetchOne($countSql, $params)['count'];
$totalPages = ceil($totalCount / $perPage);

// Pobierz listę broni dla select
$weapons = $db->fetchAll("SELECT id, name FROM weapons ORDER BY name");

$smarty->assign('characters', $characters);
$smarty->assign('weapons', $weapons);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('search', $search);
$smarty->assign('sort_by', $sortBy);
$smarty->assign('sort_order', $sortOrder);
$smarty->assign('current_page', $page);
$smarty->assign('total_pages', $totalPages);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('characters.tpl');
?>