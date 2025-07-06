<?php

/**
 * Plik ładujący wszystkie klasy związane z postaciami
 * Użyj tego pliku zamiast bezpośredniego include Character.php
 */

// Sprawdź czy już załadowane
if (!class_exists('CharacterAuth')) {
    
    // Załaduj klasy w odpowiedniej kolejności
    require_once __DIR__ . '/CharacterAuth.php';
    require_once __DIR__ . '/CharacterManager.php'; 
    require_once __DIR__ . '/CharacterSocial.php';
    require_once __DIR__ . '/Character.php';
    
    // Opcjonalnie: sprawdź czy wszystkie klasy zostały załadowane
    $requiredClasses = ['CharacterAuth', 'CharacterManager', 'CharacterSocial', 'Character'];
    foreach ($requiredClasses as $className) {
        if (!class_exists($className)) {
            throw new Exception("Nie udało się załadować klasy: $className");
        }
    }
}

?>