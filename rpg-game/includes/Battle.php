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
        
        while ($attackerStats['health'] > 0 && $defenderStats['health'] > 0 && $round <= 100) {
            $activeEffects = $this->applyActiveEffects($activeEffects, $attackerStats, $defenderStats, $battleLog, $round);
            
            if ($attackerStats['health'] > 0) {
                $result = $this->performAttack($attackerStats, $defenderStats, $attackerTraits, $round);
                $defenderStats = $result['defender'];
                $battleLog[] = [
                    'round' => $round,
                    'type' => 'attack',
                    'attacker' => $attacker['name'],
                    'defender' => $defender['name'],
                    'action' => $result['action'],
                    'damage' => $result['damage'],
                    'defender_health' => $defenderStats['health'],
                    'defender_armor' => $defenderStats['armor'],
                    'traits_activated' => $result['traits_activated']
                ];
                
                $activeEffects['defender'] = array_merge($activeEffects['defender'], $result['new_effects']);
            }
            
            if ($defenderStats['health'] > 0) {
                $result = $this->performAttack($defenderStats, $attackerStats, $defenderTraits, $round);
                $attackerStats = $result['defender'];
                $battleLog[] = [
                    'round' => $round,
                    'type' => 'attack',
                    'attacker' => $defender['name'],
                    'defender' => $attacker['name'],
                    'action' => $result['action'],
                    'damage' => $result['damage'],
                    'defender_health' => $attackerStats['health'],
                    'defender_armor' => $attackerStats['armor'],
                    'traits_activated' => $result['traits_activated']
                ];
                
                $activeEffects['attacker'] = array_merge($activeEffects['attacker'], $result['new_effects']);
            }
            
            $attackerStats['stamina']--;
            $defenderStats['stamina']--;
            
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
                        'new_effects' => [],
                        'traits_activated' => [['name' => 'Parowanie', 'image' => $trait['image_path']]]
                    ];
                }
            }
        }
        
        if ($hit) {
            $damage = $attacker['damage'];
            
            foreach ($traits as $trait) {
                if ($trait['type'] === 'active' && (mt_rand() / mt_getrandmax()) < $trait['trigger_chance']) {
                    $traitsActivated[] = [
                        'name' => $trait['name'],
                        'image' => $trait['image_path'],
                        'description' => $trait['description']
                    ];
                    
                    switch ($trait['effect_type']) {
                        case 'critical_hit':
                            $damage *= $trait['effect_value'];
                            $action .= ' [Krytyczny Cios!]';
                            break;
                            
                        case 'burn':
                            $newEffects[] = [
                                'type' => 'burn',
                                'damage' => $trait['effect_value'],
                                'duration' => $trait['effect_duration'] ?: 3,
                                'name' => $trait['name']
                            ];
                            $action .= ' [Podpalenie!]';
                            break;
                            
                        case 'heal':
                            $attacker['health'] = min($attacker['max_health'], $attacker['health'] + $trait['effect_value']);
                            $action .= ' [Regeneracja +' . $trait['effect_value'] . ' HP!]';
                            break;
                    }
                }
            }
            
            $originalDamage = $damage;
            $armorPenetration = $attacker['armor_penetration'] ?: 0;
            $effectiveArmor = max(0, $defender['armor'] - $armorPenetration);
            
            if ($effectiveArmor > 0) {
                $armorDamage = min($damage, $effectiveArmor);
                $defender['armor'] -= $armorDamage;
                $damage -= $armorDamage;
            }
            
            if ($damage > 0) {
                $defender['health'] = max(0, $defender['health'] - $damage);
            }
            
            $action = $action ?: "Trafienie za {$originalDamage} obrażeń";
            if ($armorPenetration > 0) {
                $action .= " (przebicie: {$armorPenetration})";
            }
        } else {
            $action = 'Chybienie';
        }
        
        return [
            'defender' => $defender,
            'action' => $action,
            'damage' => $originalDamage,
            'new_effects' => $newEffects,
            'traits_activated' => $traitsActivated
        ];
    }
    
    private function applyActiveEffects($activeEffects, &$attackerStats, &$defenderStats, &$battleLog, $round) {
        foreach ($activeEffects['attacker'] as $key => $effect) {
            if ($effect['type'] === 'burn') {
                $attackerStats['health'] = max(0, $attackerStats['health'] - $effect['damage']);
                $battleLog[] = [
                    'round' => $round,
                    'type' => 'effect',
                    'character' => 'Atakujący',
                    'message' => $effect['name'] . ' zadaje ' . $effect['damage'] . ' obrażeń!'
                ];
                
                $activeEffects['attacker'][$key]['duration']--;
                if ($activeEffects['attacker'][$key]['duration'] <= 0) {
                    unset($activeEffects['attacker'][$key]);
                }
            }
        }
        
        foreach ($activeEffects['defender'] as $key => $effect) {
            if ($effect['type'] === 'burn') {
                $defenderStats['health'] = max(0, $defenderStats['health'] - $effect['damage']);
                $battleLog[] = [
                    'round' => $round,
                    'type' => 'effect',
                    'character' => 'Obrońca',
                    'message' => $effect['name'] . ' zadaje ' . $effect['damage'] . ' obrażeń!'
                ];
                
                $activeEffects['defender'][$key]['duration']--;
                if ($activeEffects['defender'][$key]['duration'] <= 0) {
                    unset($activeEffects['defender'][$key]);
                }
            }
        }
        
        return $activeEffects;
    }
    
    private function prepareCharacterStats($character) {
        return [
            'id' => $character['id'],
            'health' => $character['health'],
            'max_health' => $character['max_health'],
            'stamina' => $character['stamina'],
            'max_stamina' => $character['max_stamina'],
            'damage' => $character['damage'] + ($character['weapon_damage'] ?: 0),
            'dexterity' => $character['dexterity'],
            'agility' => $character['agility'],
            'armor' => $character['armor'],
            'max_armor' => $character['max_armor'],
            'armor_penetration' => ($character['weapon_penetration'] ?: 0) + ($character['armor_penetration'] ?: 0)
        ];
    }
    
    private function applyPassiveTraits($stats, $traits) {
        foreach ($traits as $trait) {
            if ($trait['type'] === 'passive') {
                switch ($trait['effect_type']) {
                    case 'damage_boost':
                        $stats['damage'] += $trait['effect_value'];
                        break;
                    case 'agility_boost':
                        $stats['agility'] += $trait['effect_value'];
                        break;
                    case 'armor_boost':
                        $stats['armor'] += $trait['effect_value'];
                        $stats['max_armor'] += $trait['effect_value'];
                        break;
                    case 'dexterity_boost':
                        $stats['dexterity'] += $trait['effect_value'];
                        break;
                    case 'stamina_boost':
                        $stats['stamina'] += $trait['effect_value'];
                        $stats['max_stamina'] += $trait['effect_value'];
                        break;
                    case 'penetration_boost':
                        $stats['armor_penetration'] += $trait['effect_value'];
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
    
    private function checkWeaponDrop() {
        $weapons = $this->db->fetchAll("SELECT id, drop_chance FROM weapons WHERE id > 1");
        
        foreach ($weapons as $weapon) {
            if ((mt_rand() / mt_getrandmax()) < $weapon['drop_chance']) {
                return $weapon['id'];
            }
        }
        
        return null;
    }
    
    private function checkTraitDrop() {
        $traits = $this->db->fetchAll("SELECT id, drop_chance FROM traits");
        
        foreach ($traits as $trait) {
            if ((mt_rand() / mt_getrandmax()) < $trait['drop_chance']) {
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
    
    private function grantRewards($battleResult, $battleId) {
        $character = new Character();
        $winnerId = $battleResult['winner']['id'];
        
        $character->addExperience($winnerId, $battleResult['experience_gained']);
        
        if ($battleResult['weapon_dropped']) {
            $sql = "INSERT INTO character_weapons (character_id, weapon_id) VALUES (?, ?)";
            $this->db->query($sql, [$winnerId, $battleResult['weapon_dropped']]);
        }
        
        if ($battleResult['trait_dropped']) {
            $character->addTrait($winnerId, $battleResult['trait_dropped']);
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