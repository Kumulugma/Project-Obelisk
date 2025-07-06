<?php
session_start();
// POPRAWNE ŚCIEŻKI - zgodnie z innymi plikami admin
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
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

// Paginacja
$page = (int)($_GET['page'] ?? 1);
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Filtry
$characterFilter = sanitizeInput($_GET['character'] ?? '');
$typeFilter = sanitizeInput($_GET['type'] ?? '');
$dateFrom = sanitizeInput($_GET['date_from'] ?? '');
$dateTo = sanitizeInput($_GET['date_to'] ?? '');

// Buduj zapytanie
$whereConditions = [];
$params = [];

if (!empty($characterFilter)) {
    $whereConditions[] = "(a.name LIKE ? OR d.name LIKE ?)";
    $params[] = '%' . $characterFilter . '%';
    $params[] = '%' . $characterFilter . '%';
}

if (!empty($typeFilter)) {
    $whereConditions[] = "b.battle_type = ?";
    $params[] = $typeFilter;
}

if (!empty($dateFrom)) {
    $whereConditions[] = "DATE(b.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $whereConditions[] = "DATE(b.created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Pobierz walki
$sql = "SELECT b.*, 
               a.name as attacker_name, a.level as attacker_level,
               d.name as defender_name, d.level as defender_level,
               w.name as winner_name,
               wd.name as weapon_dropped_name,
               td.name as trait_dropped_name
        FROM battles b
        JOIN characters a ON b.attacker_id = a.id
        JOIN characters d ON b.defender_id = d.id
        LEFT JOIN characters w ON b.winner_id = w.id
        LEFT JOIN weapons wd ON b.weapon_dropped = wd.id
        LEFT JOIN traits td ON b.trait_dropped = td.id
        $whereClause
        ORDER BY b.created_at DESC
        LIMIT $perPage OFFSET $offset";

$battles = $db->fetchAll($sql, $params);

// Pobierz łączną liczbę walk
$countSql = "SELECT COUNT(*) as count FROM battles b
             JOIN characters a ON b.attacker_id = a.id
             JOIN characters d ON b.defender_id = d.id
             $whereClause";
$totalCount = $db->fetchOne($countSql, $params)['count'];
$totalPages = ceil($totalCount / $perPage);

// Statystyki
$stats = [];
$stats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'];
$stats['battles_today'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles WHERE DATE(created_at) = CURDATE()")['count'];
$stats['random_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles WHERE battle_type = 'random'")['count'];
$stats['challenge_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles WHERE battle_type = 'challenge'")['count'];

$smarty->assign('battles', $battles);
$smarty->assign('stats', $stats);
$smarty->assign('character_filter', $characterFilter);
$smarty->assign('type_filter', $typeFilter);
$smarty->assign('date_from', $dateFrom);
$smarty->assign('date_to', $dateTo);
$smarty->assign('current_page', $page);
$smarty->assign('total_pages', $totalPages);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('battles.tpl');
?>