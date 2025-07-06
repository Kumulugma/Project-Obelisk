<?php
/**
 * Funkcje pomocnicze dla aplikacji RPG
 */

// ==================== FUNKCJE reCAPTCHA ====================

/**
 * Pobiera klucz publiczny reCAPTCHA z bazy danych
 */
function getRecaptchaSiteKey() {
    $db = Database::getInstance();
    $setting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'recaptcha_site_key'");
    return $setting ? $setting['setting_value'] : '';
}

/**
 * Pobiera klucz prywatny reCAPTCHA z bazy danych
 */
function getRecaptchaSecretKey() {
    $db = Database::getInstance();
    $setting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'recaptcha_secret_key'");
    return $setting ? $setting['setting_value'] : '';
}

/**
 * Weryfikuje reCAPTCHA używając kluczy z bazy danych
 */
function verifyRecaptchaFromDB($response) {
    $secretKey = getRecaptchaSecretKey();
    
    if (empty($secretKey)) {
        return true; // Skip verification if no secret key configured
    }
    
    if (empty($response)) {
        return false;
    }
    
    $data = [
        'secret' => $secretKey,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    
    if ($result === false) {
        return true; // Allow if verification service is unavailable
    }
    
    $resultJson = json_decode($result, true);
    return $resultJson['success'] ?? false;
}

/**
 * Weryfikuje reCAPTCHA używając klucza z config.php (legacy)
 */
function verifyRecaptcha($response) {
    if (empty(RECAPTCHA_SECRET)) {
        return true; // Skip verification if no secret key
    }
    
    if (empty($response)) {
        return false;
    }
    
    $data = [
        'secret' => RECAPTCHA_SECRET,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    
    if ($result === false) {
        return true; // Allow if verification service is unavailable
    }
    
    $resultJson = json_decode($result, true);
    return $resultJson['success'] ?? false;
}

// ==================== FUNKCJE BEZPIECZEŃSTWA ====================

/**
 * Sanityzuje dane wejściowe
 */
function sanitizeInput($input) {
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generuje token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Weryfikuje token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generuje bezpieczny losowy hash
 */
function generateSecureHash($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generuje losowy PIN (6 cyfr)
 */
function generateRandomPIN() {
    return str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

// ==================== FUNKCJE CIASTECZEK ====================

/**
 * Zapisuje dane postaci w ciasteczku
 */
function setCharacterCookie($characterData) {
    $cookieData = [
        'pin' => $characterData['pin'],
        'hash1' => $characterData['hash1'],
        'hash2' => $characterData['hash2'],
        'name' => $characterData['name'] ?? ''
    ];
    
    $cookieValue = base64_encode(json_encode($cookieData));
    setcookie('rpg_character', $cookieValue, time() + (86400 * 30), '/', '', false, true);
}

/**
 * Pobiera dane postaci z ciasteczka
 */
function getCharacterFromCookie() {
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
function clearCharacterCookie() {
    setcookie('rpg_character', '', time() - 3600, '/');
}

// ==================== FUNKCJE FORMATOWANIA ====================

/**
 * Formatuje czas jako "X minut temu"
 */
function formatTimeAgo($datetime) {
    if (empty($datetime)) {
        return 'nieznany';
    }
    
    $time = time() - strtotime($datetime);
    
    if ($time < 0) {
        return 'w przyszłości';
    }
    
    if ($time < 60) return 'przed chwilą';
    if ($time < 3600) return floor($time/60) . ' min temu';
    if ($time < 86400) return floor($time/3600) . ' godz temu';
    if ($time < 2592000) return floor($time/86400) . ' dni temu';
    if ($time < 31536000) return floor($time/2592000) . ' miesięcy temu';
    
    return floor($time/31536000) . ' lat temu';
}

/**
 * Formatuje liczbę jako wartość ze spacjami
 */
function formatNumber($number) {
    return number_format($number, 0, ',', ' ');
}

/**
 * Formatuje procent
 */
function formatPercent($value, $decimals = 1) {
    return number_format($value * 100, $decimals, ',', ' ') . '%';
}

/**
 * Formatuje datę w formacie polskim
 */
function formatDate($datetime, $format = 'd.m.Y H:i') {
    if (empty($datetime)) {
        return 'nieznana';
    }
    return date($format, strtotime($datetime));
}

// ==================== FUNKCJE URZĄDZEŃ ====================

/**
 * Sprawdza czy użytkownik używa urządzenia mobilnego
 */
function isMobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/', $userAgent);
}

/**
 * Sprawdza czy użytkownik używa tabletu
 */
function isTablet() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/iPad|Android(?!.*Mobile)/', $userAgent);
}

/**
 * Sprawdza czy to desktop
 */
function isDesktop() {
    return !isMobile() && !isTablet();
}

// ==================== FUNKCJE BAZY DANYCH ====================

/**
 * Pobiera ustawienie systemowe
 */
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

/**
 * Ustawia wartość ustawienia systemowego
 */
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

// ==================== FUNKCJE WALIDACJI ====================

/**
 * Waliduje imię postaci
 */
function validateCharacterName($name) {
    $name = trim($name);
    
    if (empty($name)) {
        return 'Imię nie może być puste';
    }
    
    if (strlen($name) < 2) {
        return 'Imię musi mieć co najmniej 2 znaki';
    }
    
    if (strlen($name) > 50) {
        return 'Imię nie może być dłuższe niż 50 znaków';
    }
    
    if (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ0-9\s\-_]+$/', $name)) {
        return 'Imię zawiera niedozwolone znaki';
    }
    
    return true;
}

/**
 * Waliduje PIN
 */
function validatePIN($pin) {
    if (empty($pin)) {
        return 'PIN nie może być pusty';
    }
    
    if (!preg_match('/^[0-9]{6}$/', $pin)) {
        return 'PIN musi składać się z 6 cyfr';
    }
    
    return true;
}

/**
 * Waliduje kod tajny
 */
function validateSecretCode($code) {
    if (empty($code)) {
        return true; // Kod jest opcjonalny
    }
    
    if (strlen($code) < 3) {
        return 'Kod tajny musi mieć co najmniej 3 znaki';
    }
    
    if (strlen($code) > 50) {
        return 'Kod tajny nie może być dłuższy niż 50 znaków';
    }
    
    if (!preg_match('/^[a-zA-Z0-9]+$/', $code)) {
        return 'Kod tajny może zawierać tylko litery i cyfry';
    }
    
    return true;
}

// ==================== FUNKCJE POMOCNICZE ====================

/**
 * Przekierowuje z komunikatem flash
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Pobiera i usuwa komunikat flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Loguje błąd do pliku
 */
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr" . PHP_EOL;
    
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Pobiera adres IP użytkownika
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}


/**
 * Sprawdza czy użytkownik może wykonać akcję (rate limiting)
 */
function canPerformAction($action, $limit = 5, $timeframe = 300) {
    $key = 'rate_limit_' . $action . '_' . getUserIP();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $data = $_SESSION[$key];
    
    // Reset if timeframe passed
    if (time() - $data['first_attempt'] > $timeframe) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    // Check limit
    if ($data['count'] >= $limit) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

/**
 * Debuguje zmienną (tylko w trybie deweloperskim)
 */
function debug($var, $die = false) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}

/**
 * Sprawdza czy aplikacja działa w trybie deweloperskim
 */
function isDebugMode() {
    return defined('DEBUG_MODE') && DEBUG_MODE;
}

/**
 * Sprawdza status postaci na podstawie ciasteczka (bezpieczna wersja)
 */
function checkCharacterStatusFromCookie() {
    $cookieData = getCharacterFromCookie();
    
    if (!$cookieData) {
        return ['status' => 'no_cookie'];
    }
    
    try {
        $db = Database::getInstance();
        $character = $db->fetchOne(
            "SELECT id, status, name FROM characters WHERE pin = ?",
            [$cookieData['pin']]
        );
        
        if (!$character) {
            clearCharacterCookie();
            return ['status' => 'not_found'];
        }
        
        // Sprawdź czy kolumna status istnieje
        if (isset($character['status'])) {
            if ($character['status'] === 'deleted') {
                clearCharacterCookie();
                return ['status' => 'deleted', 'message' => 'Twoja postać została usunięta.'];
            }
            
            if ($character['status'] === 'banished') {
                clearCharacterCookie();
                return ['status' => 'banished', 'message' => 'Twoja postać została odebrana przez administratora.'];
            }
        }
        
        return ['status' => 'active', 'character' => $character];
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Błąd sprawdzania statusu postaci.'];
    }
}

/**
 * Pobiera ustawienie z bazy danych (bezpieczna wersja)
 */
function getSetting($key, $defaultValue = null) {
    try {
        $db = Database::getInstance();
        
        // Sprawdź czy tabela system_settings istnieje
        $tableExists = $db->fetchOne("SHOW TABLES LIKE 'system_settings'");
        
        if (!$tableExists) {
            return $defaultValue;
        }
        
        $result = $db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = ?",
            [$key]
        );
        
        if ($result) {
            $value = $result['setting_value'];
            
            // Automatyczna konwersja typów
            if (is_numeric($value)) {
                return strpos($value, '.') !== false ? (float)$value : (int)$value;
            }
            
            if (in_array(strtolower($value), ['true', 'false'])) {
                return strtolower($value) === 'true';
            }
            
            return $value;
        }
        
        return $defaultValue;
        
    } catch (Exception $e) {
        return $defaultValue;
    }
}

/**
 * Zapisuje ustawienie w bazie danych
 */
function setSetting($key, $value) {
    try {
        $db = Database::getInstance();
        
        // Konwertuj wartości na string
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_numeric($value)) {
            $value = (string)$value;
        }
        
        $existing = $db->fetchOne(
            "SELECT id FROM system_settings WHERE setting_key = ?",
            [$key]
        );
        
        if ($existing) {
            $db->query(
                "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?",
                [$value, $key]
            );
        } else {
            $db->query(
                "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)",
                [$key, $value]
            );
        }
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Sprawdza czy postać ma prawo dostępu
 */
function validateCharacterAccess($characterData) {
    if (!$characterData) {
        return false;
    }
    
    // Sprawdź status postaci
    if (in_array($characterData['status'], ['deleted', 'banished'])) {
        clearCharacterCookie();
        return false;
    }
    
    return true;
}

/**
 * Formatuje czas dla wyświetlenia "ostatnie logowanie"
 */
function formatLastLogin($datetime) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'Nigdy';
    }
    
    $now = new DateTime();
    $login = new DateTime($datetime);
    $diff = $now->diff($login);
    
    if ($diff->days == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return 'Przed chwilą';
            }
            return $diff->i . ' min temu';
        }
        return $diff->h . ' godz temu';
    } elseif ($diff->days == 1) {
        return 'Wczoraj';
    } elseif ($diff->days < 7) {
        return $diff->days . ' dni temu';
    } else {
        return $login->format('d.m.Y');
    }
}

