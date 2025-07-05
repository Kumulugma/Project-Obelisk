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

$character = new Character();
$error = '';
$success = '';

$existingCharacter = getCharacterFromCookie();
if ($existingCharacter) {
    $charData = $character->getByPin($existingCharacter['pin']);
    if ($charData) {
        header("Location: /" . $charData['hash1'] . "/" . $charData['hash2']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_character'])) {
        $name = sanitizeInput($_POST['name'] ?? '');
        $secretCode = sanitizeInput($_POST['secret_code'] ?? '');
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
        
        if (empty($name)) {
            $error = 'Podaj imię postaci.';
        } elseif (!verifyRecaptcha($recaptchaResponse)) {
            $error = 'Weryfikacja reCAPTCHA nie powiodła się.';
        } else {
            try {
                $newCharacter = $character->create($name, $secretCode);
                setCharacterCookie($newCharacter);
                
                $smarty->assign('pin', $newCharacter['pin']);
                $smarty->assign('character_url', SITE_URL . '/' . $newCharacter['hash1'] . '/' . $newCharacter['hash2']);
                $smarty->assign('show_pin_info', true);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } elseif (isset($_POST['login_pin'])) {
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
    }
}

$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('site_url', SITE_URL);
$smarty->assign('is_mobile', isMobile());

$smarty->display('home.tpl');
?>