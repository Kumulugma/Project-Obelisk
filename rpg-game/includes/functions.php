<?php
/**
 * Główny plik funkcji - ładuje wszystkie moduły funkcji
 * Użyj tego pliku zamiast bezpośredniego includu poszczególnych plików
 */

// Sprawdź czy już załadowane
if (!defined('RPG_FUNCTIONS_LOADED')) {
    define('RPG_FUNCTIONS_LOADED', true);
    
    // Załaduj moduły funkcji w odpowiedniej kolejności
    require_once __DIR__ . '/functions_core.php';       // Funkcje podstawowe
    require_once __DIR__ . '/functions_database.php';   // Funkcje bazy danych
    require_once __DIR__ . '/functions_recaptcha.php';  // Funkcje reCAPTCHA
    require_once __DIR__ . '/functions_helpers.php';    // Funkcje pomocnicze
    require_once __DIR__ . '/functions_admin.php';      // Funkcje administracyjne
    
    // Sprawdź czy wszystkie kluczowe funkcje zostały załadowane
    $requiredFunctions = [
        'sanitizeInput',
        'validateCharacterName', 
        'validatePIN',
        'verifyRecaptcha',
        'getSystemSetting',
        'setCharacterCookie',
        'formatTimeAgo',
        'getSystemStats'  // Dodana nowa funkcja
    ];
    
    $missingFunctions = [];
    foreach ($requiredFunctions as $functionName) {
        if (!function_exists($functionName)) {
            $missingFunctions[] = $functionName;
        }
    }
    
    if (!empty($missingFunctions)) {
        error_log("Missing required functions: " . implode(', ', $missingFunctions));
    }
}

?>