/**
 * Oblicza procent dla paska postępu
 */
function calculateProgressPercent($current, $max) {
    if ($max <= 0) {
        return 0;
    }
    
    return min(100, max(0, ($current / $max) * 100));
}

/**
 * Sprawdza czy użytkownik ma uprawnienia administratora
 */
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Przekierowuje jeśli użytkownik nie jest administratorem
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Generuje bezpieczny URL dla profilu postaci
 */
function generateProfileUrl($hash1, $hash2, $baseUrl = '') {
    if (empty($baseUrl)) {
        $baseUrl = SITE_URL;
    }
    
    return rtrim($baseUrl, '/') . '/' . $hash1 . '/' . $hash2;
}

/**
 * Waliduje format PIN-u
 */
function isValidPin($pin) {
    return preg_match('/^\d{6}$/', $pin);
}

/**
 * Sanityzuje i waliduje upload avatara
 */
function validateAvatarPath($path) {
    // Podstawowa walidacja ścieżki
    if (empty($path)) {
        return false;
    }
    
    // Sprawdź czy ścieżka zaczyna się od /images/
    if (!str_starts_with($path, '/images/')) {
        return false;
    }
    
    // Sprawdź rozszerzenie pliku
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    
    return in_array($extension, $allowedExtensions);
}

