<?php
session_start();
require_once '../rpg-game/includes/config.php';
require_once '../rpg-game/includes/database.php';
require_once '../rpg-game/includes/Battle.php';
require_once '../rpg-game/includes/character_includes.php';
require_once '../rpg-game/includes/functions.php';
require_once '../rpg-game/vendor/autoload.php';

$smarty = new Smarty();
$smarty->setTemplateDir(TEMPLATES_DIR);
$smarty->setCompileDir(TEMPLATES_C_DIR);
$smarty->setCacheDir(CACHE_DIR);

$battleId = intval($_GET['id'] ?? 0);
if (!$battleId) {
    header('Location: /');
    exit;
}

$battle = new Battle();
$battleData = $battle->getBattleDetails($battleId);

if (!$battleData) {
    header('Location: /');
    exit;
}

$db = Database::getInstance();

// Pobierz dodatkowe dane o wojownikach (avatary, statystyki)
$attackerData = $db->fetchOne("
    SELECT c.avatar_image, c.damage, c.dexterity, c.agility, c.armor, w.name as weapon_name
    FROM characters c 
    LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
    WHERE c.id = ?
", [$battleData['attacker_id']]);

$defenderData = $db->fetchOne("
    SELECT c.avatar_image, c.damage, c.dexterity, c.agility, c.armor, w.name as weapon_name
    FROM characters c 
    LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
    WHERE c.id = ?
", [$battleData['defender_id']]);

// Dodaj avatary do danych walki
$battleData['attacker_avatar'] = $attackerData['avatar_image'] ?? '/images/avatars/default.png';
$battleData['defender_avatar'] = $defenderData['avatar_image'] ?? '/images/avatars/default.png';
$battleData['attacker_damage'] = $attackerData['damage'] ?? 0;
$battleData['defender_armor'] = $defenderData['armor'] ?? 0;
$battleData['attacker_weapon'] = $attackerData['weapon_name'] ?? 'Pięść';
$battleData['defender_weapon'] = $defenderData['weapon_name'] ?? 'Pięść';

// Pobierz informacje o nagrodach
$weaponInfo = null;
$traitInfo = null;

if ($battleData['weapon_dropped']) {
    $weaponInfo = $db->fetchOne("SELECT * FROM weapons WHERE id = ?", [$battleData['weapon_dropped']]);
}

if ($battleData['trait_dropped']) {
    $traitInfo = $db->fetchOne("SELECT * FROM traits WHERE id = ?", [$battleData['trait_dropped']]);
}

// Debugowanie - sprawdź co otrzymujemy
error_log("Raw battle_log: " . print_r($battleData['battle_log'], true));

// Parsuj log walki jeśli jest w JSON
if (is_string($battleData['battle_log'])) {
    $decoded = json_decode($battleData['battle_log'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $battleData['battle_log'] = $decoded;
    } else {
        // Może to jest serializowane?
        $unserialized = @unserialize($battleData['battle_log']);
        if ($unserialized !== false) {
            $battleData['battle_log'] = $unserialized;
        } else {
            // Ostatnia próba - może to jest zwykły tekst?
            $battleData['battle_log'] = [
                [
                    'type' => 'info',
                    'message' => $battleData['battle_log'],
                    'round' => 1
                ]
            ];
        }
    }
}

// Upewnij się, że battle_log to tablica
if (!is_array($battleData['battle_log'])) {
    $battleData['battle_log'] = [];
}

// Jeśli log jest pusty, stwórz podstawowy log
if (empty($battleData['battle_log'])) {
    $battleData['battle_log'] = [
        [
            'type' => 'info',
            'message' => 'Rozpoczęcie walki',
            'round' => 0
        ],
        [
            'type' => 'attack',
            'round' => 1,
            'attacker' => $battleData['attacker_name'],
            'defender' => $battleData['defender_name'],
            'action' => 'Walka w toku...',
            'damage' => 0,
            'defender_health' => '?',
            'defender_armor' => '?'
        ]
    ];
    
    // Dodaj informację o zwycięzcy
    if ($battleData['winner_name']) {
        $battleData['battle_log'][] = [
            'type' => 'result',
            'message' => "Zwycięzca: " . $battleData['winner_name'],
            'round' => 'Final'
        ];
    }
}

// Przetwórz log walki dla lepszego wyświetlania
foreach ($battleData['battle_log'] as &$logEntry) {
    // Konwertuj różne formaty logów
    if (is_string($logEntry)) {
        // Jeśli to zwykły string, przekształć w strukturę
        $logEntry = [
            'type' => 'info',
            'message' => $logEntry,
            'round' => '?'
        ];
    }
    
    // Dodaj typ jeśli nie ma
    if (!isset($logEntry['type'])) {
        if (isset($logEntry['round']) && is_numeric($logEntry['round'])) {
            $logEntry['type'] = 'attack';
        } elseif (isset($logEntry['attacker']) && isset($logEntry['defender'])) {
            $logEntry['type'] = 'attack';
        } elseif (strpos($logEntry['message'] ?? '', 'trait') !== false || strpos($logEntry['message'] ?? '', 'Aktywne') !== false) {
            $logEntry['type'] = 'passive_traits';
        } elseif (strpos($logEntry['message'] ?? '', 'Wyczerpanie') !== false || strpos($logEntry['message'] ?? '', 'wyczerpanie') !== false) {
            $logEntry['type'] = 'exhaustion';
        } else {
            $logEntry['type'] = 'effect';
        }
    }
    
// Przetwórz log walki dla lepszego wyświetlania
foreach ($battleData['battle_log'] as &$logEntry) {
    // Konwertuj różne formaty logów
    if (is_string($logEntry)) {
        // Jeśli to zwykły string, przekształć w strukturę
        $logEntry = [
            'type' => 'info',
            'message' => $logEntry,
            'round' => '?'
        ];
    }
    
    // Dodaj typ jeśli nie ma
    if (!isset($logEntry['type'])) {
        if (isset($logEntry['round']) && is_numeric($logEntry['round'])) {
            $logEntry['type'] = 'attack';
        } elseif (isset($logEntry['attacker']) && isset($logEntry['defender'])) {
            $logEntry['type'] = 'attack';
        } elseif (strpos($logEntry['message'] ?? '', 'trait') !== false || strpos($logEntry['message'] ?? '', 'Aktywne') !== false) {
            $logEntry['type'] = 'passive_traits';
        } elseif (strpos($logEntry['message'] ?? '', 'Wyczerpanie') !== false || strpos($logEntry['message'] ?? '', 'wyczerpanie') !== false) {
            $logEntry['type'] = 'exhaustion';
        } else {
            $logEntry['type'] = 'effect';
        }
    }
    
    // NAPRAWKA: Dodaj nazwy wojowników jeśli brakuje
    if ($logEntry['type'] === 'attack') {
        // Jeśli brak nazwisk, dodaj z danych walki
        if (empty($logEntry['attacker']) || $logEntry['attacker'] === 'Nieznany') {
            $logEntry['attacker'] = $battleData['attacker_name'];
        }
        if (empty($logEntry['defender']) || $logEntry['defender'] === 'Nieznany') {
            $logEntry['defender'] = $battleData['defender_name'];
        }
        
        // Upewnij się że mamy action
        if (!isset($logEntry['action']) && isset($logEntry['damage'])) {
            if ($logEntry['damage'] > 0) {
                $logEntry['action'] = "Zadał {$logEntry['damage']} obrażeń";
            } else {
                $logEntry['action'] = "Chybił";
            }
        }
        
        // Dodaj domyślne wartości
        $logEntry['damage'] = $logEntry['damage'] ?? 0;
        $logEntry['defender_health'] = $logEntry['defender_health'] ?? '?';
        $logEntry['defender_armor'] = $logEntry['defender_armor'] ?? '?';
        $logEntry['action'] = $logEntry['action'] ?? 'Atak';
    }
    
    // Dla innych typów - dodaj character jeśli nie ma
    if (!isset($logEntry['character']) && isset($logEntry['message'])) {
        if ($logEntry['type'] === 'passive_traits') {
            // Spróbuj określić kto aktywuje traity na podstawie kontekstu
            if (strpos($logEntry['message'], $battleData['attacker_name']) !== false) {
                $logEntry['character'] = $battleData['attacker_name'];
            } elseif (strpos($logEntry['message'], $battleData['defender_name']) !== false) {
                $logEntry['character'] = $battleData['defender_name'];
            } else {
                $logEntry['character'] = $battleData['attacker_name']; // domyślnie atakujący
            }
        } else {
            $logEntry['character'] = 'System';
        }
    }
    
    // Dodaj traits_activated jeśli nie ma
    $logEntry['traits_activated'] = $logEntry['traits_activated'] ?? [];
}
}

// Debug - pokaż przetworzone dane
error_log("Processed battle_log: " . print_r($battleData['battle_log'], true));

// Statystyki walki
$battleStats = [
    'total_rounds' => 0,
    'total_damage_dealt' => 0,
    'traits_used' => 0
];

// Oblicz statystyki z logu
foreach ($battleData['battle_log'] as $logEntry) {
    if ($logEntry['type'] === 'attack' && isset($logEntry['round'])) {
        $battleStats['total_rounds'] = max($battleStats['total_rounds'], $logEntry['round']);
        $battleStats['total_damage_dealt'] += $logEntry['damage'] ?? 0;
    }
    if (!empty($logEntry['traits_activated'])) {
        $battleStats['traits_used'] += count($logEntry['traits_activated']);
    }
}

// Przypisz zmienne do szablonu
$smarty->assign('battle', $battleData);
$smarty->assign('weapon_reward', $weaponInfo);
$smarty->assign('trait_reward', $traitInfo);
$smarty->assign('battle_stats', $battleStats);
$smarty->assign('is_mobile', isMobile());
$smarty->assign('site_url', defined('SITE_URL') ? SITE_URL : '');

$smarty->display('battle.tpl');
?>