{include file="header.tpl" page_title="Zarządzanie Traitami"}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-magic"></i> Zarządzanie Traitami</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTraitModal">
        <i class="fas fa-plus"></i> Dodaj trait
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
                        <th>Typ</th>
                        <th>Efekt</th>
                        <th>Wartość</th>
                        <th>Szansa</th>
                        <th>Drop</th>
                        <th>Użytkownicy</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $traits as $trait}
                        <tr>
                            <td>{$trait.id}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    {if $trait.image_path}
                                        <img src="{$trait.image_path}" width="24" height="24" alt="{$trait.name}" class="me-2">
                                    {/if}
                                    <strong>{$trait.name}</strong>
                                </div>
                                <small class="text-muted">{$trait.description|truncate:50}</small>
                            </td>
                            <td>
                                {if $trait.type == 'passive'}
                                    <span class="badge bg-success">Pasywny</span>
                                {elseif $trait.type == 'active'}
                                    <span class="badge bg-warning">Aktywny</span>
                                {else}
                                    <span class="badge bg-info">Modyfikujący</span>
                                {/if}
                            </td>
                            <td>
                                {if $trait.effect_type}
                                    <span class="badge bg-secondary">{$trait.effect_type}</span>
                                    {if $trait.effect_target}
                                        <br><small class="text-muted">Cel: {$trait.effect_target}</small>
                                    {/if}
                                {/if}
                            </td>
                            <td>
                                {if $trait.effect_value}
                                    <strong>{$trait.effect_value}</strong>
                                    {if $trait.effect_duration > 0}
                                        <br><small class="text-muted">Czas: {$trait.effect_duration}</small>
                                    {/if}
                                {/if}
                            </td>
                            <td>
                                {if $trait.trigger_chance}
                                    {($trait.trigger_chance * 100)|string_format:"%.1f"}%
                                {else}
                                    -
                                {/if}
                            </td>
                            <td>{($trait.drop_chance * 100)|string_format:"%.2f"}%</td>
                            <td><span class="badge bg-info">{$trait.users_count}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTraitModal{$trait.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                {if $trait.users_count == 0}
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="trait_id" value="{$trait.id}">
                                        <button type="submit" name="delete_trait" class="btn btn-sm btn-outline-danger" 
                                                data-confirm="Czy na pewno chcesz usunąć trait {$trait.name}?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                {else}
                                    <button class="btn btn-sm btn-outline-danger" disabled title="Trait jest używany przez graczy">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                {/if}
                            </td>
                        </tr>
                        
                        <!-- Modal edycji traita -->
                        <div class="modal fade" id="editTraitModal{$trait.id}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edytuj trait: {$trait.name}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="trait_id" value="{$trait.id}">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nazwa traita</label>
                                                        <input type="text" class="form-control" name="name" value="{$trait.name}" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Opis</label>
                                                        <textarea class="form-control" name="description" rows="3">{$trait.description}</textarea>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Typ traita</label>
                                                        <select class="form-select" name="type" required>
                                                            <option value="passive" {if $trait.type == 'passive'}selected{/if}>Pasywny</option>
                                                            <option value="active" {if $trait.type == 'active'}selected{/if}>Aktywny</option>
                                                            <option value="modifier" {if $trait.type == 'modifier'}selected{/if}>Modyfikujący</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Typ efektu</label>
                                                        <select class="form-select" name="effect_type">
                                                            <option value="">Brak efektu</option>
                                                            <option value="damage_boost" {if $trait.effect_type == 'damage_boost'}selected{/if}>Zwiększenie obrażeń</option>
                                                            <option value="agility_boost" {if $trait.effect_type == 'agility_boost'}selected{/if}>Zwiększenie zwinności</option>
                                                            <option value="armor_boost" {if $trait.effect_type == 'armor_boost'}selected{/if}>Zwiększenie pancerza</option>
                                                            <option value="dexterity_boost" {if $trait.effect_type == 'dexterity_boost'}selected{/if}>Zwiększenie zręczności</option>
                                                            <option value="stamina_boost" {if $trait.effect_type == 'stamina_boost'}selected{/if}>Zwiększenie wytrzymałości</option>
                                                            <option value="penetration_boost" {if $trait.effect_type == 'penetration_boost'}selected{/if}>Zwiększenie przebicia</option>
                                                            <option value="burn" {if $trait.effect_type == 'burn'}selected{/if}>Podpalenie</option>
                                                            <option value="poison" {if $trait.effect_type == 'poison'}selected{/if}>Zatrucie</option>
                                                            <option value="heal" {if $trait.effect_type == 'heal'}selected{/if}>Leczenie</option>
                                                            <option value="critical" {if $trait.effect_type == 'critical'}selected{/if}>Krytyczne trafienie</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Cel efektu</label>
                                                        <select class="form-select" name="effect_target">
                                                            <option value="">Brak celu</option>
                                                            <option value="self" {if $trait.effect_target == 'self'}selected{/if}>Siebie</option>
                                                            <option value="enemy" {if $trait.effect_target == 'enemy'}selected{/if}>Przeciwnik</option>
                                                            <option value="both" {if $trait.effect_target == 'both'}selected{/if}>Obu</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Wartość efektu</label>
                                                                <input type="number" class="form-control" name="effect_value" value="{$trait.effect_value}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Czas trwania (rundy)</label>
                                                                <input type="number" class="form-control" name="effect_duration" value="{$trait.effect_duration}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Szansa aktywacji (0.0 - 1.0)</label>
                                                        <input type="number" class="form-control" name="trigger_chance" value="{$trait.trigger_chance}" step="0.01" min="0" max="1">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Szansa dropu (0.0 - 1.0)</label>
                                                        <input type="number" class="form-control" name="drop_chance" value="{$trait.drop_chance}" step="0.01" min="0" max="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Ścieżka do obrazka</label>
                                                        <input type="text" class="form-control" name="image_path" value="{$trait.image_path}" placeholder="/images/traits/trait.png">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Modyfikator avatara</label>
                                                <input type="text" class="form-control" name="avatar_modifier" value="{$trait.avatar_modifier}" placeholder="np. glow, shadow, flame">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                            <button type="submit" name="edit_trait" class="btn btn-primary">Zapisz zmiany</button>
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

<!-- Modal dodawania traita -->
<div class="modal fade" id="addTraitModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Dodaj nowy trait</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nazwa traita</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Opis</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Typ traita</label>
                                <select class="form-select" name="type" required>
                                    <option value="passive">Pasywny</option>
                                    <option value="active">Aktywny</option>
                                    <option value="modifier">Modyfikujący</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Typ efektu</label>
                                <select class="form-select" name="effect_type">
                                    <option value="">Brak efektu</option>
                                    <option value="damage_boost">Zwiększenie obrażeń</option>
                                    <option value="agility_boost">Zwiększenie zwinności</option>
                                    <option value="armor_boost">Zwiększenie pancerza</option>
                                    <option value="dexterity_boost">Zwiększenie zręczności</option>
                                    <option value="stamina_boost">Zwiększenie wytrzymałości</option>
                                    <option value="penetration_boost">Zwiększenie przebicia</option>
                                    <option value="burn">Podpalenie</option>
                                    <option value="poison">Zatrucie</option>
                                    <option value="heal">Leczenie</option>
                                    <option value="critical">Krytyczne trafienie</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Cel efektu</label>
                                <select class="form-select" name="effect_target">
                                    <option value="">Brak celu</option>
                                    <option value="self">Siebie</option>
                                    <option value="enemy">Przeciwnik</option>
                                    <option value="both">Obu</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Wartość efektu</label>
                                        <input type="number" class="form-control" name="effect_value" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Czas trwania (rundy)</label>
                                        <input type="number" class="form-control" name="effect_duration" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Szansa aktywacji (0.0 - 1.0)</label>
                                <input type="number" class="form-control" name="trigger_chance" value="1.0" step="0.01" min="0" max="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Szansa dropu (0.0 - 1.0)</label>
                                <input type="number" class="form-control" name="drop_chance" value="0.1" step="0.01" min="0" max="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Ścieżka do obrazka</label>
                                <input type="text" class="form-control" name="image_path" placeholder="/images/traits/trait.png">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Modyfikator avatara</label>
                        <input type="text" class="form-control" name="avatar_modifier" placeholder="np. glow, shadow, flame">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" name="add_trait" class="btn btn-primary">Dodaj trait</button>
                </div>
            </form>
        </div>
    </div>
</div>

{include file="footer.tpl"}