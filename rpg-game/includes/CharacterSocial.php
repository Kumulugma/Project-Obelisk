<?php

/**
 * Klasa odpowiedzialna za aspekty społeczne postaci
 * Znajomi, cechy (traits), broń
 */
class CharacterSocial {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Dodaje znajomego
     */
    public function addFriend($characterId, $friendId) {
        // Sprawdź czy nie próbuje dodać siebie
        if ($characterId == $friendId) {
            throw new Exception("Nie możesz dodać siebie do znajomych.");
        }
        
        // Sprawdź czy już nie są znajomymi
        $existing = $this->db->fetchOne(
            "SELECT id FROM character_friends WHERE character_id = ? AND friend_id = ?",
            [$characterId, $friendId]
        );
        
        if ($existing) {
            throw new Exception("Ta postać jest już w Twoich znajomych.");
        }
        
        // Sprawdź limit znajomych
        $friendsCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM character_friends WHERE character_id = ?",
            [$characterId]
        )['count'];
        
        if ($friendsCount >= MAX_FRIENDS) {
            throw new Exception("Osiągnięto maksymalną liczbę znajomych.");
        }
        
        // Sprawdź czy postać istnieje
        $friendExists = $this->db->fetchOne("SELECT id FROM characters WHERE id = ?", [$friendId]);
        if (!$friendExists) {
            throw new Exception("Postać nie istnieje.");
        }
        
