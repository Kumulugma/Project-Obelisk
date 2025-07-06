<?php

/**
 * Główna klasa Character - teraz znacznie uproszczona
 * Deleguje odpowiedzialności do specjalistycznych klas
 */
class Character {
    private $db;
    private $auth;
    private $manager;
    private $social;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new CharacterAuth();
        $this->manager = new CharacterManager();
        $this->social = new CharacterSocial();
    }
    
    // ==================== METODY AUTORYZACJI ====================
    
    /**
     * Pobiera postać po PIN
     */
    public function getByPin($pin) {
        return $this->auth->authenticateByPin($pin);
    }
    
    /**
     * Pobiera postać po hashach
     */
    public function getByHashes($hash1, $hash2) {
        return $this->auth->authenticateByHashes($hash1, $hash2);
    }
    
    /**
     * Automatyczne logowanie z ciasteczka
     */
    public function autoLogin() {
        return $this->auth->autoLoginFromCookie();
    }
    
    /**
     * Zapisuje dane do ciasteczka
     */
    public function saveToCookie($characterData) {
        $this->auth->setCharacterCookie($characterData);
    }
    
    /**
     * Usuwa ciasteczko
     */
    public function clearCookie() {
        $this->auth->clearCharacterCookie();
    }
    
    /**
     * Aktualizuje ostatnie logowanie
     */
    public function updateLastLogin($characterId) {
        $this->auth->updateLastLogin($characterId);
    }
    
    /**
     * Pobiera URL profilu
     */
    public function getProfileUrl($characterData) {
        return $this->auth->getProfileUrl($characterData);
    }
    
    /**
     * Sprawdza status rejestracji
     */
    public function getRegistrationStatus() {
        return $this->auth->getRegistrationStatus();
    }
    
    // ==================== METODY ZARZĄDZANIA POSTACIĄ ====================
    
    /**
     * Tworzy nową postać
     */
    public function create($name, $gender = 'male', $secretCode = null) {
        return $this->manager->create($name, $gender, $secretCode);
    }
    
    /**
     * Pobiera postać po ID
     */
    public function getById($id) {
        return $this->manager->getById($id);
    }
    
    /**
     * Resetuje dzienne punkty
     */
    public function resetDailyPoints($characterId) {
        $this->manager->resetDailyPoints($characterId);
    }
    
    /**
     * Używa punkt energii
     */
    public function useEnergyPoint($characterId) {
        return $this->manager->useEnergyPoint($characterId);
    }
    
    /**
     * Używa punkt wyzwania
     */
    public function useChallengePoint($characterId) {
        return $this->manager->useChallengePoint($characterId);
    }
    
    /**
     * Dodaje doświadczenie
     */
    public function addExperience($characterId, $exp) {
        $this->manager->addExperience($characterId, $exp);
    }
    
    /**
     * Zmienia avatar
     */
    public function changeAvatar($characterId, $avatarPath) {
        $this->manager->changeAvatar($characterId, $avatarPath);
    }
    
    /**
     * Pobiera dostępne avatary
     */
    public function getAvailableAvatars($gender = null) {
        return $this->manager->getAvailableAvatars($gender);
    }
    
    /**
     * Pobiera losowych przeciwników
     */
    public function getRandomOpponents($characterId, $count = 10) {
        return $this->manager->getRandomOpponents($characterId, $count);
    }
    
    /**
     * Pobiera ostatnie walki
     */
    public function getRecentBattles($characterId, $limit = 20) {
        return $this->manager->getRecentBattles($characterId, $limit);
    }
    
    // ==================== METODY SPOŁECZNE ====================
    
    /**
     * Dodaje znajomego
     */
    public function addFriend($characterId, $friendId) {
        $this->social->addFriend($characterId, $friendId);
    }
    
    /**
     * Usuwa znajomego
     */
    public function removeFriend($characterId, $friendId) {
        $this->social->removeFriend($characterId, $friendId);
    }
    
    /**
     * Pobiera znajomych
     */
    public function getFriends($characterId) {
        return $this->social->getFriends($characterId);
    }
    
    /**
     * Sprawdza czy są znajomymi
     */
    public function areFriends($characterId, $friendId) {
        return $this->social->areFriends($characterId, $friendId);
    }
    
    /**
     * Pobiera cechy postaci
     */
    public function getTraits($characterId) {
        return $this->social->getTraits($characterId);
    }
    
    /**
     * Dodaje cechę
     */
    public function addTrait($characterId, $traitId) {
        $this->social->addTrait($characterId, $traitId);
    }
    
    /**
     * Usuwa cechę
     */
    public function removeTrait($characterId, $traitId) {
        $this->social->removeTrait($characterId, $traitId);
    }
    
    /**
     * Sprawdza czy ma cechę
     */
    public function hasTrait($characterId, $traitId) {
        return $this->social->hasTrait($characterId, $traitId);
    }
    
    /**
     * Dodaje broń
     */
    public function addWeapon($characterId, $weaponId) {
        $this->social->addWeapon($characterId, $weaponId);
    }
    
    /**
     * Usuwa broń
     */
    public function removeWeapon($characterId, $weaponId) {
        $this->social->removeWeapon($characterId, $weaponId);
    }
    
    /**
     * Zakłada broń
     */
    public function equipWeapon($characterId, $weaponId) {
        $this->social->equipWeapon($characterId, $weaponId);
    }
    
    /**
     * Pobiera bronie postaci
     */
    public function getWeapons($characterId) {
        return $this->social->getWeapons($characterId);
    }
    
    /**
     * Pobiera wszystkie bronie
     */
    public function getAllWeapons() {
        return $this->social->getAllWeapons();
    }
    
    /**
     * Pobiera wszystkie cechy
     */
    public function getAllTraits() {
        return $this->social->getAllTraits();
    }
    
    /**
     * Sprawdza czy może dodać więcej znajomych
     */
    public function canAddMoreFriends($characterId) {
        return $this->social->canAddMoreFriends($characterId);
    }
    
    /**
     * Pobiera statystyki społeczne
     */
    public function getSocialStats($characterId) {
        return $this->social->getSocialStats($characterId);
    }
    
    // ==================== METODY POMOCNICZE ====================
    
    /**
     * Pobiera instancję bazy danych (dla kompatybilności wstecznej)
     */
    public function getDatabase() {
        return $this->db;
    }
    
    /**
     * Pobiera instancję klasy autoryzacji
     */
    public function getAuth() {
        return $this->auth;
    }
    
    /**
     * Pobiera instancję managera postaci
     */
    public function getManager() {
        return $this->manager;
    }
    
    /**
     * Pobiera instancję klasy społecznej
     */
    public function getSocial() {
        return $this->social;
    }
}

?>