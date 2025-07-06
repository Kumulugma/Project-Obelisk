<?php
/**
 * Funkcje administracyjne - statystyki, zarządzanie systemem
 */

// ==================== FUNKCJE STATYSTYK ====================

/**
 * Pobiera statystyki systemu
 */
if (!function_exists('getSystemStats')) {
    function getSystemStats() {
        try {
            $db = Database::getInstance();
            
            $stats = [];
            $stats['total_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'] ?? 0;
            $stats['active_users'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;
            $stats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'] ?? 0;
            $stats['total_weapons'] = $db->fetchOne("SELECT COUNT(*) as count FROM weapons")['count'] ?? 0;
            $stats['total_traits'] = $db->fetchOne("SELECT COUNT(*) as count FROM traits")['count'] ?? 0;
            
            // Rozmiar bazy danych (bezpiecznie)
            try {
                $dbSizeResult = $db->fetchOne("
                    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE()
                ");
                $stats['db_size'] = $dbSizeResult['size_mb'] ?? 0;
            } catch (Exception $e) {
                $stats['db_size'] = 'N/A';
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting system stats: " . $e->getMessage());
            return [
                'total_characters' => 0,
                'active_users' => 0,
                'total_battles' => 0,
                'total_weapons' => 0,
                'total_traits' => 0,
                'db_size' => 'N/A'
            ];
        }
    }
}

/**
 * Pobiera statystyki kodów tajnych
 */
if (!function_exists('getSecretCodeStats')) {
    function getSecretCodeStats() {
        try {
            $db = Database::getInstance();
            $codes = $db->fetchAll("SELECT * FROM secret_codes");
            
            return [
                'total' => count($codes),
                'active' => count(array_filter($codes, function($c) { return $c['is_active']; })),
                'unlimited' => count(array_filter($codes, function($c) { return $c['uses_left'] === -1; })),
                'expired' => count(array_filter($codes, function($c) { return $c['uses_left'] === 0; }))
            ];
        } catch (Exception $e) {
            error_log("Error getting secret code stats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'unlimited' => 0, 'expired' => 0];
        }
    }
}

/**
 * Pobiera statystyki avatarów
 */
if (!function_exists('getAvatarStats')) {
    function getAvatarStats() {
        try {
            $db = Database::getInstance();
            $avatars = $db->fetchAll("SELECT * FROM avatar_images");
            
            return [
                'total' => count($avatars),
                'active' => count(array_filter($avatars, function($a) { return $a['is_active']; })),
                'male' => count(array_filter($avatars, function($a) { return $a['gender'] === 'male'; })),
                'female' => count(array_filter($avatars, function($a) { return $a['gender'] === 'female'; })),
                'unisex' => count(array_filter($avatars, function($a) { return $a['gender'] === 'unisex'; }))
            ];
        } catch (Exception $e) {
            error_log("Error getting avatar stats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'male' => 0, 'female' => 0, 'unisex' => 0];
        }
    }
}

/**
 * Pobiera ostatnie aktywności w systemie
 */
if (!function_exists('getRecentActivity')) {
    function getRecentActivity($limit = 20) {
        try {
            $db = Database::getInstance();
            
            // Ostatnie rejestracje
            $recentRegistrations = $db->fetchAll("
                SELECT 'registration' as type, name, created_at as timestamp 
                FROM characters 
                ORDER BY created_at DESC 
                LIMIT ?
            ", [$limit]);
            
            // Ostatnie walki
            $recentBattles = $db->fetchAll("
                SELECT 'battle' as type, 
                       CONCAT(a.name, ' vs ', d.name) as name,
                       b.created_at as timestamp
                FROM battles b
                JOIN characters a ON b.attacker_id = a.id
                JOIN characters d ON b.defender_id = d.id
                ORDER BY b.created_at DESC 
                LIMIT ?
            ", [$limit]);
            
            // Połącz i posortuj
            $activities = array_merge($recentRegistrations, $recentBattles);
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            return array_slice($activities, 0, $limit);
        } catch (Exception $e) {
            error_log("Error getting recent activity: " . $e->getMessage());
            return [];
        }
    }
}

// ==================== FUNKCJE ZARZĄDZANIA ====================

/**
 * Czyści nieaktywne sesje
 */
if (!function_exists('cleanupInactiveSessions')) {
    function cleanupInactiveSessions($olderThanDays = 30) {
        try {
            $db = Database::getInstance();
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$olderThanDays} days"));
            
            // Usuń stare sesje (jeśli tabela sessions istnieje)
            $result = $db->query("DELETE FROM sessions WHERE last_activity < ?", [$cutoffDate]);
            return $result->rowCount();
        } catch (Exception $e) {
            error_log("Error cleaning up sessions: " . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Czyści stare logi błędów
 */
if (!function_exists('cleanupOldLogs')) {
    function cleanupOldLogs($olderThanDays = 90) {
        try {
            $logFile = __DIR__ . '/../logs/error.log';
            
            if (!file_exists($logFile)) {
                return 0;
            }
            
            $lines = file($logFile);
            $cutoffDate = time() - ($olderThanDays * 24 * 60 * 60);
            $keptLines = [];
            $removedCount = 0;
            
            foreach ($lines as $line) {
                // Sprawdź datę w linii loga
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $logTime = strtotime($matches[1]);
                    if ($logTime >= $cutoffDate) {
                        $keptLines[] = $line;
                    } else {
                        $removedCount++;
                    }
                } else {
                    $keptLines[] = $line; // Zachowaj linie bez znacznika czasu
                }
            }
            
            if ($removedCount > 0) {
                file_put_contents($logFile, implode('', $keptLines));
            }
            
            return $removedCount;
        } catch (Exception $e) {
            error_log("Error cleaning up logs: " . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Optymalizuje tabele bazy danych
 */
if (!function_exists('optimizeDatabaseTables')) {
    function optimizeDatabaseTables() {
        try {
            $db = Database::getInstance();
            
            // Pobierz listę tabel
            $tables = $db->fetchAll("SHOW TABLES");
            $optimizedTables = [];
            
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                
                try {
                    $db->query("OPTIMIZE TABLE `{$tableName}`");
                    $optimizedTables[] = $tableName;
                } catch (Exception $e) {
                    error_log("Error optimizing table {$tableName}: " . $e->getMessage());
                }
            }
            
            return $optimizedTables;
        } catch (Exception $e) {
            error_log("Error optimizing database: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Tworzy backup ustawień systemowych
 */
if (!function_exists('backupSystemSettings')) {
    function backupSystemSettings() {
        try {
            $settings = getAllSystemSettings();
            $backup = [
                'timestamp' => date('Y-m-d H:i:s'),
                'settings' => $settings
            ];
            
            $backupDir = __DIR__ . '/../backups';
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $filename = $backupDir . '/settings_backup_' . date('Y-m-d_H-i-s') . '.json';
            file_put_contents($filename, json_encode($backup, JSON_PRETTY_PRINT));
            
            return $filename;
        } catch (Exception $e) {
            error_log("Error creating settings backup: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Sprawdza status systemu
 */
if (!function_exists('getSystemHealth')) {
    function getSystemHealth() {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'warnings' => []
        ];
        
        try {
            // Sprawdź połączenie z bazą danych
            $db = Database::getInstance();
            $db->fetchOne("SELECT 1");
        } catch (Exception $e) {
            $health['status'] = 'critical';
            $health['issues'][] = 'Brak połączenia z bazą danych';
        }
        
        // Sprawdź dostępność katalogów
        $requiredDirs = [
            __DIR__ . '/../logs',
            __DIR__ . '/../cache',
            __DIR__ . '/../templates_c'
        ];
        
        foreach ($requiredDirs as $dir) {
            if (!is_writable($dir)) {
                $health['warnings'][] = "Katalog {$dir} nie jest zapisywalny";
            }
        }
        
        // Sprawdź ustawienia PHP
        if (ini_get('display_errors')) {
            $health['warnings'][] = 'display_errors jest włączone (powinno być wyłączone w produkcji)';
        }
        
        if (count($health['issues']) > 0) {
            $health['status'] = 'critical';
        } elseif (count($health['warnings']) > 0) {
            $health['status'] = 'warning';
        }
        
        return $health;
    }
}

?>