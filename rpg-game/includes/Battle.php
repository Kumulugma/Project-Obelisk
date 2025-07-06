<?php
class Battle {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function initiate($attackerId, $defenderId, $battleType = 'random') {
        $attacker = $this->getCharacterForBattle($attackerId);
        $defender = $this->getCharacterForBattle($defenderId);
        
        if (!$attacker || !$defender) {
            throw new Exception("Nie można znaleźć jednej z postaci.");
        }
        
        $battleResult = $this->simulateBattle($attacker, $defender);
        $battleId = $this->saveBattle($attackerId, $defenderId, $battleResult, $battleType);
        $this->grantRewards($battleResult, $battleId);
        
        return [
            'battle_id' => $battleId,
            'winner' => $battleResult['winner'],
            'battle_log' => $battleResult['battle_log'],
            'experience_gained' => $battleResult['experience_gained'],
            'weapon_dropped' => $battleResult['weapon_dropped'],
            'trait_dropped' => $battleResult['trait_dropped']
        ];
    }
    
    private function simulateBattle($attacker, $defender) {
        $battleLog = [];
        $attackerStats = $this->prepareCharacterStats($attacker);
        $defenderStats = $this->prepareCharacterStats($defender);
        
        $attackerTraits = $this->getCharacterTraits($attacker['id']);
        $defenderTraits = $this->getCharacterTraits($defender['id']);
        
        $attackerStats = $this->applyPassiveTraits($attackerStats, $attackerTraits);
        $defenderStats = $this->applyPassiveTraits($defenderStats, $defenderTraits);
        
        $round = 1;
        $activeEffects = ['attacker' => [], 'defender' => []];
        
        if (!empty($attackerTraits)) {
            $passiveTraits = array_filter($attackerTraits, function($t) { return $t['type'] === 'passive'; });
            if (!empty($passiveTraits)) {
                $traitNames = array_map(function($t) { return $t['name']; }, $passiveTraits);
                $battleLog[] = [
                    'round' => 0,
                    'type' => 'passive_traits',
                    'character' => $attacker['name'],
                    'message' => 'Aktywne traity: ' . implode(', ', $traitNames)
                ];
            }
        }
        
        if (!empty($defenderTraits)) {
            $passiveTraits = array_filter($defenderTraits, function($t) { return $t['type'] === 'passive'; });
            if (!empty($passiveTraits)) {
                $traitNames = array_map(function($t) { return $t['name']; }, $passiveTraits);
                $battleLog[] = [
                    'round' => 0,
                    'type' => 'passive_traits',
                    'character' => $defender['name'],
                    'message' => 'Aktywne traity: ' . implode(', ', $traitNames)
                ];
            }
        }
        
        while ($attackerStats['health'] > 0 && $defenderStats['health'] > 0 && $round <= 30) {
            $attackResult = $this->performAttack($attackerStats, $defenderStats, $attackerTraits, $round);
            if ($attackResult['damage'] > 0) {
                $defenderStats['health'] -= $attackResult['damage'];
                $defenderStats['armor'] = max(0, $defenderStats['armor'] - $attackResult['armor_damage']);
            }
            $battleLog[] = $attackResult['log'];
            
            if ($defenderStats['health'] <= 0) break;
            
            $defenseResult = $this->performAttack($defenderStats, $attackerStats, $defenderTraits, $round);
            if ($defenseResult['damage'] > 0) {
                $attackerStats['health'] -= $defenseResult['damage'];
                $attackerStats['armor'] = max(0, $attackerStats['armor'] - $defenseResult['armor_damage']);
            }
            $battleLog[] = $defenseResult['log'];
            
            $attackerStats['stamina'] = max(0, $attackerStats['stamina'] - 2);
            $defenderStats['stamina'] = max(0, $defenderStats['stamina'] - 2);
            
            if ($attackerStats['stamina'] <= 0) {
                $attackerStats['health'] = max(1, floor($attackerStats['health'] / 2));
                $attackerStats['stamina'] = max(3, floor($attackerStats['max_stamina'] / 2));
                $battleLog[] = [
                    'round' => $round,
                    'type' => 'exhaustion',
                    'character' => $attacker['name'],
                    'message' => 'Wyczerpanie! Zdrowie i wytrzymałość spadają!'
                ];
            }
            
            if ($defenderStats['stamina'] <= 0) {
                $defenderStats['health'] = max(1, floor($defenderStats['health'] / 2));
                $defenderStats['stamina'] = max(3, floor($defenderStats['max_stamina'] / 2));
                $battleLog[] = [
                    'round' => $round,
                    'type' => 'exhaustion',
                    'character' => $defender['name'],
                    'message' => 'Wyczerpanie! Zdrowie i wytrzymałość spadają!'
                ];
            }
            
            $round++;
        }
        
        $winner = $attackerStats['health'] > 0 ? $attacker : $defender;
        $loser = $winner['id'] == $attacker['id'] ? $defender : $attacker;
        
        $experienceGained = $winner['id'] == $attacker['id'] ? $defender['level'] : floor($attacker['level'] / 2);
        
        $weaponDropped = $this->checkWeaponDrop();
        $traitDropped = $this->checkTraitDrop();
        
        return [
            'winner' => $winner,
            'loser' => $loser,
            'battle_log' => $battleLog,
            'experience_gained' => $experienceGained,
            'weapon_dropped' => $weaponDropped,
            'trait_dropped' => $traitDropped
        ];
    }
    
