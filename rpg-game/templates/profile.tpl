<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$character.name} - RPG Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/game.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="game-container">
            <!-- Header -->
            <div class="bg-dark text-white p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0">
                            <i class="fas fa-dragon"></i> RPG Game
                        </h4>
                    </div>
                    <div class="col-auto">
                        <a href="{$site_url}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-home"></i> Strona główna
                        </a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            {if $message}
                <div class="p-3">
                    <div class="alert alert-{if $message_type == 'success'}success{else}danger{/if} alert-dismissible fade show">
                        <i class="fas {if $message_type == 'success'}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
                        {$message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            {/if}

            <div class="p-4">
                <div class="row">
                    <!-- Lewa kolumna - Profil postaci -->
                    <div class="col-lg-4">
                        <!-- Karta postaci -->
                        <div class="card mb-4">
                            <div class="card-header text-center">
                                <h4><i class="fas fa-user"></i> {$character.name}</h4>
                            </div>
                            <div class="card-body text-center">
                                <!-- Avatar -->
                                <div class="mb-4">
                                    <img src="{$formatted_stats.avatar}" alt="{$character.name}" class="character-avatar">
                                </div>
                                
                                <!-- Podstawowe info -->
                                <div class="row text-center mb-4">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value">{$character.level}</div>
                                            <div class="stat-label">Poziom</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value">{$character.damage}</div>
                                            <div class="stat-label">Obrażenia</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value">{$character.dexterity}</div>
                                            <div class="stat-label">Zręczność</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PIN (tylko jeśli ma ciasteczko) -->
                                {if $show_pin}
                                <div class="pin-display mb-3">
                                    <small>Twój PIN:</small><br>
                                    <strong>{$character.pin}</strong>
                                </div>
                                {/if}
                            </div>
                        </div>

                        <!-- Paski statusu -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-heart"></i> Status</h5>
                            </div>
                            <div class="card-body">
                                <!-- Zdrowie -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small><i class="fas fa-heart text-danger"></i> Zdrowie</small>
                                        <small class="text-muted">{$formatted_stats.formatted_stats.health}</small>
                                    </div>
                                    <div class="status-bar">
                                        <div class="status-bar-fill health-bar" style="width: {$formatted_stats.status_bars.health_percent}%">
                                            <div class="status-text">{$formatted_stats.status_bars.health_percent|string_format:"%.0f"}%</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wytrzymałość -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small><i class="fas fa-running text-success"></i> Wytrzymałość</small>
                                        <small class="text-muted">{$formatted_stats.formatted_stats.stamina}</small>
                                    </div>
                                    <div class="status-bar">
                                        <div class="status-bar-fill stamina-bar" style="width: {$formatted_stats.status_bars.stamina_percent}%">
                                            <div class="status-text">{$formatted_stats.status_bars.stamina_percent|string_format:"%.0f"}%</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pancerz -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small><i class="fas fa-shield-alt text-info"></i> Pancerz</small>
                                        <small class="text-muted">{$formatted_stats.formatted_stats.armor}</small>
                                    </div>
                                    <div class="status-bar">
                                        <div class="status-bar-fill armor-bar" style="width: {$formatted_stats.status_bars.armor_percent}%">
                                            <div class="status-text">{$formatted_stats.status_bars.armor_percent|string_format:"%.0f"}%</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Doświadczenie -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small><i class="fas fa-star text-warning"></i> Doświadczenie</small>
                                        <small class="text-muted">{$formatted_stats.formatted_stats.exp_to_next}</small>
                                    </div>
                                    <div class="status-bar">
                                        <div class="status-bar-fill experience-bar" style="width: {$formatted_stats.experience_info.progress_percent}%">
                                            <div class="status-text">{$formatted_stats.experience_info.progress_percent|string_format:"%.0f"}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Energia i wyzwania -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="energy-display">
                                    <span class="energy-number">{$character.energy_points}</span>
                                    <span class="energy-label">Energia</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="energy-display" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);">
                                    <span class="energy-number">{$character.challenge_points}</span>
                                    <span class="energy-label">Wyzwania</span>
                                </div>
                            </div>
                        </div>

                        <!-- Umiejętności (Traity) -->
                        {if $traits}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-magic"></i> Umiejętności</h5>
                            </div>
                            <div class="card-body">
                                {foreach $traits as $trait}
                                    <div class="trait-badge" title="{$trait.description}">
                                        {if $trait.image_path}
                                            <img src="{$trait.image_path}" alt="{$trait.name}" style="width: 16px; height: 16px;">
                                        {else}
                                            <i class="fas fa-magic"></i>
                                        {/if}
                                        {$trait.name}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {/if}
                    </div>

                    <!-- Środkowa kolumna - Akcje -->
                    <div class="col-lg-4">
                        <!-- Losowe walki -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-sword"></i> Losowi przeciwnicy</h5>
                            </div>
                            <div class="card-body">
                                {if $character.energy_points > 0}
                                    {foreach $opponents as $opponent}
                                        <div class="opponent-card">
                                            <div class="row align-items-center">
                                                <div class="col-3">
                                                    <img src="{$opponent.avatar}" alt="{$opponent.name}" class="character-mini-avatar">
                                                </div>
                                                <div class="col-6">
                                                    <strong>{$opponent.name}</strong><br>
                                                    <small class="text-muted">Poziom {$opponent.level}</small>
                                                </div>
                                                <div class="col-3">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="battle_random">
                                                        <input type="hidden" name="opponent_id" value="{$opponent.id}">
                                                        <button type="submit" class="btn btn-sm battle-btn">
                                                            <i class="fas fa-fist-raised"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    {foreachelse}
                                        <p class="text-muted text-center">Brak dostępnych przeciwników</p>
                                    {/foreach}
                                {else}
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Brak energii na walki!
                                    </div>
                                {/if}
                            </div>
                        </div>

                        <!-- Znajomi -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-users"></i> Znajomi</h5>
                            </div>
                            <div class="card-body">
                                <!-- Dodawanie znajomego -->
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="add_friend">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="friend_pin" placeholder="PIN znajomego" maxlength="6" required>
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </form>

                                <!-- Lista znajomych -->
                                {if $friends}
                                    {foreach $friends as $friend}
                                        <div class="friend-card">
                                            <div class="row align-items-center">
                                                <div class="col-3">
                                                    <img src="{$friend.avatar}" alt="{$friend.name}" class="character-mini-avatar">
                                                </div>
                                                <div class="col-5">
                                                    <strong>{$friend.name}</strong><br>
                                                    <small class="text-muted">Poziom {$friend.level}</small>
                                                </div>
                                                <div class="col-4">
                                                    {if $character.challenge_points > 0}
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="battle_friend">
                                                            <input type="hidden" name="friend_id" value="{$friend.id}">
                                                            <button type="submit" class="btn btn-sm battle-btn me-1" title="Wyzwij">
                                                                <i class="fas fa-fist-raised"></i>
                                                            </button>
                                                        </form>
                                                    {/if}
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="remove_friend">
                                                        <input type="hidden" name="friend_id" value="{$friend.id}">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Usuń" onclick="return confirm('Usunąć znajomego?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                {else}
                                    <p class="text-muted text-center">Brak znajomych</p>
                                {/if}
                            </div>
                        </div>
                    </div>

                    <!-- Prawa kolumna - Historia i ekwipunek -->
                    <div class="col-lg-4">
                        <!-- Ostatnie walki -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Ostatnie walki</h5>
                            </div>
                            <div class="card-body">
                                {if $recent_battles}
                                    {foreach $recent_battles as $battle}
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                            <div>
                                                <small>
                                                    {if $battle.attacker_id == $character.id}
                                                        vs {$battle.defender_name}
                                                    {else}
                                                        {$battle.attacker_name} vs Ty
                                                    {/if}
                                                </small>
                                            </div>
                                            <div>
                                                {if $battle.winner_id == $character.id}
                                                    <span class="badge bg-success">Wygrana</span>
                                                {elseif $battle.winner_id == null}
                                                    <span class="badge bg-warning">Remis</span>
                                                {else}
                                                    <span class="badge bg-danger">Przegrana</span>
                                                {/if}
                                            </div>
                                        </div>
                                    {/foreach}
                                {else}
                                    <p class="text-muted text-center">Brak walk w historii</p>
                                {/if}
                            </div>
                        </div>

                        <!-- Ekwipunek -->
                        {if $weapons}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-sword"></i> Ekwipunek</h5>
                            </div>
                            <div class="card-body">
                                {foreach $weapons as $weapon}
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                        <div>
                                            <strong>{$weapon.name}</strong>
                                            {if $weapon.is_equipped}
                                                <span class="badge bg-primary ms-1">Założona</span>
                                            {/if}
                                            <br>
                                            <small class="text-muted">
                                                Obrażenia: +{$weapon.damage_bonus}
                                                {if $weapon.armor_penetration > 0}, Przebicie: {$weapon.armor_penetration}{/if}
                                            </small>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {/if}

                        <!-- Dodatkowe statystyki -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar"></i> Statystyki</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="stat-item">
                                            <div class="stat-value">{$character.agility}</div>
                                            <div class="stat-label">Zwinność</div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-item">
                                            <div class="stat-value">{$character.armor_penetration}</div>
                                            <div class="stat-label">Przebicie</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> 
                                            Utworzono: {$character.created_at|date_format:"%d.%m.%Y"}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>