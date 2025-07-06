<?php
session_start();
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
            'trait_chance' => (float)$_POST['trait_chance'] / 100, // Konwersja z procentów
            'recaptcha_site_key' => sanitizeInput($_POST['recaptcha_site_key']),
            'recaptcha_secret_key' => sanitizeInput($_POST['recaptcha_secret_key']),
            'registration_mode' => sanitizeInput($_POST['registration_mode']),
            'registration_message' => sanitizeInput($_POST['registration_message']),
            'closed_registration_message' => sanitizeInput($_POST['closed_registration_message']),
            'invite_only_message' => sanitizeInput($_POST['invite_only_message'])
        ];
        
        try {
            foreach ($settings as $key => $value) {
                setSetting($key, $value);
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
                $db->query($sql, [$code, $usesLeft, $description]);
                $message = 'Kod tajny został dodany.';
            } catch (Exception $e) {
                $error = 'Błąd dodawania kodu: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['edit_secret_code'])) {
        $id = (int)$_POST['code_id'];
        $code = sanitizeInput($_POST['code']);
        $usesLeft = (int)$_POST['uses_left'];
        $description = sanitizeInput($_POST['description']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($code)) {
            $error = 'Kod jest wymagany.';
        } else {
            try {
                $sql = "UPDATE secret_codes SET code = ?, uses_left = ?, description = ?, is_active = ? WHERE id = ?";
                $db->query($sql, [$code, $usesLeft, $description, $isActive, $id]);
                $message = 'Kod tajny został zaktualizowany.';
            } catch (Exception $e) {
                $error = 'Błąd edycji kodu: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_secret_code'])) {
        $id = (int)$_POST['code_id'];
        
        try {
            $db->query("DELETE FROM secret_codes WHERE id = ?", [$id]);
            $message = 'Kod tajny został usunięty.';
        } catch (Exception $e) {
            $error = 'Błąd usuwania kodu: ' . $e->getMessage();
        }
    } elseif (isset($_POST['toggle_code'])) {
        $id = (int)$_POST['code_id'];
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        try {
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
    'recaptcha_secret_key' => '',
    'registration_mode' => 'open',
    'registration_message' => 'Rejestracja jest obecnie otwarta!',
    'closed_registration_message' => 'Rejestracja jest tymczasowo zamknięta. Spróbuj ponownie później.',
    'invite_only_message' => 'Rejestracja jest możliwa tylko za pomocą kodu zaproszenia.'
];

$settings = array_merge($defaultSettings, $currentSettings);

// Konwersja trait_chance na procenty dla wyświetlenia
$settings['trait_chance'] = $settings['trait_chance'] * 100;

// Pobierz kody tajne
$secretCodes = $db->fetchAll("
    SELECT sc.*, 
           (SELECT COUNT(*) FROM characters WHERE created_at >= sc.created_at) as times_used
    FROM secret_codes sc
    ORDER BY created_at DESC
");

// Statystyki systemu
$systemStats = getSystemStats();

$smarty->assign('settings', $settings);
$smarty->assign('secret_codes', $secretCodes);
$smarty->assign('system_stats', $systemStats);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('settings.tpl');
?>