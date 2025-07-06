<?php
session_start();
require_once '../rpg-game/includes/config.php';
require_once '../rpg-game/includes/database.php';
require_once '../rpg-game/includes/Character.php';
require_once '../rpg-game/includes/functions.php';
require_once '../rpg-game/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR);
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$db = Database::getInstance();
$character = new Character();

// Sprawdź status postaci z ciasteczka
$accessMessage = '';
$accessType = 'info';

if (isset($_SESSION['access_message'])) {
    $accessMessage = $_SESSION['access_message'];
    $accessType = $_SESSION['access_type'] ?? 'info';
    unset($_SESSION['access_message'], $_SESSION['access_type']);
} else {
    // Sprawdź status postaci z ciasteczka
    $cookieStatus = checkCharacterStatusFromCookie();
    
    if ($cookieStatus['status'] === 'banished') {
        $accessMessage = $cookieStatus['message'];
        $accessType = 'warning';
    } elseif ($cookieStatus['status'] === 'deleted') {
        $accessMessage = $cookieStatus['message'];
        $accessType = 'danger';
    }
}

// Pobierz ustawienia rejestracji
$registrationMode = getSetting('registration_mode', 'open');
$registrationMessages = [
    'open' => getSetting('registration_message', 'Rejestracja jest obecnie otwarta!'),
    'invite_only' => getSetting('invite_only_message', 'Rejestracja jest możliwa tylko za pomocą kodu zaproszenia.'),
    'closed' => getSetting('closed_registration_message', 'Rejestracja jest tymczasowo zamknięta. Spróbuj ponownie później.')
];

$showRegistrationForm = in_array($registrationMode, ['open', 'invite_only']);

// Ustal typ i komunikat rejestracji
$registrationInfo = [
    'type' => $registrationMode === 'open' ? 'success' : ($registrationMode === 'invite_only' ? 'warning' : 'danger'),
    'message' => $registrationMessages[$registrationMode]
];

// Pobierz klucz reCAPTCHA
$recaptchaSiteKey = getSetting('recaptcha_site_key', '');

// Obsługa formularzy
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_character'])) {
        $name = sanitizeInput($_POST['name'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $secretCode = sanitizeInput($_POST['secret_code'] ?? '');
        
        // Walidacja reCAPTCHA jeśli jest włączona
        if (!empty($recaptchaSiteKey)) {
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            if (!verifyRecaptcha($recaptchaResponse)) {
                $message = 'Weryfikacja reCAPTCHA nie powiodła się.';
                $messageType = 'error';
            }
        }
        
        if (empty($message)) {
            try {
                // Sprawdź tryb rejestracji
                if ($registrationMode === 'closed') {
                    throw new Exception('Rejestracja jest obecnie zamknięta.');
                }
                
                if ($registrationMode === 'invite_only') {
                    if (empty($secretCode)) {
                        throw new Exception('Kod zaproszenia jest wymagany.');
                    }
                    
                    if (!verifySecretCode($secretCode)) {
                        throw new Exception('Nieprawidłowy kod zaproszenia.');
                    }
                }
                
                $characterData = $character->create($name, $gender, $secretCode);
                
                // Przekieruj do profilu
                header('Location: /' . $characterData['hash1'] . '/' . $characterData['hash2']);
                exit;
                
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['login_character'])) {
        $pin = sanitizeInput($_POST['pin'] ?? '');
        
        if (empty($pin) || !isValidPin($pin)) {
            $message = 'Podaj prawidłowy 6-cyfrowy PIN.';
            $messageType = 'error';
        } else {
            try {
                $characterData = $character->getByPin($pin);
                
                if (!$characterData) {
                    throw new Exception('Postać o podanym PIN-ie nie istnieje.');
                }
                
                // Sprawdź status postaci
                if ($characterData['status'] === 'deleted') {
                    throw new Exception('Ta postać została usunięta.');
                }
                
                if ($characterData['status'] === 'banished') {
                    throw new Exception('Ta postać została odebrana przez administratora.');
                }
                
                // Przekieruj do profilu
                header('Location: /' . $characterData['hash1'] . '/' . $characterData['hash2']);
                exit;
                
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Pobierz statystyki
$stats = [];
$stats['total_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE status = 'active'")['count'];
$stats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'];
$stats['active_today'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE DATE(last_login) = CURDATE() AND status = 'active'")['count'];

// Przypisz zmienne do szablonu
$smarty->assign('registration_mode', $registrationMode);
$smarty->assign('registration_info', $registrationInfo);
$smarty->assign('show_registration_form', $showRegistrationForm);
$smarty->assign('recaptcha_site_key', $recaptchaSiteKey);
$smarty->assign('stats', $stats);
$smarty->assign('message', $message);
$smarty->assign('message_type', $messageType);
$smarty->assign('access_message', $accessMessage);
$smarty->assign('access_type', $accessType);
$smarty->assign('site_url', SITE_URL);

$smarty->display('home.tpl');

/**
 * Weryfikuje kod reCAPTCHA
 */
function verifyRecaptcha($response) {
    $secretKey = getSetting('recaptcha_secret_key', '');
    
    if (empty($secretKey) || empty($response)) {
        return false;
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return false;
    }
    
    $resultData = json_decode($result, true);
    return isset($resultData['success']) && $resultData['success'] === true;
}

/**
 * Weryfikuje kod tajny
 */
function verifySecretCode($code) {
    global $db;
    
    try {
        $secretCode = $db->fetchOne(
            "SELECT id, uses_left FROM secret_codes WHERE code = ? AND is_active = 1",
            [$code]
        );
        
        if (!$secretCode || $secretCode['uses_left'] <= 0) {
            return false;
        }
        
        // Zmniejsz liczbę pozostałych użyć
        $db->query(
            "UPDATE secret_codes SET uses_left = uses_left - 1 WHERE id = ?",
            [$secretCode['id']]
        );
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}
?>