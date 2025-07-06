{include file="header.tpl" page_title="Znajomi - {$character.name}"}

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Wyszukiwanie znajomych -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-search"></i> Wyszukaj Graczy</h4>
                </div>
                <div class="card-body">
                    {if $message}
                        <div class="alert alert-{if $message_type == 'error'}danger{elseif $message_type == 'warning'}warning{elseif $message_type == 'info'}info{else}success{/if}">
                            {$message}
                        </div>
                    {/if}
                    
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="search_friends">
                        <div class="input-group">
                            <input type="text" name="search_query" class="form-control" 
                                   placeholder="Wpisz imię gracza..." value="{$search_query}" 
                                   minlength="2" maxlength="50" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Szukaj
                            </button>
                        </div>
                        <small class="form-text text-muted">Wpisz co najmniej 2 znaki</small>
                    </form>
                    
                    {if $search_results}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Imię</th>
                                        <th>Poziom</th>
                                        <th>Status</th>
                                        <th>Ostatnia aktywność</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $search_results as $player}
                                        <tr>
                                            <td>
                                                <img src="{$player.avatar_image}" alt="Avatar" 
                                                     class="rounded-circle" width="40" height="40">
                                            </td>
                                            <td>
                                                <strong>{$player.name}</strong>
                                                {if $player.gender == 'male'}
                                                    <i class="fas fa-mars text-primary" title="Mężczyzna"></i>
                                                {else}
                                                    <i class="fas fa-venus text-danger" title="Kobieta"></i>
                                                {/if}
                                            </td>
                                            <td>
                                                <span class="badge bg-info">Lv. {$player.level}</span>
                                            </td>
                                            <td>
                                                {if $player.is_online}
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-circle"></i> Online
                                                    </span>
                                                {else}
                                                    <span class="badge bg-secondary">
                                                        <i class="far fa-circle"></i> Offline
                                                    </span>
                                                {/if}
                                            </td>
                                            <td>
                                                <small class="text-muted">{$player.last_seen}</small>
                                            </td>
                                            <td>
                                                {if $player.is_friend}
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Znajomy
                                                    </span>
                                                {else}
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="add_friend">
                                                        <input type="hidden" name="friend_id" value="{$player.id}">
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-user-plus"></i> Dodaj
                                                        </button>
                                                    </form>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {/if}
                </div>
            </div>
            
            <!-- Lista znajomych -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users"></i> Moi Znajomi</h4>
                    <span class="badge bg-primary">{$friends_count}/{$max_friends}</span>
                </div>
                <div class="card-body">
                    {if $current_friends}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Imię</th>
                                        <th>Poziom</th>
                                        <th>Status</th>
                                        <th>Ostatnia aktywność</th>
                                        <th>Znajomość od</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $current_friends as $friend}
                                        <tr {if $friend.is_online}class="table-success"{/if}>
                                            <td>
                                                <img src="{$friend.avatar_image}" alt="Avatar" 
                                                     class="rounded-circle" width="40" height="40">
                                            </td>
                                            <td>
                                                <strong>{$friend.name}</strong>
                                                {if $friend.gender == 'male'}
                                                    <i class="fas fa-mars text-primary" title="Mężczyzna"></i>
                                                {else}
                                                    <i class="fas fa-venus text-danger" title="Kobieta"></i>
                                                {/if}
                                            </td>
                                            <td>
                                                <span class="badge bg-info">Lv. {$friend.level}</span>
                                            </td>
                                            <td>
                                                {if $friend.is_online}
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-circle"></i> Online
                                                    </span>
                                                {else}
                                                    <span class="badge bg-secondary">
                                                        <i class="far fa-circle"></i> Offline
                                                    </span>
                                                {/if}
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {assign var="last_seen" value=$friend.last_login|strtotime}
                                                    {assign var="now" value=$smarty.now}
                                                    {assign var="diff" value=$now-$last_seen}
                                                    
                                                    {if $diff < 1800}
                                                        Online
                                                    {elseif $diff < 3600}
                                                        {math equation="floor(x/60)" x=$diff} min temu
                                                    {elseif $diff < 86400}
                                                        {math equation="floor(x/3600)" x=$diff} godz temu
                                                    {else}
                                                        {math equation="floor(x/86400)" x=$diff} dni temu
                                                    {/if}
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {$friend.friendship_date|date_format:"%d.%m.%Y"}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <!-- Przycisk wyzwania do walki -->
                                                    <form method="POST" action="/{$character.hash1}/{$character.hash2}" style="display: inline;">
                                                        <input type="hidden" name="action" value="battle_friend">
                                                        <input type="hidden" name="friend_id" value="{$friend.id}">
                                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                                {if $character.challenge_points <= 0}disabled title="Brak punktów wyzwań"{/if}>
                                                            <i class="fas fa-fist-raised"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Przycisk usunięcia -->
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="remove_friend">
                                                        <input type="hidden" name="friend_id" value="{$friend.id}">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('Czy na pewno chcesz usunąć {$friend.name} ze znajomych?')">
                                                            <i class="fas fa-user-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nie masz jeszcze znajomych</h5>
                            <p class="text-muted">Użyj wyszukiwarki powyżej, aby znaleźć innych graczy!</p>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
        
        <!-- Panel boczny -->
        <div class="col-md-4">
            <!-- Statystyki -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Statystyki Znajomych</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{$friends_count}</h4>
                            <small class="text-muted">Znajomych</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{$online_friends_count}</h4>
                            <small class="text-muted">Online</small>
                        </div>
                    </div>
                    
                    <div class="progress mt-3">
                        {assign var="progress_percent" value=($friends_count/$max_friends)*100}
                        <div class="progress-bar" role="progressbar" 
                             style="width: {$progress_percent}%" 
                             aria-valuenow="{$friends_count}" 
                             aria-valuemin="0" 
                             aria-valuemax="{$max_friends}">
                            {$friends_count}/{$max_friends}
                        </div>
                    </div>
                    <small class="text-muted">Limit znajomych</small>
                </div>
            </div>
            
            <!-- Punkty -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-battery-half"></i> Punkty</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Punkty energii:</span>
                            <span class="badge bg-primary">{$character.energy_points}</span>
                        </div>
                        <small class="text-muted">Do losowych walk</small>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Punkty wyzwań:</span>
                            <span class="badge bg-warning">{$character.challenge_points}</span>
                        </div>
                        <small class="text-muted">Do walki ze znajomymi</small>
                    </div>
                </div>
            </div>
            
            <!-- Nawigacja -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-compass"></i> Nawigacja</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/{$character.hash1}/{$character.hash2}" class="btn btn-primary">
                            <i class="fas fa-user"></i> Profil
                        </a>
                        <a href="/weapons/{$character.hash1}/{$character.hash2}" class="btn btn-secondary">
                            <i class="fas fa-sword"></i> Bronie
                        </a>
                        <a href="/battles/{$character.hash1}/{$character.hash2}" class="btn btn-info">
                            <i class="fas fa-history"></i> Historia walk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}