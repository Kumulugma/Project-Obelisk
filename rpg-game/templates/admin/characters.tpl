<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Postaci</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
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
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="characters.php">
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
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Zarządzanie Postaciami</h1>
                </div>
                
                {if $message}
                    <div class="alert alert-{if $message_type == 'error'}danger{else}success{/if}">
                        {$message}
                    </div>
                {/if}
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-search"></i> Filtrowanie i Wyszukiwanie</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Szukaj (imię/PIN)</label>
                                <input type="text" class="form-control" id="search" name="search" value="{$smarty.get.search}">
                            </div>
                            <div class="col-md-2">
                                <label for="min_level" class="form-label">Min. poziom</label>
                                <input type="number" class="form-control" id="min_level" name="min_level" value="{$smarty.get.min_level}">
                            </div>
                            <div class="col-md-2">
                                <label for="sort" class="form-label">Sortuj według</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="id" {if $smarty.get.sort == 'id'}selected{/if}>ID</option>
                                    <option value="name" {if $smarty.get.sort == 'name'}selected{/if}>Imię</option>
                                    <option value="level" {if $smarty.get.sort == 'level'}selected{/if}>Poziom</option>
                                    <option value="experience" {if $smarty.get.sort == 'experience'}selected{/if}>Doświadczenie</option>
                                    <option value="last_login" {if $smarty.get.sort == 'last_login'}selected{/if}>Ostatnia aktywność</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="order" class="form-label">Kierunek</label>
                                <select class="form-select" id="order" name="order">
                                    <option value="desc" {if $smarty.get.order == 'desc'}selected{/if}>Malejąco</option>
                                    <option value="asc" {if $smarty.get.order == 'asc'}selected{/if}>Rosnąco</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Filtruj</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="fas fa-users"></i> Lista Postaci ({$total_characters})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imię</th>
                                        <th>PIN</th>
                                        <th>Poziom</th>
                                        <th>Doświadczenie</th>
                                        <th>Zdrowie</th>
                                        <th>Ostatnia aktywność</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $characters as $char}
                                    <tr>
                                        <td>{$char.id}</td>
                                        <td>
                                            <img src="{$char.avatar_image}" alt="Avatar" width="30" height="30" class="rounded-circle me-2">
                                            {$char.name}
                                        </td>
                                        <td><code>{$char.pin}</code></td>
                                        <td><span class="badge bg-primary">{$char.level}</span></td>
                                        <td>{$char.experience}</td>
                                        <td>{$char.health}/{$char.max_health}</td>
                                        <td><small>{$char.last_login|date_format:"%d.%m.%Y %H:%M"}</small></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{$char.id}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="delete_character">
                                                <input type="hidden" name="character_id" value="{$char.id}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Czy na pewno chcesz usunąć tę postać?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <div class="modal fade" id="editModal{$char.id}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edytuj postać: {$char.name}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_character">
                                                        <input type="hidden" name="character_id" value="{$char.id}">
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Poziom</label>
                                                                    <input type="number" class="form-control" name="level" value="{$char.level}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Doświadczenie</label>
                                                                    <input type="number" class="form-control" name="experience" value="{$char.experience}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Zdrowie</label>
                                                                    <input type="number" class="form-control" name="health" value="{$char.health}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Max. Zdrowie</label>
                                                                    <input type="number" class="form-control" name="max_health" value="{$char.max_health}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Wytrzymałość</label>
                                                                    <input type="number" class="form-control" name="stamina" value="{$char.stamina}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Max. Wytrzymałość</label>
                                                                    <input type="number" class="form-control" name="max_stamina" value="{$char.max_stamina}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Obrażenia</label>
                                                                    <input type="number" class="form-control" name="damage" value="{$char.damage}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Zręczność</label>
                                                                    <input type="number" class="form-control" name="dexterity" value="{$char.dexterity}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Zwinność</label>
                                                                    <input type="number" class="form-control" name="agility" value="{$char.agility}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Pancerz</label>
                                                                    <input type="number" class="form-control" name="armor" value="{$char.armor}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                                        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                        
                        {if $total_pages > 1}
                        <nav>
                            <ul class="pagination justify-content-center">
                                {for $i=1 to $total_pages}
                                    <li class="page-item {if $i == $current_page}active{/if}">
                                        <a class="page-link" href="?page={$i}&{$smarty.server.QUERY_STRING|replace:'page='|cat:$current_page:''}">{$i}</a>
                                    </li>
                                {/for}
                            </ul>
                        </nav>
                        {/if}
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>