CREATE DATABASE IF NOT EXISTS rpg_game CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rpg_game;

CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE characters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    pin VARCHAR(6) UNIQUE NOT NULL,
    hash1 VARCHAR(64) NOT NULL,
    hash2 VARCHAR(64) NOT NULL,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    health INT DEFAULT 100,
    max_health INT DEFAULT 100,
    stamina INT DEFAULT 50,
    max_stamina INT DEFAULT 50,
    damage INT DEFAULT 10,
    dexterity INT DEFAULT 10,
    agility INT DEFAULT 10,
    armor INT DEFAULT 5,
    max_armor INT DEFAULT 5,
    armor_penetration INT DEFAULT 0,
    energy_points INT DEFAULT 10,
    challenge_points INT DEFAULT 2,
    avatar_image VARCHAR(255),
    equipped_weapon_id INT DEFAULT NULL,
    last_energy_reset DATE DEFAULT (CURDATE()),
    last_challenge_reset DATE DEFAULT (CURDATE()),
    last_weapon_change DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pin (pin),
    INDEX idx_hashes (hash1, hash2)
);

CREATE TABLE weapons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    damage INT NOT NULL,
    armor_penetration INT DEFAULT 0,
    drop_chance DECIMAL(6,5) DEFAULT 0.00001,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE character_weapons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT,
    weapon_id INT,
    is_equipped BOOLEAN DEFAULT FALSE,
    obtained_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (weapon_id) REFERENCES weapons(id)
);

CREATE TABLE traits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('passive', 'active', 'modifier') NOT NULL,
    effect_type VARCHAR(50),
    effect_target VARCHAR(50),
    effect_value INT,
    effect_duration INT,
    trigger_chance DECIMAL(5,4),
    drop_chance DECIMAL(5,4) DEFAULT 0.1,
    image_path VARCHAR(255),
    avatar_modifier VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE character_traits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT,
    trait_id INT,
    obtained_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (trait_id) REFERENCES traits(id)
);

CREATE TABLE character_friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT,
    friend_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (friend_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY unique_friendship (character_id, friend_id)
);

CREATE TABLE battles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attacker_id INT,
    defender_id INT,
    winner_id INT,
    battle_log TEXT,
    experience_gained INT,
    weapon_dropped INT DEFAULT NULL,
    trait_dropped INT DEFAULT NULL,
    battle_type ENUM('random', 'challenge') DEFAULT 'random',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attacker_id) REFERENCES characters(id),
    FOREIGN KEY (defender_id) REFERENCES characters(id),
    FOREIGN KEY (winner_id) REFERENCES characters(id),
    FOREIGN KEY (weapon_dropped) REFERENCES weapons(id),
    FOREIGN KEY (trait_dropped) REFERENCES traits(id),
    INDEX idx_battles_participants (attacker_id, defender_id),
    INDEX idx_battles_created (created_at)
);

CREATE TABLE avatar_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE secret_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    uses_left INT DEFAULT 1,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
