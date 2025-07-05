{include file="header.tpl" page_title="Dashboard"}

<h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>

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
                <i class="fas fa-users fa-2x mb-2"></i>
                <h4>{$stats.total_characters}</h4>
                <small>Łączna liczba postaci</small>
            </div>
        </div>
    </div>
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
                <i class="fas fa-user-check fa-2x mb-2"></i>
                <h4>{$stats.active_today}</h4>
                <small>Aktywni dzisiaj</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-sword fa-2x mb-2"></i>
                <h4>{$stats.total_weapons}</h4>
                <small>Dostępne bronie</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Najlepsi gracze -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-trophy"></i> Najlepsi gracze</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Pozycja</th>
                                <th>Imię</th>
                                <th>Poziom</th>
                                <th>Doświadczenie</th>
                                <th>Ostatnia aktywność</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $top_players as $index => $player}
                                <tr>
                                    <td>
                                        {if $index == 0}<i class="fas fa-crown text-warning"></i>{else}{$index + 1}{/if}
                                    </td>
                                    <td>{$player.name}</td>
                                    <td><span class="badge bg-primary">{$player.level}</span></td>
                                    <td>{$player.experience}</td>
                                    <td>
                                        <small class="text-muted">
                                            {$player.last_login|date_format:"%d.%m.%Y %H:%M"}
                                        </small>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ostatnie walki -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Ostatnie walki</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Atakujący</th>
                                <th>Obrońca</th>
                                <th>Zwycięzca</th>
                                <th>Czas</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $recent_battles as $battle}
                                <tr>
                                    <td>{$battle.attacker_name}</td>
                                    <td>{$battle.defender_name}</td>
                                    <td>
                                        {if $battle.winner_name}
                                            <span class="badge bg-success">{$battle.winner_name}</span>
                                        {else}
                                            <span class="badge bg-secondary">Remis</span>
                                        {/if}
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {$battle.created_at|date_format:"%H:%M"}
                                        </small>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}