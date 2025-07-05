USE rpg_game;

INSERT INTO admin_users (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO weapons (name, damage, armor_penetration, drop_chance, image_path) VALUES
('Pięść', 8, 0, 0, '/images/weapons/fist.png'),
('Miecz Żelazny', 15, 2, 0.05, '/images/weapons/iron_sword.png'),
('Topór Bojowy', 18, 3, 0.03, '/images/weapons/battle_axe.png'),
('Łuk Długi', 12, 1, 0.04, '/images/weapons/longbow.png'),
('Sztylet Trucizny', 20, 5, 0.01, '/images/weapons/poison_dagger.png'),
('Młot Wojny', 25, 8, 0.005, '/images/weapons/war_hammer.png');

INSERT INTO traits (name, description, type, effect_type, effect_target, effect_value, trigger_chance, drop_chance, image_path) VALUES
('Berserker', 'Zwiększa obrażenia o 5 punktów', 'passive', 'damage_boost', 'self', 5, 1.0, 0.1, '/images/traits/berserker.png'),
('Szybkie Stopy', 'Zwiększa zwinność o 3 punkty', 'passive', 'agility_boost', 'self', 3, 1.0, 0.15, '/images/traits/quick_feet.png'),
('Żelazna Skóra', 'Zwiększa pancerz o 3 punkty', 'passive', 'armor_boost', 'self', 3, 1.0, 0.12, '/images/traits/iron_skin.png'),
('Celny Strzał', 'Zwiększa zręczność o 4 punkty', 'passive', 'dexterity_boost', 'self', 4, 1.0, 0.12, '/images/traits/precise_shot.png'),
('Wytrzymałość', 'Zwiększa wytrzymałość o 10 punktów', 'passive', 'stamina_boost', 'self', 10, 1.0, 0.08, '/images/traits/endurance.png'),
('Podpalenie', 'Szansa na podpalenie przeciwnika na 3 tury (4 obr/turę)', 'active', 'burn', 'enemy', 4, 0.2, 0.08, '/images/traits/burn.png'),
('Krytyczny Cios', 'Szansa na podwójne obrażenia', 'active', 'critical_hit', 'self', 2, 0.15, 0.06, '/images/traits/critical.png'),
('Regeneracja', 'Szansa na odnowienie 8 zdrowia', 'active', 'heal', 'self', 8, 0.1, 0.05, '/images/traits/regeneration.png'),
('Parowanie', 'Szansa na zablokowanie ataku', 'active', 'block', 'self', 1, 0.12, 0.07, '/images/traits/parry.png'),
('Przebicie Pancerza', 'Zwiększa przebicie pancerza o 6 punktów', 'passive', 'penetration_boost', 'self', 6, 1.0, 0.04, '/images/traits/armor_pierce.png');

INSERT INTO avatar_images (image_path) VALUES
('/images/avatars/warrior1.png'),
('/images/avatars/warrior2.png'),
('/images/avatars/warrior3.png'),
('/images/avatars/mage1.png'),
('/images/avatars/mage2.png'),
('/images/avatars/mage3.png'),
('/images/avatars/rogue1.png'),
('/images/avatars/rogue2.png'),
('/images/avatars/rogue3.png'),
('/images/avatars/knight1.png');

INSERT INTO system_settings (setting_key, setting_value) VALUES
('max_characters', '1000'),
('daily_energy_points', '10'),
('daily_challenge_points', '2'),
('max_friends', '10'),
('experience_per_level', '100');

INSERT INTO secret_codes (code, uses_left, description) VALUES
('PREMIUM2024', 100, 'Kod pozwalający na stworzenie postaci mimo limitu'),
('BETATEST', 50, 'Kod dla beta testerów'),
('UNLIMITED', -1, 'Kod bez limitu użyć');