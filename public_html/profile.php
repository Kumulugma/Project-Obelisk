<?php
session_start();
require_once '../rpg-game/includes/config.php';
require_once '../rpg-game/includes/database.php';
require_once '../rpg-game/includes/character_includes.php';
require_once '../rpg-game/includes/Battle.php';
require_once '../rpg-game/includes/functions.php';
require_once '../rpg-game/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR);
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$character = new Character();
$battle = new Battle();

// Pobierz hashe z URL-a
$hash1 = $_GET['hash1'] ?? '';
$hash2 = $_GET['hash2'] ?? '';

if (empty($hash1) || empty($hash2)) {
    header('Location: /');
    exit;
}

// Sprawdź czy postać istnieje - zastąpione bezpośrednim wywołaniem getByHashes
$charData = $character->getByHashes($hash1, $hash2);
if (!$charData) {
    // Spróbuj automatycznego logowania z ciasteczka
    $cookieData = getCharacterFromCookie();
    if ($cookieData && !empty($cookieData['pin'])) {
        $charData = $character->getByPin($cookieData['pin']);
        if ($charData) {
            // Przekieruj na poprawny URL
            header("Location: /" . $charData['hash1'] . "/" . $charData['hash2']);
            exit;
        }
    }
    
    // Jeśli nic nie zadziałało, przekieruj na stronę główną
    header('Location: /');
    exit;
}

// Zaktualizuj ostatnie logowanie i resetuj punkty
$character->updateLastLogin($charData['id']);
$character->resetDailyPoints($charData['id']);

// Pobierz zaktualizowane dane
$charData = $character->getByHashes($hash1, $hash2);

// Pobierz dane do wyświetlenia
$traits = $character->getTraits($charData['id']);
$friends = $character->getFriends($charData['id']);
$weapons = $character->getWeapons($charData['id']);
$recentBattles = $character->getRecentBattles($charData['id'], 10);
$opponents = $character->getRandomOpponents($charData['id'], 10);

// Obsługa akcji
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'battle_random':
                    $opponentId = intval($_POST['opponent_id'] ?? 0);
                    if ($charData['energy_points'] > 0) {
                        if ($character->useEnergyPoint($charData['id'])) {
                            $battleResult = $battle->initiate($charData['id'], $opponentId, 'random');
                            $_SESSION['last_battle'] = $battleResult;
                            header("Location: /battle.php?id=" . $battleResult['battle_id']);
                            exit;
                        }
                    } else {
                        $message = 'Nie masz punktów energii!';
                        $messageType = 'error';
                    }
                    break;
                    
                case 'battle_friend':
                    $friendId = intval($_POST['friend_id'] ?? 0);
                    if ($charData['challenge_points'] > 0) {
                        if ($character->useChallengePoint($charData['id'])) {
                            $battleResult = $battle->initiate($charData['id'], $friendId, 'challenge');
                            $_SESSION['last_battle'] = $battleResult;
                            header("Location: /battle.php?id=" . $battleResult['battle_id']);
                            exit;
                        }
                    } else {
                        $message = 'Nie masz punktów wyzwań!';
                        $messageType = 'error';
                    }
                    break;
                    
                case 'equip_weapon':
                    $weaponId = intval($_POST['weapon_id'] ?? 0);
                    try {
                        $character->equipWeapon($charData['id'], $weaponId);
                        $message = 'Broń została wyposażona!';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                        $messageType = 'error';
                    }
                    break;
                    
                case 'add_friend':
                    $friendId = intval($_POST['friend_id'] ?? 0);
                    try {
                        $character->addFriend($charData['id'], $friendId);
                        $message = 'Przeciwnik został dodany do znajomych!';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                        $messageType = 'error';
                    }
                    break;
                    
                case 'remove_friend':
                    $friendId = intval($_POST['friend_id'] ?? 0);
                    try {
                        $character->removeFriend($charData['id'], $friendId);
                        $message = 'Znajomy został usunięty!';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                        $messageType = 'error';
                    }
                    break;
                    
                case 'change_avatar':
                    $avatarPath = sanitizeInput($_POST['avatar_path'] ?? '');
                    try {
                        $character->changeAvatar($charData['id'], $avatarPath);
                        $message = 'Avatar został zmieniony!';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                        $messageType = 'error';
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
        
        // Odśwież dane po akcji
        $charData = $character->getByHashes($hash1, $hash2);
        $traits = $character->getTraits($charData['id']);
        $friends = $character->getFriends($charData['id']);
        $weapons = $character->getWeapons($charData['id']);
        $recentBattles = $character->getRecentBattles($charData['id'], 10);
        $opponents = $character->getRandomOpponents($charData['id'], 10);
    }
}

// Zapisz dane postaci w ciasteczku dla następnych wizyt
setCharacterCookie($charData);

// Pobierz dostępne avatary dla zmiany
$availableAvatars = $character->getAvailableAvatars($charData['gender']);

// Sprawdź komunikaty flash
$flashMessage = getFlashMessage();
if ($flashMessage && empty($message)) {
    $message = $flashMessage['message'];
    $messageType = $flashMessage['type'];
}

// Przypisz zmienne do szablonu
$smarty->assign('character', $charData);
$smarty->assign('traits', $traits);
$smarty->assign('friends', $friends);
$smarty->assign('weapons', $weapons);
$smarty->assign('recent_battles', $recentBattles);
$smarty->assign('opponents', $opponents);
$smarty->assign('available_avatars', $availableAvatars);
$smarty->assign('message', $message);
$smarty->assign('message_type', $messageType);
$smarty->assign('is_mobile', isMobile());
$smarty->assign('site_url', defined('SITE_URL') ? SITE_URL : '');

$smarty->display('profile.tpl');
?>