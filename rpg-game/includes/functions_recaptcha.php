<?php
/**
 * Funkcje reCAPTCHA
 */

// ==================== FUNKCJE reCAPTCHA ====================

/**
 * Pobiera klucz publiczny reCAPTCHA z bazy danych
 */
if (!function_exists('getRecaptchaSiteKey')) {
    function getRecaptchaSiteKey() {
        try {
            $db = Database::getInstance();
            $setting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'recaptcha_site_key'");
            return $setting ? $setting['setting_value'] : '';
        } catch (Exception $e) {
            error_log("Error getting reCAPTCHA site key: " . $e->getMessage());
            return '';
        }
    }
}

/**
 * Pobiera klucz prywatny reCAPTCHA z bazy danych
 */
if (!function_exists('getRecaptchaSecretKey')) {
    function getRecaptchaSecretKey() {
        try {
            $db = Database::getInstance();
            $setting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'recaptcha_secret_key'");
            return $setting ? $setting['setting_value'] : '';
        } catch (Exception $e) {
            error_log("Error getting reCAPTCHA secret key: " . $e->getMessage());
            return '';
        }
    }
}

/**
 * Weryfikuje reCAPTCHA używając kluczy z bazy danych
 */
if (!function_exists('verifyRecaptchaFromDB')) {
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
}

/**
 * Weryfikuje reCAPTCHA - sprawdza czy funkcja już istnieje, żeby uniknąć konfliktów
 */
if (!function_exists('verifyRecaptcha')) {
    function verifyRecaptcha($response) {
        // Najpierw spróbuj użyć kluczy z bazy danych
        $secretKeyFromDB = getRecaptchaSecretKey();
        if (!empty($secretKeyFromDB)) {
            return verifyRecaptchaFromDB($response);
        }
        
        // Fallback do klucza z config.php (legacy)
        if (defined('RECAPTCHA_SECRET') && !empty(RECAPTCHA_SECRET)) {
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
        
        // Jeśli nie ma żadnych kluczy, pomiń weryfikację
        return true;
    }
}

?>