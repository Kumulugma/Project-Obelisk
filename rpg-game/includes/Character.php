<?php
class Character {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($name, $gender = 'male', $secretCode = null) {
        // NOWA LOGIKA - Sprawdzenie trybu rejestracji
        $registrationMode = $this->getSystemSetting('registration_mode', 'open');
        
        switch ($registrationMode) {
            case 'closed':
                throw new Exception($this->getSystemSetting('closed_registration_message', 'Rejestracja jest tymczasowo zamknięta.'));
                break;
                
            case 'invite_only':
                if (!$secretCode || !$this->validateSecretCode($secretCode)) {
                    throw new Exception($this->getSystemSetting('invite_only_message', 'Rejestracja wymaga kodu zaproszenia.'));
                }
                break;
                
            case 'open':
            default:
                // Sprawdź limit postaci tylko jeśli nie ma kodu
                $maxChars = $this->getSystemSetting('max_characters', 1000);
                $currentCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
                
                if ($currentCount >= $maxChars && !$this->validateSecretCode($secretCode)) {
                    throw new Exception("Osiągnięto limit postaci. Użyj kodu tajnego lub spróbuj później.");
                }
                break;
        }
        
        // Walidacja płci
        if (!in_array($gender, ['male', 'female'])) {
            $gender = 'male';
        }
        
        $pin = $this->generatePin();
        $hash1 = $this->generateHash();
        $hash2 = $this->generateHash();
        $avatar = $this->getRandomAvatarByGender($gender);
        
        $sql = "INSERT INTO characters (name, gender, pin, hash1, hash2, avatar_image) VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [$name, $gender, $pin, $hash1, $hash2, $avatar]);
        
        $characterId = $this->db->lastInsertId();
        $this->equipWeapon($characterId, 1);
        
        if ($secretCode && $this->validateSecretCode($secretCode)) {
            $this->useSecretCode($secretCode);
        }
        
        return [
            'id' => $characterId,
            'pin' => $pin,
            'hash1' => $hash1,
            'hash2' => $hash2,
            'gender' => $gender,
            'avatar' => $avatar
        ];
    }
    
    // NOWA METODA - Sprawdzenie czy rejestracja jest dostępna
    public function isRegistrationAvailable($hasSecretCode = false) {
        $registrationMode = $this->getSystemSetting('registration_mode', 'open');
        
        switch ($registrationMode) {
            case 'closed':
                return false;
                
            case 'invite_only':
                return $hasSecretCode;
                
            case 'open':
            default:
                // Sprawdź limit postaci
                $maxChars = $this->getSystemSetting('max_characters', 1000);
                $currentCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
                return $currentCount < $maxChars || $hasSecretCode;
        }
    }
    
    // NOWA METODA - Pobierz komunikat o stanie rejestracji
    public function getRegistrationMessage() {
        $registrationMode = $this->getSystemSetting('registration_mode', 'open');
        
        switch ($registrationMode) {
            case 'closed':
                return [
                    'type' => 'danger',
                    'message' => $this->getSystemSetting('closed_registration_message', 'Rejestracja jest tymczasowo zamknięta.')
                ];
                
            case 'invite_only':
                return [
                    'type' => 'warning', 
                    'message' => $this->getSystemSetting('invite_only_message', 'Rejestracja jest możliwa tylko za pomocą kodu zaproszenia.')
                ];
                
            case 'open':
            default:
                $maxChars = $this->getSystemSetting('max_characters', 1000);
                $currentCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
                
                if ($currentCount >= $maxChars) {
                    return [
                        'type' => 'warning',
                        'message' => "Osiągnięto limit {$maxChars} postaci. Rejestracja możliwa tylko z kodem tajnym."
                    ];
                } else {
                    $remaining = $maxChars - $currentCount;
                    return [
                        'type' => 'success',
                        'message' => $this->getSystemSetting('registration_message', "Rejestracja otwarta! Pozostało {$remaining} miejsc.")
                    ];
                }
        }
    }
    
    public function getByHashes($hash1, $hash2) {
        $sql = "SELECT c.*, w.name as weapon_name, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.hash1 = ? AND c.hash2 = ?";
        return $this->db->fetchOne($sql, [$hash1, $hash2]);
    }
    