/**
 * Pobiera statystyki systemu dla dashboard (bezpieczna wersja)
 */
function getSystemStats() {
    try {
        $db = Database::getInstance();
        
        $stats = [];
        
        // Podstawowe statystyki z obsługą błędów
        try {
            $stats['total_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters")['count'];
        } catch (Exception $e) {
            $stats['total_characters'] = 0;
        }
        
        try {
            $stats['active_characters'] = $db->fetchOne("SELECT COUNT(*) as count FROM characters WHERE status = 'active' OR status IS NULL")['count'];
        } catch (Exception $e) {
            $stats['active_characters'] = $stats['total_characters'];
        }
        
        try {
            $stats['total_battles'] = $db->fetchOne("SELECT COUNT(*) as count FROM battles")['count'];
        } catch (Exception $e) {
            $stats['total_battles'] = 0;
        }
        
        try {
            $stats['active_users'] = $db->fetchOne(
                "SELECT COUNT(*) as count FROM characters WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            )['count'];
        } catch (Exception $e) {
            $stats['active_users'] = 0;
        }
        
        try {
            // Rozmiar bazy danych
            $dbSize = $db->fetchOne("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size_mb 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            $stats['db_size'] = $dbSize['db_size_mb'] ?? 0;
        } catch (Exception $e) {
            $stats['db_size'] = 0;
        }
        
        return $stats;
        
    } catch (Exception $e) {
        return [
            'total_characters' => 0,
            'active_characters' => 0,
            'total_battles' => 0,
            'active_users' => 0,
            'db_size' => 0
        ];
    }
}
?>