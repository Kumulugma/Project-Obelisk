{include file="header.tpl" page_title="Wyniki walki"}

<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header text-center">
                    <h3><i class="fas fa-fist-raised"></i> Wyniki Walki</h3>
                    <h5>{$battle.attacker_name} vs {$battle.defender_name}</h5>
                </div>
                
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>
                            {if $battle.winner_name}
                                <span class="badge bg-success fs-5">
                                    <i class="fas fa-crown"></i> Zwycięzca: {$battle.winner_name}
                                </span>
                            {else}
                                <span class="badge bg-warning fs-5">Remis</span>
                            {/if}
                        </h4>
                        
                        {if $battle.experience_gained > 0}
                            <p><i class="fas fa-star"></i> Doświadczenie: +{$battle.experience_gained}</p>
                        {/if}
                    </div>
                    
                    {if $weapon_reward || $trait_reward}
                    <div class="alert alert-success text-center">
                        <h5><i class="fas fa-gift"></i> Nagrody!</h5>
                        {if $weapon_reward}
                            <p><i class="fas fa-sword"></i> Znaleziono broń: <strong>{$weapon_reward.name}</strong> (+{$weapon_reward.damage} obrażeń)</p>
                        {/if}
                        {if $trait_reward}
                            <p><i class="fas fa-magic"></i> Zdobyto trait: <strong>{$trait_reward.name}</strong></p>
                            <small>{$trait_reward.description}</small>
                        {/if}
                    </div>
                    {/if}
                    
                    <div class="battle-log">
                        <h6><i class="fas fa-scroll"></i> Przebieg walki:</h6>
                        
                        {foreach $battle.battle_log as $log}
                            <div class="battle-round">
                                {if $log.type == 'passive_traits'}
                                    <div class="text-info">
                                        <strong>{$log.character}:</strong> {$log.message}
                                    </div>
                                {elseif $log.type == 'attack'}
                                    <div class="row">
                                        <div class="col-1 text-center">
                                            <small class="badge bg-secondary">{$log.round}</small>
                                        </div>
                                        <div class="col-11">
                                            <strong>{$log.attacker}</strong> → {$log.defender}: {$log.action}
                                            {if $log.damage > 0}
                                                <span class="text-danger">(-{$log.damage} HP)</span>
                                            {/if}
                                            <br>
                                            <small class="text-muted">
                                                {$log.defender}: {$log.defender_health} HP, {$log.defender_armor} Pancerz
                                            </small>
                                            
                                            {if $log.traits_activated}
                                                <div class="mt-1">
                                                    {foreach $log.traits_activated as $trait}
                                                        <span class="trait-activated" title="{$trait.description}">
                                                            <img src="{$trait.image}" width="16" height="16" alt="{$trait.name}">
                                                            {$trait.name}
                                                        </span>
                                                    {/foreach}
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                {elseif $log.type == 'effect'}
                                    <div class="text-warning">
                                        <i class="fas fa-fire"></i> {$log.character}: {$log.message}
                                    </div>
                                {elseif $log.type == 'exhaustion'}
                                    <div class="text-danger">
                                        <i class="fas fa-tired"></i> <strong>{$log.character}:</strong> {$log.message}
                                    </div>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="javascript:history.back()" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Powrót do profilu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}