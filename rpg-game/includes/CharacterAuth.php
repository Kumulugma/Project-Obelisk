<?php

/**
 * Klasa odpowiedzialna za autoryzację i identyfikację postaci
 * Obsługuje logowanie przez PIN, hashe, ciasteczka
 */
class CharacterAuth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Autoryzacja postaci przez PIN
     */
    public function authenticateByPin($pin) {
        $sql = "SELECT c.*, w.name as weapon_name, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.pin = ?";
        return $this->db->fetchOne($sql, [$pin]);
    }
    
    /**
     * Autoryzacja postaci przez hashe
     */
    public function authenticateByHashes($hash1, $hash2) {
        $sql = "SELECT c.*, w.name as weapon_name, w.damage as weapon_damage, w.armor_penetration as weapon_penetration
                FROM characters c 
                LEFT JOIN weapons w ON c.equipped_weapon_id = w.id
                WHERE c.hash1 = ? AND c.hash2 = ?";
        return $this->db->fetchOne($sql, [$hash1, $hash2]);
    }
    
    /**
     * Zapisuje dane postaci w ciasteczku
     */
    public function setCharacterCookie($characterData) {
        $cookieData = [
            'pin' => $characterData['pin'],
            'hash1' => $characterData['hash1'],
            'hash2' => $characterData['hash2'],
            'name' => $characterData['name'] ?? '',
            'id' => $characterData['id']
        ];
        
        $cookieValue = base64_encode(json_encode($cookieData));
        setcookie('rpg_character', $cookieValue, time() + (86400 * 30), '/', '', false, true);
    }
    
    /**
     * Pobiera dane postaci z ciasteczka
     */
    public function getCharacterFromCookie() {
        if (isset($_COOKIE['rpg_character'])) {
            $decoded = base64_decode($_COOKIE['rpg_character']);
            $data = json_decode($decoded, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }
        return null;
    }
    
    /**
     * Usuwa ciasteczko postaci
     */
    public function clearCharacterCookie() {
        setcookie('rpg_character', '', time() - 3600, '/');
    }
    
    /**
     * Próbuje automatycznie zalogować postać z ciasteczka
     */
    public function autoLoginFromCookie() {
        $cookieData = $this->getCharacterFromCookie();
        if (!$cookieData) {
            return null;
        }
        
        // Spróbuj zalogować przez PIN
        if (!empty($cookieData['pin'])) {
            $character = $this->authenticateByPin($cookieData['pin']);
            if ($character) {
                return $character;
            }
        }
        
        // Spróbuj zalogować przez hashe
        if (!empty($cookieData['hash1']) && !empty($cookieData['hash2'])) {
            $character = $this->authenticateByHashes($cookieData['hash1'], $cookieData['hash2']);
            if ($character) {
                return $character;
            }
        }
        
        // Jeśli żaden sposób nie zadziałał, usuń nieprawidłowe ciasteczko
        $this->clearCharacterCookie();
        return null;
    }
    
    /**
     * Generuje unikalny PIN
     */
    public function generatePin() {
        do {
            $pin = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->db->fetchOne("SELECT id FROM characters WHERE pin = ?", [$pin]);
        } while ($exists);
        
        return $pin;
    }
    
    /**
     * Generuje unikalny hash
     */
    public function generateHash($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Sprawdza czy kod tajny jest poprawny
     */
    public function validateSecretCode($code) {
        if (!$code) return false;
        
        $codeData = $this->db->fetchOne(
            "SELECT * FROM secret_codes WHERE code = ? AND is_active = TRUE AND (uses_left > 0 OR uses_left = -1)",
            [$code]
        );
        
        return $codeData !== false;
    }
    
    /**
     * Używa kod tajny (zmniejsza liczbę użyć)
     */
    public function useSecretCode($code) {
        $this->db->query(
            "UPDATE secret_codes SET uses_left = uses_left - 1 WHERE code = ? AND uses_left > 0",
            [$code]
        );
    }
    
    /**
     * Aktualizuje ostatnie logowanie
     */
    public function updateLastLogin($characterId) {
        $sql = "UPDATE characters SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$characterId]);
    }
    
    /**
     * Pobiera URL profilu dla postaci
     */
    public function getProfileUrl($characterData) {
        return '/' . $characterData['hash1'] . '/' . $characterData['hash2'];
    }
    
    /**
     * Sprawdza status rejestracji
     */
    public function getRegistrationStatus() {
        $registrationMode = $this->getSystemSetting('registration_mode', 'open');
        
        switch ($registrationMode) {
            case 'closed':
                return [
                    'type' => 'error',
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
    
    /**
     * Pobiera ustawienie systemowe
     */
    private function getSystemSetting($key, $default = null) {
        $setting = $this->db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = ?",
            [$key]
        );
        return $setting ? $setting['setting_value'] : $default;
    }
}

?>