    public function getByPin($pin) {
        $sql = "SELECT c.*, w.name as weapon_name, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.pin = ?";
        return $this->db->fetchOne($sql, [$pin]);
    }    
    
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
    
    public function removeFriend($characterId, $friendId) {
        $sql = "DELETE FROM character_friends WHERE character_id = ? AND friend_id = ?";
        $result = $this->db->query($sql, [$characterId, $friendId]);
        
        if ($result->rowCount() == 0) {
            throw new Exception("Ta postać nie jest w Twoich znajomych.");
        }
    }
    
    public function getTraits($characterId) {
        $sql = "SELECT t.* 
                FROM traits t 
                JOIN character_traits ct ON t.id = ct.trait_id 
                WHERE ct.character_id = ?
                ORDER BY ct.obtained_at";
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
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
    
    public function addExperience($characterId, $exp) {
        $character = $this->getById($characterId);
        $newExp = $character['experience'] + $exp;
        $newLevel = $character['level'];
        
        $expPerLevel = $this->getSystemSetting('experience_per_level', EXP_PER_LEVEL);
        while ($newExp >= ($newLevel * $expPerLevel)) {
            $newExp -= ($newLevel * $expPerLevel);
            $newLevel++;
            $this->levelUp($characterId, $newLevel);
        }
        
        $sql = "UPDATE characters SET experience = ?, level = ? WHERE id = ?";
        $this->db->query($sql, [$newExp, $newLevel, $characterId]);
    }
    
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
        
        $updateSql .= implode(', ', $setParts) . " WHERE id = ?";
        $updateParams[] = $characterId;
        
        $this->db->query($updateSql, $updateParams);
        
        if (rand(1, 100) <= 20) {
            $this->grantRandomTrait($characterId);
        }
    }
    
    private function grantRandomTrait($characterId) {
        $sql = "SELECT t.id, t.drop_chance 
                FROM traits t 
                WHERE t.id NOT IN (
                    SELECT ct.trait_id FROM character_traits ct WHERE ct.character_id = ?
                )";
        $availableTraits = $this->db->fetchAll($sql, [$characterId]);
        
        if (empty($availableTraits)) return;
        
        $totalChance = 0;
        foreach ($availableTraits as $trait) {
            $totalChance += $trait['drop_chance'];
        }
        
        if ($totalChance <= 0) return;
        
        $rand = mt_rand() / mt_getrandmax() * $totalChance;
        $currentChance = 0;
        
        foreach ($availableTraits as $trait) {
            $currentChance += $trait['drop_chance'];
            if ($rand <= $currentChance) {
                $this->addTrait($characterId, $trait['id']);
                break;
            }
        }
    }
    
