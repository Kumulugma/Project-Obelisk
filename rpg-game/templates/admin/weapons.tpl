{include file="header.tpl" page_title="Zarządzanie Bronią"}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-sword"></i> Zarządzanie Bronią</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWeaponModal">
        <i class="fas fa-plus"></i> Dodaj broń
    </button>
</div>

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

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa</th>
                        <th>Obrażenia</th>
                        <th>Przebicie pancerza</th>
                        <th>Szansa dropu</th>
                        <th>Obrazek</th>
                        <th>Użytkownicy</th>
                        <th>W ekwipunkach</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $weapons as $weapon}
                        <tr>
                            <td>{$weapon.id}</td>
                            <td><strong>{$weapon.name}</strong></td>
                            <td><span class="badge bg-danger">{$weapon.damage}</span></td>
                            <td><span class="badge bg-warning">{$weapon.armor_penetration}</span></td>
                            <td>{($weapon.drop_chance * 100)|string_format:"%.2f"}%</td>
                            <td>
                                {if $weapon.image_path}
                                    <img src="{$weapon.image_path}" width="32" height="32" alt="{$weapon.name}">
                                {else}
                                    <i class="fas fa-image text-muted"></i>
                                {/if}
                            </td>
                            <td><span class="badge bg-info">{$weapon.users_count}</span></td>
                            <td><span class="badge bg-secondary">{$weapon.inventory_count}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editWeaponModal{$weapon.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                {if $weapon.users_count == 0}
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="weapon_id" value="{$weapon.id}">
                                        <button type="submit" name="delete_weapon" class="btn btn-sm btn-outline-danger" 
                                                data-confirm="Czy na pewno chcesz usunąć broń {$weapon.name}?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                {else}
                                    <button class="btn btn-sm btn-outline-danger" disabled title="Broń jest używana przez graczy">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                {/if}
                            </td>
                        </tr>
                        
                        <!-- Modal edycji broni -->
                        <div class="modal fade" id="editWeaponModal{$weapon.id}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edytuj broń: {$weapon.name}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="weapon_id" value="{$weapon.id}">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Nazwa broni</label>
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
                                                        <label class="form-label">Przebicie pancerza</label>
                                                        <input type="number" class="form-control" name="armor_penetration" value="{$weapon.armor_penetration}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Szansa dropu (0.0 - 1.0)</label>
                                                <input type="number" class="form-control" name="drop_chance" value="{$weapon.drop_chance}" step="0.001" min="0" max="1" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Ścieżka do obrazka</label>
                                                <input type="text" class="form-control" name="image_path" value="{$weapon.image_path}" placeholder="/images/weapons/weapon.png">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                            <button type="submit" name="edit_weapon" class="btn btn-primary">Zapisz zmiany</button>
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

<!-- Modal dodawania broni -->
<div class="modal fade" id="addWeaponModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Dodaj nową broń</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nazwa broni</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Obrażenia</label>
                                <input type="number" class="form-control" name="damage" value="10" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Przebicie pancerza</label>
                                <input type="number" class="form-control" name="armor_penetration" value="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Szansa dropu (0.0 - 1.0)</label>
                        <input type="number" class="form-control" name="drop_chance" value="0.05" step="0.001" min="0" max="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ścieżka do obrazka</label>
                        <input type="text" class="form-control" name="image_path" placeholder="/images/weapons/weapon.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" name="add_weapon" class="btn btn-primary">Dodaj broń</button>
                </div>
            </form>
        </div>
    </div>
</div>

{include file="footer.tpl"}