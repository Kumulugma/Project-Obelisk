<?php
/**
 * Funkcje pomocnicze - formatowanie, ciasteczka, urządzenia
 */

// ==================== FUNKCJE CIASTECZEK ====================

/**
 * Zapisuje dane postaci w ciasteczku
 */
if (!function_exists('setCharacterCookie')) {
    function setCharacterCookie($characterData) {
        $cookieData = [
            'pin' => $characterData['pin'],
            'hash1' => $characterData['hash1'],
            'hash2' => $characterData['hash2'],
            'name' => $characterData['name'] ?? '',
            'id' => $characterData['id'] ?? ''
        ];
        
        $cookieValue = base64_encode(json_encode($cookieData));
        setcookie('rpg_character', $cookieValue, time() + (86400 * 30), '/', '', false, true);
    }
}

/**
 * Pobiera dane postaci z ciasteczka
 */
if (!function_exists('getCharacterFromCookie')) {
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
}

/**
 * Usuwa ciasteczko postaci
 */
if (!function_exists('clearCharacterCookie')) {
    function clearCharacterCookie() {
        setcookie('rpg_character', '', time() - 3600, '/');
    }
}

// ==================== FUNKCJE FORMATOWANIA ====================

/**
 * Formatuje czas jako "X minut temu"
 */
if (!function_exists('formatTimeAgo')) {
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
}

/**
 * Formatuje liczbę jako wartość ze spacjami
 */
if (!function_exists('formatNumber')) {
    function formatNumber($number) {
        return number_format($number, 0, ',', ' ');
    }
}

/**
 * Formatuje procent
 */
if (!function_exists('formatPercent')) {
    function formatPercent($value, $decimals = 1) {
        return number_format($value * 100, $decimals, ',', ' ') . '%';
    }
}

/**
 * Formatuje datę w formacie polskim
 */
if (!function_exists('formatDate')) {
    function formatDate($datetime, $format = 'd.m.Y H:i') {
        if (empty($datetime)) {
            return 'nieznana';
        }
        return date($format, strtotime($datetime));
    }
}

// ==================== FUNKCJE URZĄDZEŃ ====================

/**
 * Sprawdza czy użytkownik używa urządzenia mobilnego
 */
if (!function_exists('isMobile')) {
    function isMobile() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/', $userAgent);
    }
}

/**
 * Sprawdza czy użytkownik używa tabletu
 */
if (!function_exists('isTablet')) {
    function isTablet() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/iPad|Android(?!.*Mobile)/', $userAgent);
    }
}

/**
 * Sprawdza czy to desktop
 */
if (!function_exists('isDesktop')) {
    function isDesktop() {
        return !isMobile() && !isTablet();
    }
}

// ==================== FUNKCJE POMOCNICZE ====================

/**
 * Przekierowuje z komunikatem flash
 */
if (!function_exists('redirectWithMessage')) {
    function redirectWithMessage($url, $message, $type = 'success') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        header("Location: $url");
        exit;
    }
}

/**
 * Pobiera i usuwa komunikat flash
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'] ?? 'info';
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            return ['message' => $message, 'type' => $type];
        }
        return null;
    }
}

/**
 * Loguje błąd do pliku
 */
if (!function_exists('logError')) {
    function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$timestamp] $message $contextStr" . PHP_EOL;
        
        $logFile = __DIR__ . '/../logs/error.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

?>