    public function equipWeapon($characterId, $weaponId) {
        $hasWeapon = $this->db->fetchOne(
            "SELECT id FROM character_weapons WHERE character_id = ? AND weapon_id = ?",
            [$characterId, $weaponId]
        );
        
        if (!$hasWeapon && $weaponId != 1) {
            throw new Exception("Nie posiadasz tej broni.");
        }
        
        $character = $this->getById($characterId);
        if ($character['last_weapon_change'] == date('Y-m-d') && $weaponId != 1) {
            throw new Exception("Możesz zmienić broń tylko raz dziennie.");
        }
        
        // Usuń wyposażenie ze wszystkich broni
        $this->db->query(
            "UPDATE character_weapons SET is_equipped = FALSE WHERE character_id = ?",
            [$characterId]
        );
        
        // Wyposaż nową broń (jeśli nie jest pięścią)
        if ($weaponId != 1) {
            $this->db->query(
                "UPDATE character_weapons SET is_equipped = TRUE WHERE character_id = ? AND weapon_id = ?",
                [$characterId, $weaponId]
            );
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
    
    public function getWeapons($characterId) {
        $sql = "SELECT w.*, cw.is_equipped, cw.obtained_at
                FROM weapons w
                JOIN character_weapons cw ON w.id = cw.weapon_id
                WHERE cw.character_id = ?
                ORDER BY cw.obtained_at DESC";
        return $this->db->fetchAll($sql, [$characterId]);
    }
    
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
    
    // NOWE METODY DLA SYSTEMU AVATARÓW I PŁCI
    
    public function getAvailableAvatars($gender = null) {
        if ($gender && in_array($gender, ['male', 'female'])) {
            $sql = "SELECT * FROM avatar_images WHERE (gender = ? OR gender = 'unisex') AND is_active = TRUE";
            return $this->db->fetchAll($sql, [$gender]);
        } else {
            $sql = "SELECT * FROM avatar_images WHERE is_active = TRUE";
            return $this->db->fetchAll($sql);
        }
    }
    
    public function changeAvatar($characterId, $avatarPath) {
        // Sprawdź czy avatar istnieje i jest aktywny
        $avatar = $this->db->fetchOne(
            "SELECT id FROM avatar_images WHERE image_path = ? AND is_active = TRUE",
            [$avatarPath]
        );
        
        if (!$avatar) {
            throw new Exception("Wybrany avatar nie istnieje lub jest nieaktywny.");
        }
        
        // Sprawdź czy postać może zmienić avatar (np. raz dziennie)
        $character = $this->getById($characterId);
        $today = date('Y-m-d');
        
        // Dodaj sprawdzenie ostatniej zmiany avatara (opcjonalnie)
        // if ($character['last_avatar_change'] == $today) {
        //     throw new Exception("Możesz zmienić avatar tylko raz dziennie.");
        // }
        
        $sql = "UPDATE characters SET avatar_image = ? WHERE id = ?";
        $this->db->query($sql, [$avatarPath, $characterId]);
    }
    
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
    
    private function generatePin() {
        do {
            $pin = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->db->fetchOne("SELECT id FROM characters WHERE pin = ?", [$pin]);
        } while ($exists);
        
        return $pin;
    }
    
    private function generateHash($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    private function getRandomAvatar() {
        // Stara metoda - używana jako fallback
        $avatars = $this->db->fetchAll("SELECT image_path FROM avatar_images WHERE is_active = TRUE");
        if (empty($avatars)) {
            return '/images/avatars/default.png';
        }
        return $avatars[array_rand($avatars)]['image_path'];
    }
    
    private function validateSecretCode($code) {
        if (!$code) return false;
        
        $codeData = $this->db->fetchOne(
            "SELECT * FROM secret_codes WHERE code = ? AND is_active = TRUE AND (uses_left > 0 OR uses_left = -1)",
            [$code]
        );
        
        return $codeData !== false;
    }
    
    private function useSecretCode($code) {
        $this->db->query(
            "UPDATE secret_codes SET uses_left = uses_left - 1 WHERE code = ? AND uses_left > 0",
            [$code]
        );
    }
    
    private function getSystemSetting($key, $default = null) {
        $setting = $this->db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = ?",
            [$key]
        );
        return $setting ? $setting['setting_value'] : $default;
    }
    
    public function getById($id) {
        $sql = "SELECT c.*, w.name as weapon_name, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    /**
     * Sprawdza czy postać ma status "banished" (zbieg)
     */
    public function isBanished($characterId) {
        $character = $this->db->fetchOne("SELECT status FROM characters WHERE id = ?", [$characterId]);
        return $character && $character['status'] === 'banished';
    }
    
    /**
     * Pobiera domyślny avatar dla danej płci
     */
    private function getDefaultAvatarByGender($gender) {
        $sql = "SELECT image_path FROM avatar_images WHERE (gender = ? OR gender = 'unisex') AND is_active = 1 ORDER BY RAND() LIMIT 1";
        $avatar = $this->db->fetchOne($sql, [$gender]);
        
        if ($avatar) {
            return $avatar['image_path'];
        }
        
        // Ostatni fallback
        return '/images/avatars/default.png';
    }
    /**
     * Sprawdza czy postać ma ciasteczko i jest prawidłowa
     */
    public function validateCharacterFromCookie() {
        $cookieData = getCharacterFromCookie();
        
        if (!$cookieData) {
            return null;
        }
        
        // Sprawdź czy postać nadal istnieje i ma prawidłowy status
        $character = $this->getByPin($cookieData['pin']);
        
        if (!$character) {
            clearCharacterCookie();
            return null;
        }
        
        // Sprawdź status postaci
        if (in_array($character['status'], ['deleted', 'banished'])) {
            clearCharacterCookie();
            return null;
        }
        
        return $character;
    }
    
    /**
     * Sprawdza czy postać istnieje i ma prawidłowy status
     */
    public function checkCharacterAccess($hash1, $hash2) {
        $character = $this->getByHashes($hash1, $hash2);
        
        if (!$character) {
            return ['status' => 'not_found', 'message' => 'Postać nie istnieje.'];
        }
        
        if (isset($character['status'])) {
            if ($character['status'] === 'banished') {
                return ['status' => 'banished', 'message' => 'Twoja postać została odebrana przez administratora.'];
            }
            
            if ($character['status'] === 'deleted') {
                return ['status' => 'deleted', 'message' => 'Twoja postać została usunięta.'];
            }
        }
        
        return ['status' => 'active', 'character' => $character];
    }
    
    /**
     * Oblicza ile doświadczenia potrzeba do następnego poziomu
     */
    public function getExperienceToNextLevel($character) {
        $expPerLevel = getSetting('exp_per_level', 100);
        $currentLevel = $character['level'];
        $currentExp = $character['experience'];
        
        // Doświadczenie potrzebne na następny poziom
        $expForNextLevel = $currentLevel * $expPerLevel;
        
        // Ile jeszcze potrzeba
        $expNeeded = $expForNextLevel - $currentExp;
        
        // Postęp w procentach
        $expForCurrentLevel = ($currentLevel - 1) * $expPerLevel;
        $progressExp = $currentExp - $expForCurrentLevel;
        $levelExpRange = $expForNextLevel - $expForCurrentLevel;
        $progressPercent = $levelExpRange > 0 ? ($progressExp / $levelExpRange) * 100 : 0;
        
        return [
            'exp_needed' => max(0, $expNeeded),
            'exp_for_next_level' => $expForNextLevel,
            'exp_for_current_level' => $expForCurrentLevel,
            'progress_percent' => min(100, max(0, $progressPercent)),
            'progress_exp' => $progressExp,
            'level_exp_range' => $levelExpRange
        ];
    }
    
    /**
     * Pobiera procenty dla pasków statusu
     */
    public function getStatusBarPercentages($character) {
        return [
            'health_percent' => ($character['health'] / max(1, $character['max_health'])) * 100,
            'stamina_percent' => ($character['stamina'] / max(1, $character['max_stamina'])) * 100,
            'armor_percent' => ($character['armor'] / max(1, $character['max_armor'])) * 100,
        ];
    }
    
    /**
     * Pobiera avatar postaci lub domyślny
     */
    public function getCharacterAvatar($character) {
        if (!empty($character['avatar_image'])) {
            return $character['avatar_image'];
        }
        
        // Fallback do domyślnego avatara
        return '/images/avatars/default.png';
    }
    
    /**
     * Formatuje statystyki postaci dla wyświetlenia
     */
    public function formatCharacterStats($character) {
        $expInfo = $this->getExperienceToNextLevel($character);
        $statusBars = $this->getStatusBarPercentages($character);
        
        return [
            'avatar' => $this->getCharacterAvatar($character),
            'experience_info' => $expInfo,
            'status_bars' => $statusBars,
            'formatted_stats' => [
                'health' => $character['health'] . '/' . $character['max_health'],
                'stamina' => $character['stamina'] . '/' . $character['max_stamina'],
                'armor' => $character['armor'] . '/' . $character['max_armor'],
                'experience' => number_format($character['experience']) . ' exp',
                'exp_to_next' => number_format($expInfo['exp_needed']) . ' exp do poziomu ' . ($character['level'] + 1)
            ]
        ];
    }
    
    /**
     * Bezpieczne pobieranie znajomych (obsługuje brak tabeli)
     */
    public function getFriends($characterId) {
        try {
            // Próbuj różne nazwy tabel dla znajomych
            $tables = ['character_friends', 'friends'];
            
            foreach ($tables as $table) {
                try {
                    $sql = "SELECT c.*, f.created_at as friendship_date 
                            FROM characters c 
                            JOIN {$table} f ON c.id = f.friend_id 
                            WHERE f.character_id = ? AND c.status = 'active'
                            ORDER BY f.created_at DESC";
                    
                    return $this->db->fetchAll($sql, [$characterId]);
                } catch (Exception $e) {
                    // Spróbuj następną tabelę
                    continue;
                }
            }
            
            // Jeśli żadna tabela nie działa, zwróć pustą tablicę
            return [];
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Bezpieczne pobieranie znajomych z avatarami
     */
    public function getFriendsWithAvatars($characterId) {
        $friends = $this->getFriends($characterId);
        
        // Dodaj avatary
        foreach ($friends as &$friend) {
            $friend['avatar'] = $this->getCharacterAvatar($friend);
        }
        
        return $friends;
    }
    
    /**
     * Pobiera traity postaci z szczegółami
     */
    public function getTraitsWithDetails($characterId) {
        try {
            $sql = "
                SELECT t.*, ct.obtained_at as equipped_at 
                FROM character_traits ct
                JOIN traits t ON ct.trait_id = t.id
                WHERE ct.character_id = ?
                ORDER BY t.type, t.name
            ";
            
            return $this->db->fetchAll($sql, [$characterId]);
        } catch (Exception $e) {
            // Fallback do podstawowej metody getTraits
            return $this->getTraits($characterId);
        }
    }
    
    /**
     * Pobiera przeciwników z avatarami
     */
    public function getRandomOpponentsWithAvatars($characterId, $limit = 10) {
        $sql = "
            SELECT c.id, c.name, c.level, c.avatar_image, c.gender,
                   c.health, c.max_health, c.damage, c.armor
            FROM characters c 
            WHERE c.id != ? AND (c.status = 'active' OR c.status IS NULL)
            ORDER BY RAND() 
            LIMIT ?
        ";
        
        $opponents = $this->db->fetchAll($sql, [$characterId, $limit]);
        
        // Dodaj avatary
        foreach ($opponents as &$opponent) {
            $opponent['avatar'] = $this->getCharacterAvatar($opponent);
        }
        
        return $opponents;
    }
    
    /**
     * Aktualizuje ostatnie logowanie
     */
    public function updateLastLogin($characterId) {
        try {
            $sql = "UPDATE characters SET last_login = NOW() WHERE id = ?";
            $this->db->query($sql, [$characterId]);
        } catch (Exception $e) {
            // Cicha obsługa błędu
        }
    }
    
    /**
     * Resetuje dzienne punkty
     */
    public function resetDailyPoints($characterId) {
        try {
            $today = date('Y-m-d');
            
            // Sprawdź czy trzeba zresetować punkty
            $character = $this->getById($characterId);
            if (!$character) return;
            
            $lastReset = $character['last_energy_reset'] ?? '1900-01-01';
            
            if ($lastReset < $today) {
                $dailyEnergy = getSetting('daily_energy', 10);
                $dailyChallenges = getSetting('daily_challenges', 2);
                
                $sql = "UPDATE characters SET 
                        energy_points = ?, 
                        challenge_points = ?, 
                        last_energy_reset = ? 
                        WHERE id = ?";
                        
                $this->db->query($sql, [$dailyEnergy, $dailyChallenges, $today, $characterId]);
            }
        } catch (Exception $e) {
            // Cicha obsługa błędu
        }
    }
    
    /**
     * Używa punkt energii
     */
    public function useEnergyPoint($characterId) {
        try {
            $sql = "UPDATE characters SET energy_points = energy_points - 1 WHERE id = ? AND energy_points > 0";
            $result = $this->db->query($sql, [$characterId]);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Używa punkt wyzwania
     */
    public function useChallengePoint($characterId) {
        try {
            $sql = "UPDATE characters SET challenge_points = challenge_points - 1 WHERE id = ? AND challenge_points > 0";
            $result = $this->db->query($sql, [$characterId]);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>