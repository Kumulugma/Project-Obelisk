{include file="header.tpl" page_title="Profil - {$character.name}"}

<div class="container mt-3">
    {if $message}
        <div class="alert alert-{if $message_type == 'error'}danger{else}success{/if}">
            {$message}
        </div>
    {/if}
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <img src="{$character.avatar_image}" alt="Avatar" class="rounded-circle" width="80" height="80">
                    <h4 class="mt-2">{$character.name}</h4>
                    <span class="badge bg-primary">Poziom {$character.level}</span>
                </div>
                
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Zdrowie:</span>
                            <span>{$character.health}/{$character.max_health}</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill health-bar" style="width: {($character.health / $character.max_health * 100)|string_format:"%.0f"}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Wytrzymałość:</span>
                            <span>{$character.stamina}/{$character.max_stamina}</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill stamina-bar" style="width: {($character.stamina / $character.max_stamina * 100)|string_format:"%.0f"}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Pancerz:</span>
                            <span>{$character.armor}/{$character.max_armor}</span>
                        </div>
                        <div class="stat-bar">
                            <div class="stat-fill armor-bar" style="width: {($character.armor / $character.max_armor * 100)|string_format:"%.0f"}%"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <small>Obrażenia</small><br>
                            <strong>{$character.damage + $character.weapon_damage|default:0}</strong>
                        </div>
                        <div class="col-6">
                            <small>Zręczność</small><br>
                            <strong>{$character.dexterity}</strong>
                        </div>
                    </div>
                    
                    <div class="row text-center mt-2">
                        <div class="col-6">
                            <small>Zwinność</small><br>
                            <strong>{$character.agility}</strong>
                        </div>
                        <div class="col-6">
                            <small>Doświadczenie</small><br>
                            <strong>{$character.experience}</strong>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="d-flex justify-content-between">
                                <span>Energia:</span>
                                <span class="badge bg-primary">{$character.energy_points}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between">
                                <span>Wyzwania:</span>
                                <span class="badge bg-warning">{$character.challenge_points}</span>
                            </div>
                        </div>
                    </div>
                    
                    {if $character.weapon_name}
                    <hr>
                    <div class="text-center">
                        <small>Aktualna broń:</small><br>
                        <strong>{$character.weapon_name}</strong>
                        <br><small>(+{$character.weapon_damage} obrażeń)</small>
                    </div>
                    {/if}
                </div>
            </div>
            
            {if $traits}
            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-magic"></i> Traity</h6>
                </div>
                <div class="card-body">
                    {foreach $traits as $trait}
                        <div class="trait-badge" title="{$trait.description}">
                            <img src="{$trait.image_path}" alt="{$trait.name}" width="16" height="16">
                            {$trait.name}
                        </div>
                    {/foreach}
                </div>
            </div>
            {/if}
            
            {if $weapons && count($weapons) > 1}
            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-sword"></i> Bronie</h6>
                </div>
                <div class="card-body">
                    {foreach $weapons as $weapon}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                {$weapon.name}
                                {if $weapon.is_equipped}<i class="fas fa-check text-success"></i>{/if}
                            </span>
                            {if !$weapon.is_equipped}
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="equip_weapon">
                                    <input type="hidden" name="weapon_id" value="{$weapon.id}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Załóż</button>
                                </form>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            </div>
            {/if}
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-fist-raised"></i> Walka z losowymi przeciwnikami</h5>
                    <small>Koszt: 1 punkt energii</small>
                </div>
                <div class="card-body">
                    {if $character.energy_points > 0}
                        <div class="row">
                            {foreach $opponents as $opponent}
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="d-flex align-items-center">
                                            <img src="{$opponent.avatar_image}" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                                            <div class="flex-grow-1">
                                                <strong>{$opponent.name}</strong><br>
                                                <small>Poziom {$opponent.level}</small>
                                            </div>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="battle_random">
                                                <input type="hidden" name="opponent_id" value="{$opponent.id}">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="add_friend" value="1" id="friend_{$opponent.id}">
                                                    <label class="form-check-label" for="friend_{$opponent.id}">
                                                        <small>Dodaj do znajomych</small>
                                                    </label>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-danger">Walcz!</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        <div class="alert alert-warning">
                            <i class="fas fa-battery-empty"></i> Brak punktów energii! Następne punkty otrzymasz jutro.
                        </div>
                    {/if}
                </div>
            </div>
            
            {if $friends}
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Wyzwania znajomych</h5>
                    <small>Koszt: 1 punkt wyzwania</small>
                </div>
                <div class="card-body">
                    {if $character.challenge_points > 0}
                        <div class="row">
                            {foreach $friends as $friend}
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="d-flex align-items-center">
                                            <img src="{$friend.avatar_image}" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                                            <div class="flex-grow-1">
                                                <strong>{$friend.name}</strong><br>
                                                <small>Poziom {$friend.level}</small>
                                            </div>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="battle_friend">
                                                <input type="hidden" name="friend_id" value="{$friend.id}">
                                                <button type="submit" class="btn btn-sm btn-warning">Wyzwij!</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        <div class="alert alert-warning">
                            <i class="fas fa-hourglass-empty"></i> Brak punktów wyzwań! Następne punkty otrzymasz jutro.
                        </div>
                    {/if}
                </div>
            </div>
            {/if}
            
            {if $recent_battles}
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> Ostatnie walki</h5>
                </div>
                <div class="card-body">
                    <div style="max-height: 300px; overflow-y: auto;">
                        {foreach $recent_battles as $battle}
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    {if $battle.battle_role == 'attack'}
                                        <i class="fas fa-sword text-danger"></i> vs {$battle.defender_name}
                                    {else}
                                        <i class="fas fa-shield text-primary"></i> vs {$battle.attacker_name}
                                    {/if}
                                </div>
                                <div>
                                    {if $battle.winner_name == $character.name}
                                        <span class="badge bg-success">Zwycięstwo</span>
                                    {else}
                                        <span class="badge bg-danger">Porażka</span>
                                    {/if}
                                    <small class="text-muted">{$battle.created_at|date_format:"%d.%m %H:%M"}</small>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
            {/if}
        </div>
    </div>
</div>

{include file="footer.tpl"}