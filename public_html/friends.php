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

// Pobierz aktualnych znajomych z avatarami
$db = Database::getInstance();
$currentFriends = $db->fetchAll("
    SELECT c.id, c.name, c.level, c.gender, c.avatar_image, c.last_login,
           cf.added_at as friendship_date,
           CASE WHEN c.last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 1 ELSE 0 END as is_online
    FROM characters c 
    JOIN character_friends cf ON c.id = cf.friend_id 
    WHERE cf.character_id = ? 
    ORDER BY c.last_login DESC
", [$charData['id']]);

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
                    // Wyszukaj graczy po nazwie z avatarami
                    $searchResults = $db->fetchAll("
                        SELECT c.id, c.name, c.level, c.gender, c.avatar_image, c.last_login,
                               CASE WHEN c.last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 1 ELSE 0 END as is_online
                        FROM characters c
                        WHERE c.name LIKE ? AND c.id != ? AND c.status = 'active'
                        ORDER BY c.last_login DESC
                        LIMIT 20
                    ", ['%' . $searchQuery . '%', $charData['id']]);
                    
                    if (empty($searchResults)) {
                        $message = 'Nie znaleziono graczy o imieniu "' . htmlspecialchars($searchQuery) . '".';
                        $messageType = 'info';
                    } else {
                        // Dodaj informacje o statusie znajomości
                        foreach ($searchResults as &$result) {
                            $result['is_friend'] = in_array($result['id'], $friendIds);
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
                
                $friendName = $db->fetchOne("SELECT name FROM characters WHERE id = ?", [$friendId])['name'];
                $message = "Gracz {$friendName} został dodany do znajomych!";
                $messageType = 'success';
                
                // Odśwież listę znajomych
                $currentFriends = $db->fetchAll("
                    SELECT c.id, c.name, c.level, c.gender, c.avatar_image, c.last_login,
                           cf.added_at as friendship_date,
                           CASE WHEN c.last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 1 ELSE 0 END as is_online
                    FROM characters c 
                    JOIN character_friends cf ON c.id = cf.friend_id 
                    WHERE cf.character_id = ? 
                    ORDER BY c.last_login DESC
                ", [$charData['id']]);
                $friendIds = array_column($currentFriends, 'id');
                break;
                
            case 'remove_friend':
                $friendId = intval($_POST['friend_id'] ?? 0);
                $friendName = $db->fetchOne("SELECT name FROM characters WHERE id = ?", [$friendId])['name'];
                $character->removeFriend($charData['id'], $friendId);
                
                $message = "Gracz {$friendName} został usunięty ze znajomych.";
                $messageType = 'success';
                
                // Odśwież listę znajomych
                $currentFriends = $db->fetchAll("
                    SELECT c.id, c.name, c.level, c.gender, c.avatar_image, c.last_login,
                           cf.added_at as friendship_date,
                           CASE WHEN c.last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) THEN 1 ELSE 0 END as is_online
                    FROM characters c 
                    JOIN character_friends cf ON c.id = cf.friend_id 
                    WHERE cf.character_id = ? 
                    ORDER BY c.last_login DESC
                ", [$charData['id']]);
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
$smarty->assign('site_url', defined('SITE_URL') ? SITE_URL : '');

$smarty->display('friends.tpl');
?>