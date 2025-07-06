<?php

/**
 * Główna klasa zarządzania postaciami
 * Odpowiada za tworzenie, edycję, pobieranie danych postaci
 */
class CharacterManager {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new CharacterAuth();
    }
    
    /**
     * Tworzy nową postać
     */
    public function create($name, $gender = 'male', $secretCode = null) {
        // Sprawdzenie trybu rejestracji
        $registrationMode = $this->getSystemSetting('registration_mode', 'open');
        
        switch ($registrationMode) {
            case 'closed':
                throw new Exception($this->getSystemSetting('closed_registration_message', 'Rejestracja jest tymczasowo zamknięta.'));
                break;
                
            case 'invite_only':
                if (!$secretCode || !$this->auth->validateSecretCode($secretCode)) {
                    throw new Exception($this->getSystemSetting('invite_only_message', 'Rejestracja wymaga kodu zaproszenia.'));
                }
                break;
                
            case 'open':
            default:
                // Sprawdź limit postaci tylko jeśli nie ma kodu
                $maxChars = $this->getSystemSetting('max_characters', 1000);
                $currentCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
                
                if ($currentCount >= $maxChars && !$this->auth->validateSecretCode($secretCode)) {
                    throw new Exception("Osiągnięto limit postaci. Użyj kodu tajnego lub spróbuj później.");
                }
                break;
        }
        
        // Walidacja płci
        if (!in_array($gender, ['male', 'female'])) {
            $gender = 'male';
        }
        
        $pin = $this->auth->generatePin();
        $hash1 = $this->auth->generateHash();
        $hash2 = $this->auth->generateHash();
        $avatar = $this->getRandomAvatarByGender($gender);
        
        $sql = "INSERT INTO characters (name, gender, pin, hash1, hash2, avatar_image) VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [$name, $gender, $pin, $hash1, $hash2, $avatar]);
        
        $characterId = $this->db->lastInsertId();
        
        // Dodaj domyślną broń (pięść)
        $this->db->query("INSERT INTO character_weapons (character_id, weapon_id) VALUES (?, 1)", [$characterId]);
        
        // Użyj kod tajny jeśli został podany
        if ($secretCode && $this->auth->validateSecretCode($secretCode)) {
            $this->auth->useSecretCode($secretCode);
        }
        
        return $this->getById($characterId);
    }
    
    /**
     * Pobiera postać po ID
     */
    public function getById($id) {
        $sql = "SELECT c.*, w.name as weapon_name, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Resetuje dzienne punkty
     */
    public function resetDailyPoints($characterId) {
        $today = date('Y-m-d');
        $character = $this->getById($characterId);
        
        $resetEnergy = false;
        $resetChallenges = false;
        
        if ($character['last_energy_reset'] != $today) {
            $resetEnergy = true;
        }
        
        if ($character['last_challenge_reset'] != $today) {
            $resetChallenges = true;
        }
        
        if ($resetEnergy || $resetChallenges) {
            $sql = "UPDATE characters SET ";
            $params = [];
            
            if ($resetEnergy) {
                $sql .= "energy_points = ?, last_energy_reset = ?";
                $params[] = $this->getSystemSetting('daily_energy', 10);
                $params[] = $today;
            }
            
            if ($resetChallenges) {
                if ($resetEnergy) $sql .= ", ";
                $sql .= "challenge_points = ?, last_challenge_reset = ?";
                $params[] = $this->getSystemSetting('daily_challenges', 2);
                $params[] = $today;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $characterId;
            
            $this->db->query($sql, $params);
        }
    }
    
    /**
     * Używa punkt energii
     */
    public function useEnergyPoint($characterId) {
        $sql = "UPDATE characters SET energy_points = energy_points - 1 WHERE id = ? AND energy_points > 0";
        $result = $this->db->query($sql, [$characterId]);
        return $result->rowCount() > 0;
    }
    
    /**
     * Używa punkt wyzwania
     */
    public function useChallengePoint($characterId) {
        $sql = "UPDATE characters SET challenge_points = challenge_points - 1 WHERE id = ? AND challenge_points > 0";
        $result = $this->db->query($sql, [$characterId]);
        return $result->rowCount() > 0;
    }
    
    /**
     * Dodaje doświadczenie i obsługuje poziomowanie
     */
    public function addExperience($characterId, $exp) {
        $character = $this->getById($characterId);
        $newExp = $character['experience'] + $exp;
        $newLevel = $character['level'];
        
        $expPerLevel = $this->getSystemSetting('exp_per_level', 100);
        while ($newExp >= ($newLevel * $expPerLevel)) {
            $newExp -= ($newLevel * $expPerLevel);
            $newLevel++;
            $this->levelUp($characterId, $newLevel);
        }
        
        $sql = "UPDATE characters SET experience = ?, level = ? WHERE id = ?";
        $this->db->query($sql, [$newExp, $newLevel, $characterId]);
    }
    
    /**
     * Poziomowanie postaci
     */
    private function levelUp($characterId, $newLevel) {
        $stats = ['health', 'max_health', 'stamina', 'max_stamina', 'dexterity', 'agility', 'armor', 'max_armor'];
        $statBoosts = [];
        
        for ($i = 0; $i < 5; $i++) {
            $stat = $stats[array_rand($stats)];
            if (!isset($statBoosts[$stat])) {
                $statBoosts[$stat] = 0;
            }
            $statBoosts[$stat]++;
        }
        
        $updateSql = "UPDATE characters SET ";
        $updateParams = [];
        $setParts = [];
        
        foreach ($statBoosts as $stat => $boost) {
            $setParts[] = "$stat = $stat + ?";
            $updateParams[] = $boost;
        }
        
        $updateSql .= implode(", ", $setParts) . " WHERE id = ?";
        $updateParams[] = $characterId;
        
        $this->db->query($updateSql, $updateParams);
    }
    
    /**
     * Zmienia avatar postaci
     */
    public function changeAvatar($characterId, $avatarPath) {
        // Sprawdź czy avatar istnieje i jest aktywny
        $avatar = $this->db->fetchOne(
            "SELECT id FROM avatar_images WHERE image_path = ? AND is_active = TRUE",
            [$avatarPath]
        );
        
        if (!$avatar) {
            throw new Exception("Wybrany avatar nie istnieje lub jest nieaktywny.");
        }
        
        $sql = "UPDATE characters SET avatar_image = ? WHERE id = ?";
        $this->db->query($sql, [$avatarPath, $characterId]);
    }
    
    /**
     * Pobiera dostępne avatary według płci
     */
    public function getAvailableAvatars($gender = null) {
        if ($gender && in_array($gender, ['male', 'female'])) {
            $sql = "SELECT * FROM avatar_images WHERE (gender = ? OR gender = 'unisex') AND is_active = TRUE";
            return $this->db->fetchAll($sql, [$gender]);
        } else {
            $sql = "SELECT * FROM avatar_images WHERE is_active = TRUE";
            return $this->db->fetchAll($sql);
        }
    }
    
    /**
     * Pobiera losowy avatar według płci
     */
    private function getRandomAvatarByGender($gender) {
        $sql = "SELECT image_path FROM avatar_images WHERE (gender = ? OR gender = 'unisex') AND is_active = TRUE";
        $avatars = $this->db->fetchAll($sql, [$gender]);
        
        if (empty($avatars)) {
            // Fallback do uniwersalnych avatarów
            $avatars = $this->db->fetchAll("SELECT image_path FROM avatar_images WHERE is_active = TRUE");
            if (empty($avatars)) {
                return '/images/avatars/default.png';
            }
        }
        
        return $avatars[array_rand($avatars)]['image_path'];
    }
    
    /**
     * Pobiera losowych przeciwników
     */
    public function getRandomOpponents($characterId, $count = 10) {
        // Walidacja parametru count jako liczba całkowita
        $count = intval($count);
        if ($count <= 0) {
            $count = 10;
        }
        if ($count > 50) {
            $count = 50; // Maksymalny limit dla bezpieczeństwa
        }
        
        // Budowanie zapytania z bezpiecznym LIMIT
        $sql = "SELECT c.*, w.name as weapon_name 
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.id != ? 
                ORDER BY RAND() 
                LIMIT " . $count;
        
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
    /**
     * Pobiera ostatnie walki postaci
     */
    public function getRecentBattles($characterId, $limit = 20) {
        // Walidacja limitu jako liczba całkowita
        $limit = intval($limit);
        if ($limit <= 0) {
            $limit = 20;
        }
        if ($limit > 100) {
            $limit = 100; // Maksymalny limit dla bezpieczeństwa
        }
        
        $sql = "SELECT b.*, 
                       a.name as attacker_name, 
                       d.name as defender_name,
                       w.name as winner_name,
                       CASE 
                           WHEN b.attacker_id = ? THEN 'attack'
                           ELSE 'defend'
                       END as battle_role
                FROM battles b
                JOIN characters a ON b.attacker_id = a.id
                JOIN characters d ON b.defender_id = d.id
                LEFT JOIN characters w ON b.winner_id = w.id
                WHERE b.attacker_id = ? OR b.defender_id = ?
                ORDER BY b.created_at DESC
                LIMIT " . $limit;
        
        return $this->db->fetchAll($sql, [$characterId, $characterId, $characterId]);
    }
    
    /**
     * Pobiera ustawienie systemowe - używa funkcji globalnej
     */
    public function getSystemSetting($key, $default = null) {
        return getSystemSetting($key, $default);
    }
}

?>