    private function calculateHitChance($attackerDexterity, $defenderAgility) {
        $baseChance = 0.5;
        $difference = $attackerDexterity - $defenderAgility;
        $modifier = $difference * 0.05;
        
        $hitChance = $baseChance + $modifier;
        
        return max(0.1, min(0.9, $hitChance));
    }
    
    private function performAttack($attacker, $defender, $traits, $round) {
        $hitChance = $this->calculateHitChance($attacker['dexterity'], $defender['agility']);
        $hit = (mt_rand() / mt_getrandmax()) < $hitChance;
        
        $damage = 0;
        $action = '';
        $newEffects = [];
        $traitsActivated = [];
        
        $defenderTraits = $this->getCharacterTraits($defender['id']);
        foreach ($defenderTraits as $trait) {
            if ($trait['name'] === 'Parowanie' && $trait['type'] === 'active') {
                if ((mt_rand() / mt_getrandmax()) < $trait['trigger_chance']) {
                    return [
                        'defender' => $defender,
                        'action' => 'Atak sparowany!',
                        'damage' => 0,
                        'armor_damage' => 0,
                        'log' => [
                            'round' => $round,
                            'type' => 'parry',
                            'character' => $defender['name'],
                            'message' => 'Sparował atak!'
                        ]
                    ];
                }
            }
        }
        
        if ($hit) {
            $baseDamage = $attacker['damage'] + ($attacker['weapon_damage'] ?? 0);
            $damage = max(1, $baseDamage - $defender['armor']);
            
            $action = "Zadał {$damage} obrażeń";
            
            foreach ($traits as $trait) {
                if ($trait['type'] === 'active' && (mt_rand() / mt_getrandmax()) < $trait['trigger_chance']) {
                    switch ($trait['name']) {
                        case 'Krytyczne Uderzenie':
                            $damage = floor($damage * 1.5);
                            $action .= " (krytyk!)";
                            $traitsActivated[] = $trait['name'];
                            break;
                        case 'Przebicie Pancerza':
                            $damage += floor($defender['armor'] * 0.5);
                            $action .= " (przebicie!)";
                            $traitsActivated[] = $trait['name'];
                            break;
                    }
                }
            }
        } else {
            $action = "Chybił";
        }
        
        return [
            'attacker' => $attacker,
            'damage' => $damage,
            'armor_damage' => 0,
            'log' => [
                'round' => $round,
                'type' => $hit ? 'hit' : 'miss',
                'character' => $attacker['name'],
                'message' => $action,
                'traits_activated' => $traitsActivated
            ]
        ];
    }
    
    private function prepareCharacterStats($character) {
        return [
            'health' => $character['health'],
            'max_health' => $character['max_health'],
            'stamina' => $character['stamina'],
            'max_stamina' => $character['max_stamina'],
            'damage' => $character['damage'],
            'armor' => $character['armor'],
            'dexterity' => $character['dexterity'],
            'agility' => $character['agility'],
            'weapon_damage' => $character['weapon_damage'] ?? 0
        ];
    }
    
    private function applyPassiveTraits($stats, $traits) {
        foreach ($traits as $trait) {
            if ($trait['type'] === 'passive') {
                switch ($trait['name']) {
                    case 'Wzmocnienie':
                        $stats['damage'] += 2;
                        break;
                    case 'Pancerz':
                        $stats['armor'] += 3;
                        break;
                    case 'Szybkość':
                        $stats['agility'] += 2;
                        break;
                    case 'Precyzja':
                        $stats['dexterity'] += 2;
                        break;
                    case 'Wytrzymałość':
                        $stats['max_health'] += 10;
                        $stats['health'] = min($stats['health'] + 10, $stats['max_health']);
                        break;
                }
            }
        }
        return $stats;
    }
    
