<?php
/**
 * Funkcje podstawowe - bezpieczeństwo, sanityzacja, walidacja
 */

// ==================== FUNKCJE BEZPIECZEŃSTWA ====================

/**
 * Sanityzuje dane wejściowe
 */
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generuje token CSRF
 */
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Weryfikuje token CSRF
 */
if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Generuje bezpieczny losowy hash
 */
if (!function_exists('generateSecureHash')) {
    function generateSecureHash($length = 64) {
        return bin2hex(random_bytes($length / 2));
    }
}

/**
 * Generuje losowy PIN (6 cyfr)
 */
if (!function_exists('generateRandomPIN')) {
    function generateRandomPIN() {
        return str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
}

// ==================== FUNKCJE WALIDACJI ====================

/**
 * Waliduje imię postaci
 */
if (!function_exists('validateCharacterName')) {
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
}

/**
 * Waliduje PIN
 */
if (!function_exists('validatePIN')) {
    function validatePIN($pin) {
        if (empty($pin)) {
            return 'PIN nie może być pusty';
        }
        
        if (!preg_match('/^[0-9]{6}$/', $pin)) {
            return 'PIN musi składać się z 6 cyfr';
        }
        
        return true;
    }
}

/**
 * Waliduje kod tajny
 */
if (!function_exists('validateSecretCode')) {
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
}

?>