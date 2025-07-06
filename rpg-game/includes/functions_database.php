<?php
/**
 * Funkcje bazy danych - ustawienia systemowe
 */

// ==================== FUNKCJE BAZY DANYCH ====================

/**
 * Pobiera ustawienie systemowe
 */
if (!function_exists('getSystemSetting')) {
    function getSystemSetting($key, $default = null) {
        try {
            $db = Database::getInstance();
            $setting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
            return $setting ? $setting['setting_value'] : $default;
        } catch (Exception $e) {
            error_log("Error getting system setting: " . $e->getMessage());
            return $default;
        }
    }
}

/**
 * Ustawia wartość ustawienia systemowego
 */
if (!function_exists('setSystemSetting')) {
    function setSystemSetting($key, $value) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP";
            return $db->query($sql, [$key, $value, $value]);
        } catch (Exception $e) {
            error_log("Error setting system setting: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Pobiera wszystkie ustawienia systemowe jako tablicę
 */
if (!function_exists('getAllSystemSettings')) {
    function getAllSystemSettings() {
        try {
            $db = Database::getInstance();
            $settings = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error getting all system settings: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Usuwa ustawienie systemowe
 */
if (!function_exists('deleteSystemSetting')) {
    function deleteSystemSetting($key) {
        try {
            $db = Database::getInstance();
            return $db->query("DELETE FROM system_settings WHERE setting_key = ?", [$key]);
        } catch (Exception $e) {
            error_log("Error deleting system setting: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Sprawdza czy ustawienie istnieje
 */
if (!function_exists('systemSettingExists')) {
    function systemSettingExists($key) {
        try {
            $db = Database::getInstance();
            $setting = $db->fetchOne("SELECT setting_key FROM system_settings WHERE setting_key = ?", [$key]);
            return $setting !== false;
        } catch (Exception $e) {
            error_log("Error checking system setting existence: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Aktualizuje lub tworzy wielokrotne ustawienia systemowe
 * NAPRAWIONA WERSJA - używa metod Database bezpośrednio
 */
if (!function_exists('setMultipleSystemSettings')) {
    function setMultipleSystemSettings($settings) {
        if (!is_array($settings) || empty($settings)) {
            return false;
        }
        
        try {
            $db = Database::getInstance();
            
            // Sprawdź czy Database ma metody transakcji
            if (method_exists($db, 'beginTransaction')) {
                // Użyj transakcji jeśli dostępne
                $db->beginTransaction();
                
                foreach ($settings as $key => $value) {
                    $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                            VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP";
                    $db->query($sql, [$key, $value, $value]);
                }
                
                $db->commit();
            } else {
                // Fallback bez transakcji
                foreach ($settings as $key => $value) {
                    $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                            VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP";
                    $db->query($sql, [$key, $value, $value]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            // Wycofaj transakcję w przypadku błędu jeśli dostępna
            if (isset($db) && method_exists($db, 'inTransaction') && $db->inTransaction()) {
                $db->rollback();
            }
            error_log("Error setting multiple system settings: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Alternatywna wersja bez transakcji (jako backup)
 */
if (!function_exists('setMultipleSystemSettingsSimple')) {
    function setMultipleSystemSettingsSimple($settings) {
        if (!is_array($settings) || empty($settings)) {
            return false;
        }
        
        $success = true;
        foreach ($settings as $key => $value) {
            if (!setSystemSetting($key, $value)) {
                $success = false;
                error_log("Failed to set system setting: $key = $value");
            }
        }
        
        return $success;
    }
}