    private function getCharacterTraits($characterId) {
        $sql = "SELECT t.* 
                FROM traits t 
                JOIN character_traits ct ON t.id = ct.trait_id 
                WHERE ct.character_id = ?";
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
    private function getCharacterForBattle($characterId) {
        $sql = "SELECT c.*, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.id = ?";
        return $this->db->fetchOne($sql, [$characterId]);
    }
    
    // ZMIENIONE: Dodano modifier dla szans na drop
    private function checkWeaponDrop($modifier = 1.0) {
        $weapons = $this->db->fetchAll("SELECT id, drop_chance FROM weapons WHERE id > 1");
        
        foreach ($weapons as $weapon) {
            $adjustedChance = $weapon['drop_chance'] * $modifier;
            if ((mt_rand() / mt_getrandmax()) < $adjustedChance) {
                return $weapon['id'];
            }
        }
        
        return null;
    }
    
    // ZMIENIONE: Dodano modifier dla szans na drop
    private function checkTraitDrop($modifier = 1.0) {
        $traits = $this->db->fetchAll("SELECT id, drop_chance FROM traits");
        
        foreach ($traits as $trait) {
            $adjustedChance = $trait['drop_chance'] * $modifier;
            if ((mt_rand() / mt_getrandmax()) < $adjustedChance) {
                return $trait['id'];
            }
        }
        
        return null;
    }
    
    private function saveBattle($attackerId, $defenderId, $battleResult, $battleType) {
        $sql = "INSERT INTO battles (attacker_id, defender_id, winner_id, battle_log, experience_gained, weapon_dropped, trait_dropped, battle_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $attackerId,
            $defenderId,
            $battleResult['winner']['id'],
            json_encode($battleResult['battle_log']),
            $battleResult['experience_gained'],
            $battleResult['weapon_dropped'],
            $battleResult['trait_dropped'],
            $battleType
        ]);
        
        return $this->db->lastInsertId();
    }
    
    // ZMIENIONE: Sprawiedliwy system nagród
    private function grantRewards($battleResult, $battleId) {
        $character = new Character();
        $winnerId = $battleResult['winner']['id'];
        $loserId = $battleResult['loser']['id'];
        
        // Doświadczenie tylko dla zwycięzcy
        $character->addExperience($winnerId, $battleResult['experience_gained']);
        
        // SYSTEM NAGRÓD DLA OBUDWU GRACZY
        
        // 1. Nagrody podstawowe (te które wypadły w walce)
        if ($battleResult['weapon_dropped']) {
            // Zwycięzca zawsze dostaje broń
            $sql = "INSERT INTO character_weapons (character_id, weapon_id) VALUES (?, ?)";
            $this->db->query($sql, [$winnerId, $battleResult['weapon_dropped']]);
            
            // Przegrywający ma 30% szansy na tę samą broń
            if (mt_rand(1, 100) <= 30) {
                $this->db->query($sql, [$loserId, $battleResult['weapon_dropped']]);
            }
        }
        
        if ($battleResult['trait_dropped']) {
            // Zwycięzca zawsze dostaje trait
            $character->addTrait($winnerId, $battleResult['trait_dropped']);
            
            // Przegrywający ma 25% szansy na ten sam trait
            if (mt_rand(1, 100) <= 25) {
                $character->addTrait($loserId, $battleResult['trait_dropped']);
            }
        }
        
        // 2. Dodatkowe szanse na nagrody dla przegrywającego
        $loserWeaponDrop = $this->checkWeaponDrop(0.3); // 30% standardowej szansy
        $loserTraitDrop = $this->checkTraitDrop(0.25);  // 25% standardowej szansy
        
        if ($loserWeaponDrop && $loserWeaponDrop != $battleResult['weapon_dropped']) {
            $sql = "INSERT INTO character_weapons (character_id, weapon_id) VALUES (?, ?)";
            $this->db->query($sql, [$loserId, $loserWeaponDrop]);
        }
        
        if ($loserTraitDrop && $loserTraitDrop != $battleResult['trait_dropped']) {
            $character->addTrait($loserId, $loserTraitDrop);
        }
        
        // 3. Bonus za uczestnictwo (mała szansa na dodatkowe nagrody dla zwycięzcy)
        if (mt_rand(1, 100) <= 15) { // 15% szansy na bonus
            $bonusWeapon = $this->checkWeaponDrop(0.5);
            if ($bonusWeapon && $bonusWeapon != $battleResult['weapon_dropped']) {
                $sql = "INSERT INTO character_weapons (character_id, weapon_id) VALUES (?, ?)";
                $this->db->query($sql, [$winnerId, $bonusWeapon]);
            }
        }
        
        if (mt_rand(1, 100) <= 12) { // 12% szansy na bonus trait
            $bonusTrait = $this->checkTraitDrop(0.4);
            if ($bonusTrait && $bonusTrait != $battleResult['trait_dropped']) {
                $character->addTrait($winnerId, $bonusTrait);
            }
        }
    }
    
    public function getBattleDetails($battleId) {
        $sql = "SELECT b.*, 
                       a.name as attacker_name, 
                       d.name as defender_name,
                       w.name as winner_name,
                       wd.name as weapon_name,
                       t.name as trait_name
                FROM battles b
                JOIN characters a ON b.attacker_id = a.id
                JOIN characters d ON b.defender_id = d.id
                LEFT JOIN characters w ON b.winner_id = w.id
                LEFT JOIN weapons wd ON b.weapon_dropped = wd.id
                LEFT JOIN traits t ON b.trait_dropped = t.id
                WHERE b.id = ?";
        
        $battle = $this->db->fetchOne($sql, [$battleId]);
        if ($battle) {
            $battle['battle_log'] = json_decode($battle['battle_log'], true);
        }
        
        return $battle;
    }
}
?>