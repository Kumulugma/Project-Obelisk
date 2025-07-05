{include file="header.tpl" page_title="Zarządzanie Postaciami"}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users"></i> Zarządzanie Postaciami</h1>
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

<!-- Filtry -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Szukaj po imieniu:</label>
                <input type="text" class="form-control" name="search" value="{$search}" placeholder="Wpisz imię postaci...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sortuj według:</label>
                <select class="form-select" name="sort">
                    <option value="created_at" {if $sort_by == 'created_at'}selected{/if}>Data utworzenia</option>
                    <option value="name" {if $sort_by == 'name'}selected{/if}>Imię</option>
                    <option value="level" {if $sort_by == 'level'}selected{/if}>Poziom</option>
                    <option value="experience" {if $sort_by == 'experience'}selected{/if}>Doświadczenie</option>
                    <option value="last_login" {if $sort_by == 'last_login'}selected{/if}>Ostatnia aktywność</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kolejność:</label>
                <select class="form-select" name="order">
                    <option value="DESC" {if $sort_order == 'DESC'}selected{/if}>Malejąco</option>
                    <option value="ASC" {if $sort_order == 'ASC'}selected{/if}>Rosnąco</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtruj
                    </button>
                    <a href="characters.php" class="btn btn-outline-secondary">Wyczyść</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista postaci -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imię</th>
                        <th>Poziom</th>
                        <th>Doświadczenie</th>
                        <th>Zdrowie</th>
                        <th>Wytrzymałość</th>
                        <th>Broń</th>
                        <th>Walki</th>
                        <th>Ostatnia aktywność</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $characters as $character}
                        <tr>
                            <td>{$character.id}</td>
                            <td>
                                <strong>{$character.name}</strong>
                                <br><small class="text-muted">PIN: {$character.pin}</small>
                            </td>
                            <td><span class="badge bg-primary">{$character.level}</span></td>
                            <td>{$character.experience}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" style="width: {($character.health / $character.max_health) * 100}%">
                                        {$character.health}/{$character.max_health}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-warning" style="width: {($character.stamina / $character.max_stamina) * 100}%">
                                        {$character.stamina}/{$character.max_stamina}
                                    </div>
                                </div>
                            </td>
                            <td>
                                {if $character.weapon_name}
                                    <span class="badge bg-info">{$character.weapon_name}</span>
                                {else}
                                    <span class="badge bg-secondary">Pięść</span>
                                {/if}
                            </td>
                            <td>
                                <small>
                                    Łącznie: {$character.total_battles}<br>
                                    Wygranych: {$character.won_battles}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {$character.last_login|date_format:"%d.%m.%Y %H:%M"}
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{$character.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="character_id" value="{$character.id}">
                                    <button type="submit" name="delete_character" class="btn btn-sm btn-outline-danger" 
                                            data-confirm="Czy na pewno chcesz usunąć postać {$character.name}?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        
                        <!-- Modal edycji -->
                        <div class="modal fade" id="editModal{$character.id}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edytuj postać: {$character.name}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="character_id" value="{$character.id}">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Zdrowie i Wytrzymałość</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Zdrowie</label>
                                                        <input type="number" class="form-control" name="health" value="{$character.health}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Maksymalne zdrowie</label>
                                                        <input type="number" class="form-control" name="max_health" value="{$character.max_health}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Wytrzymałość</label>
                                                        <input type="number" class="form-control" name="stamina" value="{$character.stamina}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Maksymalna wytrzymałość</label>
                                                        <input type="number" class="form-control" name="max_stamina" value="{$character.max_stamina}" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <h6>Statystyki Bojowe</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Obrażenia</label>
                                                        <input type="number" class="form-control" name="damage" value="{$character.damage}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Zręczność</label>
                                                        <input type="number" class="form-control" name="dexterity" value="{$character.dexterity}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Zwinność</label>
                                                        <input type="number" class="form-control" name="agility" value="{$character.agility}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Pancerz</label>
                                                        <input type="number" class="form-control" name="armor" value="{$character.armor}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Maksymalny pancerz</label>
                                                        <input type="number" class="form-control" name="max_armor" value="{$character.max_armor}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Przebicie pancerza</label>
                                                        <input type="number" class="form-control" name="armor_penetration" value="{$character.armor_penetration}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Progresja</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Poziom</label>
                                                        <input type="number" class="form-control" name="level" value="{$character.level}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Doświadczenie</label>
                                                        <input type="number" class="form-control" name="experience" value="{$character.experience}" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <h6>Zasoby Dzienne</h6>
                                                    <div class="mb-3">
                                                        <label class="form-label">Punkty energii</label>
                                                        <input type="number" class="form-control" name="energy_points" value="{$character.energy_points}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Punkty wyzwań</label>
                                                        <input type="number" class="form-control" name="challenge_points" value="{$character.challenge_points}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                            <button type="submit" name="edit_character" class="btn btn-primary">Zapisz zmiany</button>
                                        </div>
                                    </form>
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