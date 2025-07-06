<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walka: {$battle.attacker_name} vs {$battle.defender_name} - RPG Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/game.css" rel="stylesheet">
    <style>
        .battle-arena {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .battle-arena::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .battle-header {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #ff6b6b;
            color: white;
            padding: 20px 0;
            position: relative;
            z-index: 2;
        }
        
        .fighter-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .fighter-card.winner {
            border-color: #28a745;
            box-shadow: 0 0 30px rgba(40, 167, 69, 0.5);
        }
        
        .fighter-card.loser {
            border-color: #dc3545;
            opacity: 0.8;
            filter: grayscale(0.3);
        }
        
        .fighter-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #ddd;
            object-fit: cover;
            margin: 0 auto 15px;
            display: block;
            transition: all 0.3s ease;
        }
        
        .fighter-card.winner .fighter-avatar {
            border-color: #28a745;
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.5);
        }
        
        .fighter-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }
        
        .stat-badge {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 8px 12px;
            text-align: center;
            font-size: 0.9em;
        }
        
        .vs-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, #ff6b6b, #ee5a52);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            font-weight: bold;
            box-shadow: 0 0 20px rgba(255, 107, 107, 0.6);
            z-index: 10;
            border: 4px solid white;
        }
        
        .result-banner {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .result-banner.draw {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }
        
        .rewards-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .battle-log-container {
            background: rgba(248, 249, 250, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
        }
        
        .battle-round {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        
        .battle-round:hover {
            transform: translateX(5px);
        }
        
        .battle-round.attack {
            border-left-color: #dc3545;
        }
        
        .battle-round.effect {
            border-left-color: #ffc107;
            background: linear-gradient(to right, #fff3cd, white);
        }
        
        .battle-round.exhaustion {
            border-left-color: #6f42c1;
            background: linear-gradient(to right, #f8d7da, white);
        }
        
        .round-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8em;
            margin-right: 10px;
        }
        
        .damage-indicator {
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .trait-activation {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            margin: 5px 2px;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.3);
        }
        
        .health-status {
            margin-top: 10px;
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .action-buttons {
            position: sticky;
            bottom: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #dee2e6;
        }
        
        .crown-icon {
            color: #ffc107;
            font-size: 1.2em;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .exp-gain {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 5px;
        }
        
        .timeline-connector {
            width: 2px;
            height: 20px;
            background: #dee2e6;
            margin: 0 auto;
        }
        
        /* Animacje */
        .fighter-card {
            animation: slideIn 0.6s ease-out;
        }
        
        .battle-round {
            animation: fadeInUp 0.4s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .vs-indicator {
                position: relative;
                margin: 20px auto;
                transform: none;
            }
            
            .fighter-avatar {
                width: 80px;
                height: 80px;
            }
            
            .battle-log-container {
                max-height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="battle-arena">
        <!-- Header -->
        <div class="battle-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="mb-0">
                            <i class="fas fa-swords"></i> Arena Walki
                        </h2>
                        <p class="mb-0 opacity-75">Wyniki pojedynku</p>
                    </div>
                    <div class="col-auto">
                        <button onclick="history.back()" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left"></i> Powrót
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-4 position-relative">
            <!-- Wynik walki -->
            <div class="result-banner {if !$battle.winner_name}draw{/if}">
                {if $battle.winner_name}
                    <h3 class="mb-2">
                        <i class="fas fa-crown crown-icon"></i>
                        Zwycięzca: {$battle.winner_name}!
                    </h3>
                    <p class="mb-0">Gratulacje dla zwycięzcy tej epicka walki!</p>
                {else}
                    <h3 class="mb-2">
                        <i class="fas fa-handshake"></i>
                        Remis!
                    </h3>
                    <p class="mb-0">Obaj wojownicy pokazali równą siłę</p>
                {/if}
                
                {if $battle.experience_gained > 0}
                    <div class="mt-3">
                        <span class="exp-gain">
                            <i class="fas fa-star"></i> +{$battle.experience_gained} EXP
                        </span>
                    </div>
                {/if}
            </div>

            <!-- Karty wojowników -->
            <div class="row position-relative mb-4">
                <div class="col-md-5">
                    <div class="fighter-card {if $battle.winner_name == $battle.attacker_name}winner{elseif $battle.winner_name}loser{/if}">
                        <img src="{if $battle.attacker_avatar}{$battle.attacker_avatar}{else}/images/avatars/default.png{/if}" 
                             alt="{$battle.attacker_name}" 
                             class="fighter-avatar"
                             onerror="this.src='/images/avatars/default.png'">
                        
                        <h4 class="text-center mb-0">{$battle.attacker_name}</h4>
                        <p class="text-center text-muted">Atakujący</p>
                        
                        <div class="fighter-stats">
                            <div class="stat-badge">
                                <i class="fas fa-level-up-alt text-warning"></i><br>
                                <strong>{$battle.attacker_level}</strong><br>
                                <small>Poziom</small>
                            </div>
                            <div class="stat-badge">
                                <i class="fas fa-sword text-danger"></i><br>
                                <strong>{$battle.attacker_damage|default:"?"}</strong><br>
                                <small>Obrażenia</small>
                            </div>
                        </div>
                        
                        {if $battle.winner_name == $battle.attacker_name}
                            <div class="text-center mt-3">
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-trophy"></i> Zwycięzca
                                </span>
                            </div>
                        {/if}
                    </div>
                </div>
                
                <!-- VS Indicator -->
                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <div class="vs-indicator">
                        VS
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="fighter-card {if $battle.winner_name == $battle.defender_name}winner{elseif $battle.winner_name}loser{/if}">
                        <img src="{if $battle.defender_avatar}{$battle.defender_avatar}{else}/images/avatars/default.png{/if}" 
                             alt="{$battle.defender_name}" 
                             class="fighter-avatar"
                             onerror="this.src='/images/avatars/default.png'">
                        
                        <h4 class="text-center mb-0">{$battle.defender_name}</h4>
                        <p class="text-center text-muted">Obrońca</p>
                        
                        <div class="fighter-stats">
                            <div class="stat-badge">
                                <i class="fas fa-level-up-alt text-warning"></i><br>
                                <strong>{$battle.defender_level}</strong><br>
                                <small>Poziom</small>
                            </div>
                            <div class="stat-badge">
                                <i class="fas fa-shield-alt text-info"></i><br>
                                <strong>{$battle.defender_armor|default:"?"}</strong><br>
                                <small>Pancerz</small>
                            </div>
                        </div>
                        
                        {if $battle.winner_name == $battle.defender_name}
                            <div class="text-center mt-3">
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-trophy"></i> Zwycięzca
                                </span>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Nagrody -->
            {if $weapon_reward || $trait_reward}
            <div class="rewards-section">
                <h4 class="mb-3">
                    <i class="fas fa-gift"></i> Nagrody z walki
                </h4>
                <div class="row">
                    {if $weapon_reward}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-sword fa-2x me-3"></i>
                            <div>
                                <strong>{$weapon_reward.name}</strong><br>
                                <small>+{$weapon_reward.damage} obrażeń</small>
                                {if $weapon_reward.armor_penetration > 0}
                                    <br><small>+{$weapon_reward.armor_penetration} przebicia</small>
                                {/if}
                            </div>
                        </div>
                    </div>
                    {/if}
                    
                    {if $trait_reward}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-magic fa-2x me-3"></i>
                            <div>
                                <strong>{$trait_reward.name}</strong><br>
                                <small>{$trait_reward.description|truncate:50}</small>
                            </div>
                        </div>
                    </div>
                    {/if}
                </div>
            </div>
            {/if}

            <!-- Log walki -->
            <div class="battle-log-container">
                <h5 class="mb-4">
                    <i class="fas fa-scroll"></i> Przebieg walki
                    <small class="text-muted">({if $battle.battle_log}{count($battle.battle_log)}{else}0{/if} akcji)</small>
                </h5>
                
                {if $battle.battle_log && count($battle.battle_log) > 0}
                    {foreach $battle.battle_log as $index => $log}
                        {* Debug - pokaż surowe dane *}
                        {* <pre style="font-size: 10px;">{$log|@debug_print_var}</pre> *}
                        
                        {if $log.type == 'passive_traits' || (!$log.type && ($log.message|strpos:'trait' !== false || $log.message|strpos:'Aktywne' !== false))}
                            <div class="battle-round effect">
                                <div class="d-flex align-items-start">
                                    <div class="round-number" style="background: #6f42c1;">
                                        <i class="fas fa-magic"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{$log.character|default:'System'}</strong> aktywuje pasywne umiejętności:
                                        <div class="mt-2 text-muted">
                                            {$log.message|default:'Aktywacja umiejętności'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        {elseif $log.type == 'attack' || ($log.attacker && $log.defender)}
                            <div class="battle-round attack">
                                <div class="d-flex align-items-start">
                                    <div class="round-number">
                                        {$log.round|default:($index+1)}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{$log.attacker|default:'Atakujący'}</strong> atakuje <strong>{$log.defender|default:'Przeciwnika'}</strong>
                                                {if $log.damage > 0}
                                                    <span class="damage-indicator">
                                                        <i class="fas fa-bolt"></i> -{$log.damage} HP
                                                    </span>
                                                {else}
                                                    <span class="badge bg-secondary ms-2">Pudło!</span>
                                                {/if}
                                            </div>
                                        </div>
                                        
                                        <div class="action-description mt-2">
                                            {$log.action|default:$log.message|default:'Atak'}
                                        </div>
                                        
                                        {if $log.defender_health || $log.defender_armor}
                                        <div class="health-status">
                                            {if $log.defender_health}<i class="fas fa-heart text-danger"></i> {$log.defender|default:'Przeciwnik'}: {$log.defender_health} HP{/if}
                                            {if $log.defender_armor}<i class="fas fa-shield-alt text-info ms-3"></i> {$log.defender_armor} Pancerz{/if}
                                        </div>
                                        {/if}
                                        
                                        {if $log.traits_activated && count($log.traits_activated) > 0}
                                            <div class="mt-2">
                                                {foreach $log.traits_activated as $trait}
                                                    <span class="trait-activation">
                                                        <i class="fas fa-magic"></i> {$trait.name|default:'Umiejętność'}
                                                    </span>
                                                {/foreach}
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            
                        {elseif $log.type == 'effect' || $log.type == 'result'}
                            <div class="battle-round effect">
                                <div class="d-flex align-items-start">
                                    <div class="round-number" style="background: #ffc107;">
                                        <i class="fas fa-fire"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{$log.character|default:'System'}</strong>:
                                        <div class="mt-1 text-warning">
                                            <i class="fas fa-magic"></i> {$log.message|default:'Efekt specjalny'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        {elseif $log.type == 'exhaustion' || ($log.message|strpos:'Wyczerpanie' !== false)}
                            <div class="battle-round exhaustion">
                                <div class="d-flex align-items-start">
                                    <div class="round-number" style="background: #6f42c1;">
                                        <i class="fas fa-tired"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{$log.character|default:'Wojownik'}</strong> odczuwa wyczerpanie:
                                        <div class="mt-1 text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> {$log.message|default:'Wyczerpanie'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        {else}
                            {* Fallback dla nieznanych typów *}
                            <div class="battle-round">
                                <div class="d-flex align-items-start">
                                    <div class="round-number" style="background: #6c757d;">
                                        <i class="fas fa-info"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        {if $log.message}
                                            {$log.message}
                                        {elseif $log.attacker}
                                            <strong>{$log.attacker}</strong> vs <strong>{$log.defender|default:'Przeciwnik'}</strong>
                                        {else}
                                            Akcja w rundzie {$log.round|default:($index+1)}
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/if}
                        
                        {if !$log@last}
                            <div class="timeline-connector"></div>
                        {/if}
                    {/foreach}
                {else}
                    {* Jeśli brak logów, pokaż podstawowe informacje *}
                    <div class="battle-round">
                        <div class="d-flex align-items-start">
                            <div class="round-number" style="background: #007bff;">
                                1
                            </div>
                            <div class="flex-grow-1">
                                <strong>Walka zakończona</strong>
                                <div class="mt-2">
                                    <strong>{$battle.attacker_name}</strong> walczył przeciwko <strong>{$battle.defender_name}</strong>
                                    {if $battle.winner_name}
                                        <br>Zwycięzcą został: <strong>{$battle.winner_name}</strong>
                                    {else}
                                        <br>Walka zakończyła się remisem
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>

            <!-- Przyciski akcji -->
            <div class="action-buttons">
                <button onclick="history.back()" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-arrow-left"></i> Powrót do profilu
                </button>
                <button onclick="location.reload()" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i> Odśwież
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animacja stopniowego pokazywania log'ów walki
        document.addEventListener('DOMContentLoaded', function() {
            const rounds = document.querySelectorAll('.battle-round');
            rounds.forEach((round, index) => {
                round.style.opacity = '0';
                round.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    round.style.transition = 'all 0.4s ease';
                    round.style.opacity = '1';
                    round.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Automatyczne przewijanie do końca walki
            setTimeout(() => {
                const lastRound = rounds[rounds.length - 1];
                if (lastRound) {
                    lastRound.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, rounds.length * 100 + 500);
        });
        
        // Efekt hover dla kart wojowników
        document.querySelectorAll('.fighter-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>