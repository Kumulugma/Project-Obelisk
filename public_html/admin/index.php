<?php
// Zamień zawartość public_html/admin/index.php
session_start();


// POPRAWNE ŚCIEŻKI - według explore.php
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
require_once '../../rpg-game/includes/character_includes.php';
require_once '../../rpg-game/includes/Battle.php';
require_once '../../rpg-game/includes/functions.php';
require_once '../../rpg-game/vendor/autoload.php';

// Sprawdź autoryzację
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Pobierz ustawienia reCAPTCHA z bazy danych
$recaptchaSiteKey = getRecaptchaSiteKey();
$recaptchaSecretKey = getRecaptchaSecretKey();

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR . 'admin/');
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$db = Database::getInstance();

// Pobierz statystyki
$stats = [];
$stats['total_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
$stats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'];
$stats['active_today'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE DATE(last_login) = CURDATE()")['count'];
$stats['total_weapons'] = $db->fetchOne("SELECT COUNT(*) as count FROM weapons")['count'];
$stats['total_traits'] = $db->fetchOne("SELECT COUNT(*) as count FROM traits")['count'];

// Ostatnie walki
$recentBattles = $db->fetchAll("
    SELECT b.*, a.name as attacker_name, d.name as defender_name, w.name as winner_name
    FROM battles b
    JOIN characters a ON b.attacker_id = a.id
    JOIN characters d ON b.defender_id = d.id
    LEFT JOIN characters w ON b.winner_id = w.id
    ORDER BY b.created_at DESC
    LIMIT 10
");

// Najlepsi gracze
$topPlayers = $db->fetchAll("
    SELECT name, level, experience, last_login
    FROM characters
    ORDER BY level DESC, experience DESC
    LIMIT 10
");

$smarty->assign('stats', $stats);
$smarty->assign('recent_battles', $recentBattles);
$smarty->assign('top_players', $topPlayers);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('dashboard.tpl');
?>