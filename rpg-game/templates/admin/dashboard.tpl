<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .stat-card {
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h5><i class="fas fa-shield-alt"></i> Admin Panel</h5>
                        <small>Witaj, {$admin_username}</small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="characters.php">
                                <i class="fas fa-users"></i> Postaci
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="weapons.php">
                                <i class="fas fa-sword"></i> Bronie
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="traits.php">
                                <i class="fas fa-magic"></i> Traity
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="battles.php">
                                <i class="fas fa-fist-raised"></i> Walki
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Ustawienia
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="../" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Strona główna
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?logout=1">
                                <i class="fas fa-sign-out-alt"></i> Wyloguj
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Postaci</h5>
                                        <h3 class="text-primary">{$stats.total_characters}</h3>
                                    </div>
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #28a745;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Walki</h5>
                                        <h3 class="text-success">{$stats.total_battles}</h3>
                                    </div>
                                    <i class="fas fa-fist-raised fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Aktywni dziś</h5>
                                        <h3 class="text-warning">{$stats.active_today}</h3>
                                    </div>
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #dc3545;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Bronie</h5>
                                        <h3 class="text-danger">{$stats.total_weapons}</h3>
                                    </div>
                                    <i class="fas fa-sword fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-trophy"></i> Top Gracze</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Imię</th>
                                                <th>Poziom</th>
                                                <th>Doświadczenie</th>
                                                <th>Ostatnia aktywność</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach $top_players as $player}
                                            <tr>
                                                <td>{$player.name}</td>
                                                <td><span class="badge bg-primary">{$player.level}</span></td>
                                                <td>{$player.experience}</td>
                                                <td><small>{$player.last_login|date_format:"%d.%m %H:%M"}</small></td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Ostatnie Walki</h5>
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
                                                        <small class="text-success">{$battle.winner_name}</small>
                                                    {else}
                                                        <small class="text-muted">Remis</small>
                                                    {/if}
                                                </td>
                                                <td><small>{$battle.created_at|date_format:"%d.%m %H:%M"}</small></td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>