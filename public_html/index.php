<?php
session_start();
require_once '../rpg-game/includes/config.php';
require_once '../rpg-game/includes/database.php';
require_once '../rpg-game/includes/character_includes.php';
require_once '../rpg-game/includes/functions.php';
require_once '../rpg-game/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR);
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$character = new Character();
$error = '';
$success = '';

// Sprawdź status rejestracji
$registrationInfo = $character->getRegistrationStatus();

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_character':
                $name = sanitizeInput($_POST['character_name'] ?? '');
                $gender = sanitizeInput($_POST['gender'] ?? 'male');
                $secretCode = sanitizeInput($_POST['secret_code'] ?? '');
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
                
                // Sprawdzenie czy rejestracja jest dostępna
                if ($registrationInfo['type'] === 'error') {
                    $error = $registrationInfo['message'];
                    break;
                }
                
                // Walidacja danych
                if (empty($name)) {
                    $error = 'Nazwa postaci jest wymagana.';
                } elseif (strlen($name) > 50) {
                    $error = 'Nazwa postaci może mieć maksymalnie 50 znaków.';
                } elseif (!in_array($gender, ['male', 'female'])) {
                    $error = 'Nieprawidłowa płeć.';
                } elseif (!verifyRecaptcha($recaptchaResponse)) {
                    $error = 'Weryfikacja reCAPTCHA nie powiodła się.';
                } else {
                    try {
                        $result = $character->create($name, $gender, $secretCode);
                        
                        // Ustaw komunikat sukcesu z informacjami o postaci
                        $genderText = ($gender === 'male') ? 'Mężczyzna' : 'Kobieta';
                        $success = "Postać '{$name}' ({$genderText}) została utworzona!<br>";
                        $success .= "PIN: <strong>{$result['pin']}</strong><br>";
                        $success .= "Avatar: {$result['avatar_image']}<br>";
                        $success .= "<a href='/{$result['hash1']}/{$result['hash2']}' class='btn btn-success mt-2'>";
                        $success .= "<i class='fas fa-play'></i> Rozpocznij grę</a>";
                        
                        // Po udanym utworzeniu - odśwież status rejestracji
                        $registrationInfo = $character->getRegistrationStatus();
                        
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                }
                break;
                
            case 'login_character':
                $pin = sanitizeInput($_POST['pin'] ?? '');
                $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
                
                if (empty($pin)) {
                    $error = 'Podaj PIN postaci.';
                } elseif (!verifyRecaptcha($recaptchaResponse)) {
                    $error = 'Weryfikacja reCAPTCHA nie powiodła się.';
                } else {
                    $charData = $character->getByPin($pin);
                    if ($charData) {
                        setCharacterCookie($charData);
                        header("Location: /" . $charData['hash1'] . "/" . $charData['hash2']);
                        exit;
                    } else {
                        $error = 'Nieprawidłowy PIN.';
                    }
                }
                break;
        }
    }
}

// Pobierz statystyki do wyświetlenia
$db = Database::getInstance();
$stats = [
    'total_characters' => $db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'] ?? 0,
    'total_battles' => $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'] ?? 0,
    'active_today' => $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE DATE(last_login) = CURDATE()")['count'] ?? 0
];

// Pobierz klucz reCAPTCHA
$recaptchaSiteKey = getRecaptchaSiteKey();

// Przypisz zmienne do Smarty
$smarty->assign('registration_info', $registrationInfo);
$smarty->assign('stats', $stats);
$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('recaptcha_site_key', $recaptchaSiteKey);

// Wyświetl szablon
$smarty->display('home.tpl');
?>