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
$message = '';
$error = '';

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $settings = [
            'max_characters' => (int)$_POST['max_characters'],
            'daily_energy' => (int)$_POST['daily_energy'],
            'daily_challenges' => (int)$_POST['daily_challenges'],
            'max_friends' => (int)$_POST['max_friends'],
            'exp_per_level' => (int)$_POST['exp_per_level'],
            'trait_chance' => (float)$_POST['trait_chance'],
            'recaptcha_site_key' => sanitizeInput($_POST['recaptcha_site_key']),
            'recaptcha_secret_key' => sanitizeInput($_POST['recaptcha_secret_key'])
        ];
        
        try {
            foreach ($settings as $key => $value) {
                $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                        VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?";
                // ZMIANA: execute() -> query()
                $db->query($sql, [$key, $value, $value]);
            }
            $message = 'Ustawienia zostały zapisane.';
        } catch (Exception $e) {
            $error = 'Błąd zapisywania ustawień: ' . $e->getMessage();
        }
    } elseif (isset($_POST['add_secret_code'])) {
        $code = sanitizeInput($_POST['code']);
        $usesLeft = (int)$_POST['uses_left'];
        $description = sanitizeInput($_POST['description']);
        
        if (empty($code)) {
            $error = 'Kod jest wymagany.';
        } else {
            try {
                $sql = "INSERT INTO secret_codes (code, uses_left, description) VALUES (?, ?, ?)";
                // ZMIANA: execute() -> query()
                $db->query($sql, [$code, $usesLeft, $description]);
                $message = 'Kod tajny został dodany.';
            } catch (Exception $e) {
                $error = 'Błąd dodawania kodu: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['toggle_code'])) {
        $id = (int)$_POST['code_id'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            // ZMIANA: execute() -> query()
            $db->query("UPDATE secret_codes SET is_active = ? WHERE id = ?", [$isActive, $id]);
            $message = 'Status kodu został zmieniony.';
        } catch (Exception $e) {
            $error = 'Błąd zmiany statusu kodu: ' . $e->getMessage();
        }
    }
}

// Pobierz ustawienia
$currentSettings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
foreach ($settingsData as $setting) {
    $currentSettings[$setting['setting_key']] = $setting['setting_value'];
}

// Ustawienia domyślne
$defaultSettings = [
    'max_characters' => 1000,
    'daily_energy' => 10,
    'daily_challenges' => 2,
    'max_friends' => 10,
    'exp_per_level' => 100,
    'trait_chance' => 0.2,
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => ''
];

$settings = array_merge($defaultSettings, $currentSettings);

// POPRAWKA: Pobierz kody tajne BEZ kolumny secret_code_used
$secretCodes = $db->fetchAll("
    SELECT sc.*, 
           0 as times_used
    FROM secret_codes sc
    ORDER BY created_at DESC
");

// Pobierz avatary
$avatars = $db->fetchAll("SELECT * FROM avatar_images ORDER BY id");

// Statystyki systemu
$systemStats = [];
$systemStats['total_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
$systemStats['active_users'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'];
$systemStats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'];
$systemStats['db_size'] = $db->fetchOne("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()")['size_mb'] ?? 0;

$smarty->assign('settings', $settings);
$smarty->assign('secret_codes', $secretCodes);
$smarty->assign('avatars', $avatars);
$smarty->assign('system_stats', $systemStats);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('settings.tpl');
?>