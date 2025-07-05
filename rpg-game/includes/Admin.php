<?php
class Admin {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($username, $password) {
        $admin = $this->db->fetchOne(
            "SELECT * FROM admin_users WHERE username = ?",
            [$username]
        );
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    public function getStats() {
        $stats = [];
        
        $stats['total_characters'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM characters"
        )['count'];
        
        $stats['total_battles'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM battles"
        )['count'];
        
        $stats['active_today'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM characters WHERE DATE(last_login) = CURDATE()"
        )['count'];
        
        $stats['total_weapons'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM weapons"
        )['count'];
        
        $stats['total_traits'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM traits"
        )['count'];
        
        return $stats;
    }
    
    public function getRecentBattles($limit = 10) {
        return $this->db->fetchAll("
            SELECT b.*, a.name as attacker_name, d.name as defender_name, w.name as winner_name
            FROM battles b
            JOIN characters a ON b.attacker_id = a.id
            JOIN characters d ON b.defender_id = d.id
            LEFT JOIN characters w ON b.winner_id = w.id
            ORDER BY b.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
    
    public function getTopPlayers($limit = 10) {
        return $this->db->fetchAll("
            SELECT name, level, experience, last_login
            FROM characters
            ORDER BY level DESC, experience DESC
            LIMIT ?
        ", [$limit]);
    }
    
    public function updateCharacter($id, $data) {
        $allowedFields = ['health', 'max_health', 'stamina', 'max_stamina', 'damage', 'dexterity', 'agility', 'armor', 'max_armor', 'level', 'experience'];
        
        $updateParts = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateParts[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (!empty($updateParts)) {
            $params[] = $id;
            $sql = "UPDATE characters SET " . implode(', ', $updateParts) . " WHERE id = ?";
            return $this->db->query($sql, $params);
        }
        
        return false;
    }
    
    public function deleteCharacter($id) {
        if ($id > 0) {
            return $this->db->query("DELETE FROM characters WHERE id = ?", [$id]);
        }
        return false;
    }
    
    public function addWeapon($name, $damage, $armorPenetration = 0, $dropChance = 0.01, $imagePath = '') {
        $sql = "INSERT INTO weapons (name, damage, armor_penetration, drop_chance, image_path) VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, [$name, $damage, $armorPenetration, $dropChance, $imagePath]);
    }
    
    public function updateWeapon($id, $name, $damage, $armorPenetration = 0, $dropChance = 0.01, $imagePath = '') {
        $sql = "UPDATE weapons SET name = ?, damage = ?, armor_penetration = ?, drop_chance = ?, image_path = ? WHERE id = ?";
        return $this->db->query($sql, [$name, $damage, $armorPenetration, $dropChance, $imagePath, $id]);
    }
    
    public function deleteWeapon($id) {
        if ($id > 1) {
            return $this->db->query("DELETE FROM weapons WHERE id = ?", [$id]);
        }
        return false;
    }
    
    public function getAllWeapons() {
        return $this->db->fetchAll("SELECT * FROM weapons ORDER BY id");
    }
    
    public function addTrait($data) {
        $sql = "INSERT INTO traits (name, description, type, effect_type, effect_target, effect_value, effect_duration, trigger_chance, drop_chance, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $data['name'],
            $data['description'],
            $data['type'],
            $data['effect_type'],
            $data['effect_target'],
            $data['effect_value'],
            $data['effect_duration'],
            $data['trigger_chance'],
            $data['drop_chance'],
            $data['image_path']
        ]);
    }
    
    public function updateTrait($id, $data) {
        $sql = "UPDATE traits SET name = ?, description = ?, type = ?, effect_type = ?, effect_target = ?, effect_value = ?, effect_duration = ?, trigger_chance = ?, drop_chance = ?, image_path = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['name'],
            $data['description'],
            $data['type'],
            $data['effect_type'],
            $data['effect_target'],
            $data['effect_value'],
            $data['effect_duration'],
            $data['trigger_chance'],
            $data['drop_chance'],
            $data['image_path'],
            $id
        ]);
    }
    
    public function deleteTrait($id) {
        return $this->db->query("DELETE FROM traits WHERE id = ?", [$id]);
    }
    
    public function getAllTraits() {
        return $this->db->fetchAll("SELECT * FROM traits ORDER BY id");
    }
    
    public function getSystemSetting($key) {
        $setting = $this->db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = ?",
            [$key]
        );
        return $setting ? $setting['setting_value'] : null;
    }
    
    public function updateSystemSetting($key, $value) {
        $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP";
        return $this->db->query($sql, [$key, $value, $value]);
    }
    
    public function getAllSystemSettings() {
        return $this->db->fetchAll("SELECT * FROM system_settings ORDER BY setting_key");
    }
    
    public function addSecretCode($code, $usesLeft = 1, $description = '') {
        $sql = "INSERT INTO secret_codes (code, uses_left, description) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$code, $usesLeft, $description]);
    }
    
    public function updateSecretCode($id, $code, $usesLeft, $description = '', $isActive = true) {
        $sql = "UPDATE secret_codes SET code = ?, uses_left = ?, description = ?, is_active = ? WHERE id = ?";
        return $this->db->query($sql, [$code, $usesLeft, $description, $isActive, $id]);
    }
    
    public function deleteSecretCode($id) {
        return $this->db->query("DELETE FROM secret_codes WHERE id = ?", [$id]);
    }
    
    public function getAllSecretCodes() {
        return $this->db->fetchAll("SELECT * FROM secret_codes ORDER BY created_at DESC");
    }
    
    public function logActivity($action, $details = '') {
        $sql = "INSERT INTO admin_activity_log (admin_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return $this->db->query($sql, [$_SESSION['admin_id'], $action, $details, $ipAddress]);
    }
}
?>