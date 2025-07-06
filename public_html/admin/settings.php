<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

session_start();
require_once '../../rpg-game/includes/config.php';
require_once '../../rpg-game/includes/database.php';
require_once '../../rpg-game/includes/character_includes.php';
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
            'trait_chance' => (float)$_POST['trait_chance'] / 100, // Konwersja z % na ułamek dziesiętny
            'recaptcha_site_key' => sanitizeInput($_POST['recaptcha_site_key']),
            'recaptcha_secret_key' => sanitizeInput($_POST['recaptcha_secret_key']),
            
            // USTAWIENIA REJESTRACJI
            'registration_mode' => sanitizeInput($_POST['registration_mode']),
            'registration_message' => sanitizeInput($_POST['registration_message']),
            'closed_registration_message' => sanitizeInput($_POST['closed_registration_message']),
            'invite_only_message' => sanitizeInput($_POST['invite_only_message'])
        ];
        
        // Walidacja trybu rejestracji
        if (!in_array($settings['registration_mode'], ['open', 'closed', 'invite_only'])) {
            $settings['registration_mode'] = 'open';
        }
        
        try {
            setMultipleSystemSettings($settings);
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
    } elseif (isset($_POST['add_avatar'])) {
        $imagePath = sanitizeInput($_POST['image_path']);
        $gender = sanitizeInput($_POST['gender']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($imagePath) || !in_array($gender, ['male', 'female', 'unisex'])) {
            $error = 'Ścieżka obrazka i płeć są wymagane.';
        } else {
            try {
                $sql = "INSERT INTO avatar_images (image_path, gender, is_active) VALUES (?, ?, ?)";
                $db->query($sql, [$imagePath, $gender, $isActive]);
                $message = 'Avatar został dodany.';
            } catch (Exception $e) {
                $error = 'Błąd dodawania avatara: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['bulk_add_avatars'])) {
        $avatarsData = sanitizeInput($_POST['avatars_data'] ?? '');
        $defaultGender = sanitizeInput($_POST['default_gender']);
        
        if (!empty($avatarsData)) {
            $lines = explode("\n", trim($avatarsData));
            $added = 0;
            $errors = 0;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Format: /path/to/avatar.png|gender lub samo /path/to/avatar.png
                $parts = explode('|', $line);
                $imagePath = trim($parts[0]);
                $gender = isset($parts[1]) ? trim($parts[1]) : $defaultGender;
                
                if (!in_array($gender, ['male', 'female', 'unisex'])) {
                    $gender = $defaultGender;
                }
                
                try {
                    $sql = "INSERT INTO avatar_images (image_path, gender, is_active) VALUES (?, ?, 1)";
                    $db->query($sql, [$imagePath, $gender]);
                    $added++;
                } catch (Exception $e) {
                    $errors++;
                }
            }
            
            $message = "Dodano {$added} avatarów. Błędów: {$errors}";
        }
    }
}

// Pobierz ustawienia
$currentSettings = getAllSystemSettings();

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

// Pobierz avatary
$avatars = $db->fetchAll("
    SELECT a.*, 
           (SELECT COUNT(*) FROM characters WHERE avatar_image = a.image_path) as usage_count
    FROM avatar_images a 
    ORDER BY a.gender, a.id
");

// Statystyki systemu
$systemStats = [];
$systemStats['total_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'] ?? 0;
$systemStats['active_users'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;
$systemStats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'] ?? 0;

// Rozmiar bazy danych (bezpiecznie)
try {
    $dbSizeResult = $db->fetchOne("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ");
    $systemStats['db_size'] = $dbSizeResult['size_mb'] ?? 0;
} catch (Exception $e) {
    $systemStats['db_size'] = 'N/A';
}

// Statystyki avatarów
$avatarStats = [
    'total' => count($avatars),
    'active' => count(array_filter($avatars, function($a) { return $a['is_active']; })),
    'male' => count(array_filter($avatars, function($a) { return $a['gender'] === 'male'; })),
    'female' => count(array_filter($avatars, function($a) { return $a['gender'] === 'female'; })),
    'unisex' => count(array_filter($avatars, function($a) { return $a['gender'] === 'unisex'; }))
];

// Statystyki kodów tajnych
$codeStats = [
    'total' => count($secretCodes),
    'active' => count(array_filter($secretCodes, function($c) { return $c['is_active']; })),
    'unlimited' => count(array_filter($secretCodes, function($c) { return $c['uses_left'] === -1; })),
    'expired' => count(array_filter($secretCodes, function($c) { return $c['uses_left'] === 0; }))
];

// Przypisz zmienne do Smarty
$smarty->assign('settings', $settings);
$smarty->assign('secret_codes', $secretCodes);
$smarty->assign('code_stats', $codeStats);
$smarty->assign('avatars', $avatars);
$smarty->assign('avatar_stats', $avatarStats);
$smarty->assign('system_stats', $systemStats);
$smarty->assign('message', $message);
$smarty->assign('error', $error);
$smarty->assign('admin_username', $_SESSION['admin_username']);

$smarty->display('settings.tpl');
?>