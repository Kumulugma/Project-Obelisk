{include file="header.tpl" page_title="Zarządzanie Avatarami"}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-user-circle"></i> Zarządzanie Avatarami</h1>
    <div>
        <span class="badge bg-primary">Wszystkich: {$avatar_stats.total}</span>
        <span class="badge bg-success">Aktywnych: {$avatar_stats.active}</span>
        <span class="badge bg-info">Męskich: {$avatar_stats.male}</span>
        <span class="badge bg-danger">Żeńskich: {$avatar_stats.female}</span>
        <span class="badge bg-warning">Unisex: {$avatar_stats.unisex}</span>
    </div>
</div>

{if $message}
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
{/if}

{if $error}
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> {$error}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
{/if}

<!-- Dodawanie pojedynczego avatara -->
<div class="card mb-4">
    <div class="card-header">
        <h4><i class="fas fa-plus"></i> Dodaj Nowy Avatar</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label">Ścieżka do obrazka</label>
                    <input type="text" class="form-control" name="image_path" placeholder="/images/avatars/avatar.png" required>
                    <div class="form-text">Pełna ścieżka do pliku obrazka</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Płeć</label>
                    <select class="form-select" name="gender" required>
                        <option value="male">Męski</option>
                        <option value="female">Żeński</option>
                        <option value="unisex">Uniwersalny</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                        <label class="form-check-label">Aktywny</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" name="add_avatar" class="btn btn-primary d-block">
                        <i class="fas fa-plus"></i> Dodaj
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Masowe dodawanie avatarów -->
<div class="card mb-4">
    <div class="card-header">
        <h4><i class="fas fa-upload"></i> Masowe Dodawanie Avatarów</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label">Lista avatarów (jeden na linię)</label>
                    <textarea class="form-control" name="avatars_data" rows="5" 
                              placeholder="/images/avatars/male/warrior1.png|male&#10;/images/avatars/female/mage1.png|female&#10;/images/avatars/unisex/default.png"></textarea>
                    <div class="form-text">Format: ścieżka|płeć (jeśli nie podasz płci, użyta zostanie domyślna)</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Domyślna płeć</label>
                    <select class="form-select" name="default_gender">
                        <option value="male">Męski</option>
                        <option value="female">Żeński</option>
                        <option value="unisex">Uniwersalny</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" name="bulk_add_avatars" class="btn btn-success d-block">
                        <i class="fas fa-upload"></i> Dodaj Wszystkie
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista avatarów -->
<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-list"></i> Lista Avatarów ({$avatar_stats.total})</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Podgląd</th>
                        <th>Ścieżka</th>
                        <th>Płeć</th>
                        <th>Status</th>
                        <th>Użycia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $avatars as $avatar}
                    <tr>
                        <td>
                            <img src="{$avatar.image_path}" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" 
                                 onerror="this.src='/images/avatars/default.png'">
                        </td>
                        <td><code>{$avatar.image_path}</code></td>
                        <td>
                            {if $avatar.gender == 'male'}
                                <span class="badge bg-info"><i class="fas fa-mars"></i> Męski</span>
                            {elseif $avatar.gender == 'female'}
                                <span class="badge bg-danger"><i class="fas fa-venus"></i> Żeński</span>
                            {else}
                                <span class="badge bg-warning"><i class="fas fa-genderless"></i> Unisex</span>
                            {/if}
                        </td>
                        <td>
                            {if $avatar.is_active}
                                <span class="badge bg-success"><i class="fas fa-check"></i> Aktywny</span>
                            {else}
                                <span class="badge bg-secondary"><i class="fas fa-times"></i> Nieaktywny</span>
                            {/if}
                        </td>
                        <td>
                            <span class="badge bg-primary">{$avatar.usage_count}</span>
                            {if $avatar.usage_count > 0}
                                <i class="fas fa-users text-muted" title="Używany przez postacie"></i>
                            {/if}
                        </td>
                        <td>
                            <!-- Toggle Status -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="avatar_id" value="{$avatar.id}">
                                {if $avatar.is_active}
                                    <button type="submit" name="toggle_avatar" class="btn btn-sm btn-outline-warning" title="Dezaktywuj">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                {else}
                                    <input type="hidden" name="is_active" value="1">
                                    <button type="submit" name="toggle_avatar" class="btn btn-sm btn-outline-success" title="Aktywuj">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                {/if}
                            </form>
                            
                            <!-- Edit Button -->
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{$avatar.id}" title="Edytuj">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Delete Button -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="avatar_id" value="{$avatar.id}">
                                <button type="submit" name="delete_avatar" class="btn btn-sm btn-outline-danger" 
                                        {if $avatar.usage_count > 0}disabled title="Nie można usunąć - avatar jest używany"{else}
                                        onclick="return confirm('Czy na pewno usunąć ten avatar?')" title="Usuń"{/if}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    
                    <!-- Modal edycji -->
                    <div class="modal fade" id="editModal{$avatar.id}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edytuj Avatar</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="avatar_id" value="{$avatar.id}">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Podgląd</label>
                                            <div>
                                                <img src="{$avatar.image_path}" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;" 
                                                     onerror="this.src='/images/avatars/default.png'">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Ścieżka do obrazka</label>
                                            <input type="text" class="form-control" name="image_path" value="{$avatar.image_path}" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Płeć</label>
                                            <select class="form-select" name="gender" required>
                                                <option value="male" {if $avatar.gender == 'male'}selected{/if}>Męski</option>
                                                <option value="female" {if $avatar.gender == 'female'}selected{/if}>Żeński</option>
                                                <option value="unisex" {if $avatar.gender == 'unisex'}selected{/if}>Uniwersalny</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_active" {if $avatar.is_active}checked{/if}>
                                                <label class="form-check-label">Aktywny</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                        <button type="submit" name="edit_avatar" class="btn btn-primary">Zapisz</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    {foreachelse}
                    <tr>
                        <td colspan="6" class="text-center text-muted">Brak avatarów w systemie</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

{include file="footer.tpl"}