{include file="header.tpl" page_title="Historia Walk"}

<h1><i class="fas fa-fist-raised"></i> Historia Walk</h1>

{if $message}
    <div class="alert alert-success alert-dismissible fade show">
        {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
{/if}

{if $error}
    <div class="alert alert-danger alert-dismissible fade show">
        {$error}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
{/if}

<!-- Statystyki -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-fist-raised fa-2x mb-2"></i>
                <h4>{$stats.total_battles}</h4>
                <small>Łączna liczba walk</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-calendar-day fa-2x mb-2"></i>
                <h4>{$stats.battles_today}</h4>
                <small>Walki dzisiaj</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-random fa-2x mb-2"></i>
                <h4>{$stats.random_battles}</h4>
                <small>Walki losowe</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-user-friends fa-2x mb-2"></i>
                <h4>{$stats.challenge_battles}</h4>
                <small>Wyzwania</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtry -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Postać:</label>
                <input type="text" class="form-control" name="character" value="{$character_filter}" placeholder="Imię postaci">
            </div>
            <div class="col-md-2">
                <label class="form-label">Typ walki:</label>
                <select class="form-select" name="type">
                    <option value="">Wszystkie</option>
                    <option value="random" {if $type_filter == 'random'}selected{/if}>Losowe</option>
                    <option value="challenge" {if $type_filter == 'challenge'}selected{/if}>Wyzwania</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Data od:</label>
                <input type="date" class="form-control" name="date_from" value="{$date_from}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Data do:</label>
                <input type="date" class="form-control" name="date_to" value="{$date_to}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtruj
                    </button>
                    <a href="battles.php" class="btn btn-outline-secondary">Wyczyść</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista walk -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Atakujący</th>
                        <th>Obrońca</th>
                        <th>Zwycięzca</th>
                        <th>Typ</th>
                        <th>Doświadczenie</th>
                        <th>Nagrody</th>
                        <th>Data walki</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $battles as $battle}
                        <tr>
                            <td>{$battle.id}</td>
                            <td>
                                <strong>{$battle.attacker_name}</strong>
                                <br><small class="text-muted">Lvl {$battle.attacker_level}</small>
                            </td>
                            <td>
                                <strong>{$battle.defender_name}</strong>
                                <br><small class="text-muted">Lvl {$battle.defender_level}</small>
                            </td>
                            <td>
                                {if $battle.winner_name}
                                    <span class="badge bg-success">{$battle.winner_name}</span>
                                {else}
                                    <span class="badge bg-secondary">Remis</span>
                                {/if}
                            </td>
                            <td>
                                {if $battle.battle_type == 'random'}
                                    <span class="badge bg-primary">Losowa</span>
                                {else}
                                    <span class="badge bg-warning">Wyzwanie</span>
                                {/if}
                            </td>
                            <td><span class="badge bg-info">{$battle.experience_gained} XP</span></td>
                            <td>
                                {if $battle.weapon_dropped_name}
                                    <i class="fas fa-sword text-warning" title="Broń: {$battle.weapon_dropped_name}"></i>
                                {/if}
                                {if $battle.trait_dropped_name}
                                    <i class="fas fa-magic text-success" title="Trait: {$battle.trait_dropped_name}"></i>
                                {/if}
                                {if !$battle.weapon_dropped_name && !$battle.trait_dropped_name}
                                    <span class="text-muted">-</span>
                                {/if}
                            </td>
                            <td>
                                <small class="text-muted">
                                    {$battle.created_at|date_format:"%d.%m.%Y %H:%M"}
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#battleLogModal{$battle.id}">
                                    <i class="fas fa-eye"></i> Log
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal z logiem walki -->
                        <div class="modal fade" id="battleLogModal{$battle.id}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Log walki #{$battle.id}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h6>Atakujący:</h6>
                                                <p><strong>{$battle.attacker_name}</strong> (Poziom {$battle.attacker_level})</p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Obrońca:</h6>
                                                <p><strong>{$battle.defender_name}</strong> (Poziom {$battle.defender_level})</p>
                                            </div>
                                        </div>
                                        
                                        <h6>Przebieg walki:</h6>
                                        <div class="battle-log" style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                            <pre style="white-space: pre-wrap; font-family: inherit;">{$battle.battle_log|escape}</pre>
                                        </div>
                                        
                                        {if $battle.weapon_dropped_name || $battle.trait_dropped_name}
                                            <div class="mt-3">
                                                <h6>Nagrody:</h6>
                                                {if $battle.weapon_dropped_name}
                                                    <span class="badge bg-warning me-2">
                                                        <i class="fas fa-sword"></i> {$battle.weapon_dropped_name}
                                                    </span>
                                                {/if}
                                                {if $battle.trait_dropped_name}
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-magic"></i> {$battle.trait_dropped_name}
                                                    </span>
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </tbody>
            </table>
        </div>
        
        <!-- Paginacja -->
        {if $total_pages > 1}
            <nav>
                <ul class="pagination justify-content-center">
                    {if $current_page > 1}
                        <li class="page-item">
                            <a class="page-link" href="?page={$current_page-1}&search={$search}&sort={$sort_by}&order={$sort_order}">Poprzednia</a>
                        </li>
                    {/if}
                    
                    {for $i=1 to $total_pages}
                        {if $i == $current_page}
                            <li class="page-item active">
                                <span class="page-link">{$i}</span>
                            </li>
                        {elseif $i <= 3 || $i > $total_pages-3 || abs($i - $current_page) <= 2}
                            <li class="page-item">
                                <a class="page-link" href="?page={$i}&search={$search}&sort={$sort_by}&order={$sort_order}">{$i}</a>
                            </li>
                        {elseif $i == 4 || $i == $total_pages-3}
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        {/if}
                    {/for}
                    
                    {if $current_page < $total_pages}
                        <li class="page-item">
                            <a class="page-link" href="?page={$current_page+1}&search={$search}&sort={$sort_by}&order={$sort_order}">Następna</a>
                        </li>
                    {/if}
                </ul>
            </nav>
        {/if}
    </div>
</div>

{include file="footer.tpl"}