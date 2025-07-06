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

// Pobierz hashe z URL-a
$hash1 = $_GET['hash1'] ?? '';
$hash2 = $_GET['hash2'] ?? '';

if (empty($hash1) || empty($hash2)) {
    header('Location: /');
    exit;
}

// Sprawdź czy postać istnieje
$charData = $character->getByHashes($hash1, $hash2);
if (!$charData) {
    header('Location: /');
    exit;
}

// Zmienne dla komunikatów i danych
$message = '';
$messageType = '';
$searchResults = [];
$searchQuery = '';
$currentFriends = [];

// Pobierz aktualnych znajomych
$currentFriends = $character->getFriends($charData['id']);
$friendIds = array_column($currentFriends, 'id');

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'search_friends':
                $searchQuery = sanitizeInput($_POST['search_query'] ?? '');
                
                if (strlen($searchQuery) < 2) {
                    $message = 'Wpisz co najmniej 2 znaki aby wyszukać graczy.';
                    $messageType = 'warning';
                } else {
                    $searchResults = $character->searchByName($searchQuery, $charData['id']);
                    
                    if (empty($searchResults)) {
                        $message = 'Nie znaleziono graczy o imieniu "' . htmlspecialchars($searchQuery) . '".';
                        $messageType = 'info';
                    } else {
                        // Dodaj informacje o statusie znajomości i online
                        foreach ($searchResults as &$result) {
                            $result['is_friend'] = in_array($result['id'], $friendIds);
                            $result['is_online'] = $character->isOnline($result['id']);
                            $result['last_seen'] = formatLastSeen($result['last_login']);
                        }
                        
                        $message = 'Znaleziono ' . count($searchResults) . ' graczy.';
                        $messageType = 'success';
                    }
                }
                break;
                
            case 'add_friend':
                $friendId = intval($_POST['friend_id'] ?? 0);
                $character->addFriend($charData['id'], $friendId);
                
                $friendName = $character->getById($friendId)['name'];
                $message = "Gracz {$friendName} został dodany do znajomych!";
                $messageType = 'success';
                
                // Odśwież listę znajomych
                $currentFriends = $character->getFriends($charData['id']);
                $friendIds = array_column($currentFriends, 'id');
                break;
                
            case 'remove_friend':
                $friendId = intval($_POST['friend_id'] ?? 0);
                $friendName = $character->getById($friendId)['name'];
                $character->removeFriend($charData['id'], $friendId);
                
                $message = "Gracz {$friendName} został usunięty ze znajomych.";
                $messageType = 'success';
                
                // Odśwież listę znajomych
                $currentFriends = $character->getFriends($charData['id']);
                $friendIds = array_column($currentFriends, 'id');
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Funkcja formatowania czasu ostatniego logowania
function formatLastSeen($lastLogin) {
    $diff = time() - strtotime($lastLogin);
    
    if ($diff < 1800) return 'Online';
    if ($diff < 3600) return 'Był(a) ' . floor($diff/60) . ' min temu';
    if ($diff < 86400) return 'Był(a) ' . floor($diff/3600) . ' godz temu';
    if ($diff < 604800) return 'Był(a) ' . floor($diff/86400) . ' dni temu';
    
    return 'Był(a) ponad tydzień temu';
}

// Pobierz statystyki znajomych
$maxFriends = getSystemSetting('max_friends', 50);
$friendsCount = count($currentFriends);
$onlineFriends = array_filter($currentFriends, function($friend) {
    return $friend['is_online'];
});

$smarty->assign('character', $charData);
$smarty->assign('message', $message);
$smarty->assign('message_type', $messageType);
$smarty->assign('search_query', $searchQuery);
$smarty->assign('search_results', $searchResults);
$smarty->assign('current_friends', $currentFriends);
$smarty->assign('friends_count', $friendsCount);
$smarty->assign('max_friends', $maxFriends);
$smarty->assign('online_friends_count', count($onlineFriends));
$smarty->assign('is_mobile', isMobile());
$smarty->assign('site_url', SITE_URL);

$smarty->display('friends.tpl');
?>