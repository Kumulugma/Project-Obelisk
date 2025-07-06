<?php
session_start();
require_once '../rpg-game/includes/config.php';
require_once '../rpg-game/includes/database.php';
require_once '../rpg-game/includes/Character.php';
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

// Sprawdź dostęp do postaci (czy nie została usunięta/odebrana)
$accessCheck = $character->checkCharacterAccess($hash1, $hash2);

if ($accessCheck['status'] !== 'active') {
    // Usuń ciasteczko jeśli postać została usunięta lub odebrana
    clearCharacterCookie();
    
    // Przekieruj z komunikatem
    $_SESSION['access_message'] = $accessCheck['message'];
    $_SESSION['access_type'] = $accessCheck['status'];
    header('Location: /');
    exit;
}

$charData = $accessCheck['character'];

// Zaktualizuj ostatnie logowanie i resetuj punkty
$character->updateLastLogin($charData['id']);
$character->resetDailyPoints($charData['id']);

// Pobierz zaktualizowane dane
$charData = $character->getByHashes($hash1, $hash2);

// Sprawdź czy ma ciasteczko (do wyświetlenia PIN-u)
$cookieData = getCharacterFromCookie();
$showPin = ($cookieData && $cookieData['pin'] === $charData['pin']);

// Pobierz dane do wyświetlenia - NAPRAWIONE: używamy istniejących metod
$traits = $character->getTraits($charData['id']);

// TYMCZASOWO: puste tablice dla znajomych (do póki nie naprawimy struktury bazy)
$friends = [];
try {
    // Spróbuj pobrać znajomych - jeśli tabela istnieje
    $friends = $character->getFriends($charData['id']);
} catch (Exception $e) {
    // Jeśli tabela nie istnieje, pozostaw pustą tablicę
    $friends = [];
}

$weapons = $character->getWeapons($charData['id']);
$recentBattles = $character->getRecentBattles($charData['id'], 10);
$opponents = $character->getRandomOpponents($charData['id'], 10);

// Pobierz sformatowane statystyki
$formattedStats = $character->formatCharacterStats($charData);

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
                    
                case 'add_friend':
                    $friendPin = sanitizeInput($_POST['friend_pin'] ?? '');
                    try {
                        // TYMCZASOWO: wyłączone do póki nie naprawimy bazy danych
                        throw new Exception('System znajomych jest tymczasowo wyłączony.');
                        // $character->addFriend($charData['id'], $friendPin);
                        // $message = 'Znajomy został dodany!';
                        // $messageType = 'success';
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                        $messageType = 'error';
                    }
                    break;
                    
                case 'remove_friend':
                    $friendId = intval($_POST['friend_id'] ?? 0);
                    try {
                        // TYMCZASOWO: wyłączone do póki nie naprawimy bazy danych
                        throw new Exception('System znajomych jest tymczasowo wyłączony.');
                        // $character->removeFriend($charData['id'], $friendId);
                        // $message = 'Znajomy został usunięty!';
                        // $messageType = 'success';
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
        
        // Bezpieczne odświeżenie znajomych
        try {
            $friends = $character->getFriends($charData['id']);
        } catch (Exception $e) {
            $friends = [];
        }
        
        $weapons = $character->getWeapons($charData['id']);
        $recentBattles = $character->getRecentBattles($charData['id'], 10);
        $opponents = $character->getRandomOpponents($charData['id'], 10);
        $formattedStats = $character->formatCharacterStats($charData);
    }
}

// Zapisz dane postaci w ciasteczku (tylko dla aktywnych postaci)
if ($charData['status'] === 'active') {
    setCharacterCookie($charData);
}

// Przypisz zmienne do szablonu
$smarty->assign('character', $charData);
$smarty->assign('formatted_stats', $formattedStats);
$smarty->assign('traits', $traits);
$smarty->assign('friends', $friends);
$smarty->assign('weapons', $weapons);
$smarty->assign('recent_battles', $recentBattles);
$smarty->assign('opponents', $opponents);
$smarty->assign('message', $message);
$smarty->assign('message_type', $messageType);
$smarty->assign('show_pin', $showPin);
$smarty->assign('is_mobile', isMobile());
$smarty->assign('site_url', SITE_URL);

$smarty->display('profile.tpl');
?>