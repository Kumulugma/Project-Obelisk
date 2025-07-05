<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Bronie</title>
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
                            <a class="nav-link" href="characters.php">
                                <i class="fas fa-users"></i> Postaci
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="weapons.php">
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
                    <h1 class="h2">Zarządzanie Broniami</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWeaponModal">
                        <i class="fas fa-plus"></i> Dodaj Broń
                    </button>
                </div>
                
                {if $message}
                    <div class="alert alert-{if $message_type == 'error'}danger{else}success{/if}">
                        {$message}
                    </div>
                {/if}
                
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-sword"></i> Lista Broni</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nazwa</th>
                                        <th>Obrażenia</th>
                                        <th>Przebicie Pancerza</th>
                                        <th>Szansa Dropu</th>
                                        <th>Ścieżka Obrazu</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $weapons as $weapon}
                                    <tr>
                                        <td>{$weapon.id}</td>
                                        <td>
                                            {if $weapon.image_path}
                                                <img src="{$weapon.image_path}" alt="{$weapon.name}" width="30" height="30" class="me-2">
                                            {/if}
                                            {$weapon.name}
                                        </td>
                                        <td><span class="badge bg-danger">{$weapon.damage}</span></td>
                                        <td><span class="badge bg-warning">{$weapon.armor_penetration}</span></td>
                                        <td>{($weapon.drop_chance * 100)|string_format:"%.3f"}%</td>
                                        <td><small>{$weapon.image_path}</small></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editWeaponModal{$weapon.id}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {if $weapon.id > 1}
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="delete_weapon">
                                                <input type="hidden" name="weapon_id" value="{$weapon.id}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Czy na pewno chcesz usunąć tę broń?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            {/if}
                                        </td>
                                    </tr>
                                    
                                    <div class="modal fade" id="editWeaponModal{$weapon.id}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edytuj broń: {$weapon.name}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_weapon">
                                                        <input type="hidden" name="weapon_id" value="{$weapon.id}">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Nazwa</label>
                                                            <input type="text" class="form-control" name="name" value="{$weapon.name}" required>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Obrażenia</label>
                                                                    <input type="number" class="form-control" name="damage" value="{$weapon.damage}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Przebicie Pancerza</label>
                                                                    <input type="number" class="form-control" name="armor_penetration" value="{$weapon.armor_penetration}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Szansa Dropu (0.00001 - 1.0)</label>
                                                            <input type="number" class="form-control" name="drop_chance" value="{$weapon.drop_chance}" step="0.00001" min="0" max="1">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Ścieżka Obrazu</label>
                                                            <input type="text" class="form-control" name="image_path" value="{$weapon.image_path}">
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
                    </div>
                </div>
                
                <div class="modal fade" id="addWeaponModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Dodaj nową broń</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="add_weapon">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nazwa</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Obrażenia</label>
                                                <input type="number" class="form-control" name="damage" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Przebicie Pancerza</label>
                                                <input type="number" class="form-control" name="armor_penetration" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Szansa Dropu (0.00001 - 1.0)</label>
                                        <input type="number" class="form-control" name="drop_chance" value="0.01" step="0.00001" min="0" max="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ścieżka Obrazu</label>
                                        <input type="text" class="form-control" name="image_path" placeholder="/images/weapons/weapon_name.png">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                    <button type="submit" class="btn btn-primary">Dodaj broń</button>
                                </div>
                            </form>
                        </div>
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