        $sql = "INSERT INTO character_friends (character_id, friend_id) VALUES (?, ?)";
        $this->db->query($sql, [$characterId, $friendId]);
    }
    
    /**
     * Usuwa znajomego
     */
    public function removeFriend($characterId, $friendId) {
        $sql = "DELETE FROM character_friends WHERE character_id = ? AND friend_id = ?";
        $result = $this->db->query($sql, [$characterId, $friendId]);
        
        if ($result->rowCount() == 0) {
            throw new Exception("Ta postać nie jest w Twoich znajomych.");
        }
    }
    
    /**
     * Pobiera listę znajomych
     */
    public function getFriends($characterId) {
        $sql = "SELECT c.*, cf.added_at 
                FROM characters c 
                JOIN character_friends cf ON c.id = cf.friend_id 
                WHERE cf.character_id = ? 
                ORDER BY cf.added_at DESC";
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
    /**
     * Sprawdza czy postacie są znajomymi
     */
    public function areFriends($characterId, $friendId) {
        $result = $this->db->fetchOne(
            "SELECT id FROM character_friends WHERE character_id = ? AND friend_id = ?",
            [$characterId, $friendId]
        );
        return $result !== false;
    }
    
    /**
     * Pobiera cechy postaci
     */
    public function getTraits($characterId) {
        $sql = "SELECT t.* 
                FROM traits t 
                JOIN character_traits ct ON t.id = ct.trait_id 
                WHERE ct.character_id = ?
                ORDER BY ct.obtained_at";
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
    /**
     * Dodaje cechę do postaci
     */
    public function addTrait($characterId, $traitId) {
        // Sprawdź czy już ma ten trait
        $existing = $this->db->fetchOne(
            "SELECT id FROM character_traits WHERE character_id = ? AND trait_id = ?",
            [$characterId, $traitId]
        );
        
        if ($existing) {
            return; // Już ma ten trait
        }
        
        $sql = "INSERT INTO character_traits (character_id, trait_id) VALUES (?, ?)";
        $this->db->query($sql, [$characterId, $traitId]);
    }
    
    /**
     * Usuwa cechę z postaci
     */
    public function removeTrait($characterId, $traitId) {
        $sql = "DELETE FROM character_traits WHERE character_id = ? AND trait_id = ?";
        $result = $this->db->query($sql, [$characterId, $traitId]);
        
        if ($result->rowCount() == 0) {
            throw new Exception("Postać nie posiada tej cechy.");
        }
    }
    
    /**
     * Sprawdza czy postać posiada określoną cechę
     */
    public function hasTrait($characterId, $traitId) {
        $result = $this->db->fetchOne(
            "SELECT id FROM character_traits WHERE character_id = ? AND trait_id = ?",
            [$characterId, $traitId]
        );
        return $result !== false;
    }
    
    /**
     * Dodaje broń do ekwipunku postaci
     */
    public function addWeapon($characterId, $weaponId) {
        // Sprawdź czy już ma tę broń
        $existing = $this->db->fetchOne(
            "SELECT id FROM character_weapons WHERE character_id = ? AND weapon_id = ?",
            [$characterId, $weaponId]
        );
        
        if ($existing) {
            return; // Już ma tę broń
        }
        
        $sql = "INSERT INTO character_weapons (character_id, weapon_id) VALUES (?, ?)";
        $this->db->query($sql, [$characterId, $weaponId]);
    }
    
    /**
     * Usuwa broń z ekwipunku
     */
    public function removeWeapon($characterId, $weaponId) {
        // Nie można usunąć pięści (id = 1)
        if ($weaponId == 1) {
            throw new Exception("Nie można usunąć pięści.");
        }
        
        // Jeśli ma założoną tę broń, zmień na pięść
        $character = $this->db->fetchOne("SELECT equipped_weapon_id FROM characters WHERE id = ?", [$characterId]);
        if ($character && $character['equipped_weapon_id'] == $weaponId) {
            $this->equipWeapon($characterId, 1); // Załóż pięść
        }
        
        $sql = "DELETE FROM character_weapons WHERE character_id = ? AND weapon_id = ?";
        $this->db->query($sql, [$characterId, $weaponId]);
    }
    
    /**
     * Zakłada broń
     */
    public function equipWeapon($characterId, $weaponId) {
        // Sprawdź czy postać ma tę broń
        if ($weaponId != 1) { // Pięść jest zawsze dostępna
            $hasWeapon = $this->db->fetchOne(
                "SELECT id FROM character_weapons WHERE character_id = ? AND weapon_id = ?",
                [$characterId, $weaponId]
            );
            
            if (!$hasWeapon) {
                throw new Exception("Nie posiadasz tej broni.");
            }
        }
        
        // Zaktualizuj postać
        $updateSql = "UPDATE characters SET equipped_weapon_id = ?";
        $updateParams = [$weaponId];
        
        if ($weaponId != 1) {
            $updateSql .= ", last_weapon_change = ?";
            $updateParams[] = date('Y-m-d');
        }
        
        $updateSql .= " WHERE id = ?";
        $updateParams[] = $characterId;
        
        $this->db->query($updateSql, $updateParams);
    }
    
    /**
     * Pobiera bronie postaci
     */
    public function getWeapons($characterId) {
        $sql = "SELECT w.*, cw.is_equipped, cw.obtained_at
                FROM weapons w
                JOIN character_weapons cw ON w.id = cw.weapon_id
                WHERE cw.character_id = ?
                ORDER BY cw.obtained_at DESC";
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
    /**
     * Pobiera wszystkie dostępne bronie
     */
    public function getAllWeapons() {
        $sql = "SELECT * FROM weapons ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Pobiera wszystkie dostępne cechy
     */
    public function getAllTraits() {
        $sql = "SELECT * FROM traits ORDER BY type, name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Sprawdza czy postać może dodać więcej znajomych
     */
    public function canAddMoreFriends($characterId) {
        $friendsCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM character_friends WHERE character_id = ?",
            [$characterId]
        )['count'];
        
        return $friendsCount < MAX_FRIENDS;
    }
    
    /**
     * Pobiera liczbę znajomych postaci
     */
    public function getFriendsCount($characterId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM character_friends WHERE character_id = ?",
            [$characterId]
        );
        return $result['count'];
    }
    
    /**
     * Pobiera statystyki społeczne postaci
     */
    public function getSocialStats($characterId) {
        $friendsCount = $this->getFriendsCount($characterId);
        $traitsCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM character_traits WHERE character_id = ?",
            [$characterId]
        )['count'];
        $weaponsCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM character_weapons WHERE character_id = ?",
            [$characterId]
        )['count'];
        
        return [
            'friends_count' => $friendsCount,
            'traits_count' => $traitsCount,
            'weapons_count' => $weaponsCount,
            'can_add_friends' => $friendsCount < MAX_FRIENDS
        ];
    }
}

?>