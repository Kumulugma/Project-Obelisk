{include file="header.tpl" page_title="Profil - {$character.name}"}

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> {$character.name} (Poziom {$character.level})</h3>
                </div>
                <div class="card-body">
                    {if $message}
                        <div class="alert alert-{if $message_type == 'error'}danger{else}success{/if}">
                            {$message}
                        </div>
                    {/if}
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>PIN:</strong> {$character.pin}</p>
                            <p><strong>Zdrowie:</strong> {$character.health}/{$character.max_health}</p>
                            <p><strong>Wytrzymałość:</strong> {$character.stamina}/{$character.max_stamina}</p>
                            <p><strong>Obrażenia:</strong> {$character.damage}</p>
                            <p><strong>Zręczność:</strong> {$character.dexterity}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Zwinność:</strong> {$character.agility}</p>
                            <p><strong>Pancerz:</strong> {$character.armor}/{$character.max_armor}</p>
                            <p><strong>Doświadczenie:</strong> {$character.experience}</p>
                            <p><strong>Punkty energii:</strong> {$character.energy_points}</p>
                            <p><strong>Punkty wyzwań:</strong> {$character.challenge_points}</p>
                        </div>
                    </div>
                    
                    {if $character.weapon_name}
                        <p><strong>Broń:</strong> {$character.weapon_name} ({$character.weapon_damage} obrażeń)</p>
                    {/if}
                </div>
            </div>
            
            <!-- Losowi przeciwnicy -->
            {if $opponents}
                <div class="card mt-4">
                    <div class="card-header">
                        <h4><i class="fas fa-sword"></i> Losowi Przeciwnicy</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {foreach $opponents as $opponent}
                                <div class="col-md-6 mb-3">
                                    <div class="border p-3 rounded">
                                        <h6>{$opponent.name} (Lv. {$opponent.level})</h6>
                                        <p class="small mb-2">
                                            ❤️ {$opponent.health} | ⚔️ {$opponent.damage} | 🛡️ {$opponent.armor}
                                        </p>
                                        {if $character.energy_points > 0}
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="battle_random">
                                                <input type="hidden" name="opponent_id" value="{$opponent.id}">
                                                <button type="submit" class="btn btn-sm btn-danger">Atakuj!</button>
                                            </form>
                                        {else}
                                            <button class="btn btn-sm btn-secondary" disabled>Brak energii</button>
                                        {/if}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/if}
            
        </div>
        
        <div class="col-md-4">
            <!-- Historia walk -->
            {if $recent_battles}
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Ostatnie walki</h5>
                    </div>
                    <div class="card-body">
                        {foreach $recent_battles as $battle}
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="small">
                                        {if $battle.attacker_id == $character.id}
                                            vs {$battle.defender_name}
                                        {else}
                                            vs {$battle.attacker_name}
                                        {/if}
                                    </span>
                                    <span class="small text-muted">
                                        {$battle.created_at|date_format:"%d.%m %H:%M"}
                                    </span>
                                </div>
                                <div>
                                    {if $battle.winner_id == $character.id}
                                        <span class="badge bg-success">Zwycięstwo</span>
                                    {else}
                                        <span class="badge bg-danger">Porażka</span>
                                    {/if}
                                    
                                    {if $battle.experience_gained > 0}
                                        <span class="badge bg-info">+{$battle.experience_gained} exp</span>
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>

{include file="footer.tpl"}