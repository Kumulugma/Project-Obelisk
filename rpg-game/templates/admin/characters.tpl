{include file="header.tpl" page_title="Zarządzanie Postaciami"}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-users"></i> Zarządzanie Postaciami</h1>
    <div>
        <span class="badge bg-info">Strona {$current_page} z {$total_pages}</span>
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

<!-- Filtry i wyszukiwanie -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Szukaj</label>
                <input type="text" class="form-control" name="search" value="{$search}" placeholder="Nazwa postaci">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sortuj według</label>
                <select class="form-select" name="sort">
                    <option value="created_at" {if $sort_by == 'created_at'}selected{/if}>Data utworzenia</option>
                    <option value="name" {if $sort_by == 'name'}selected{/if}>Nazwa</option>
                    <option value="level" {if $sort_by == 'level'}selected{/if}>Poziom</option>
                    <option value="experience" {if $sort_by == 'experience'}selected{/if}>Doświadczenie</option>
                    <option value="last_login" {if $sort_by == 'last_login'}selected{/if}>Ostatnie logowanie</option>
                    <option value="status" {if $sort_by == 'status'}selected{/if}>Status</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kolejność</label>
                <select class="form-select" name="order">
                    <option value="DESC" {if $sort_order == 'DESC'}selected{/if}>Malejąco</option>
                    <option value="ASC" {if $sort_order == 'ASC'}selected{/if}>Rosnąco</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">
                    <i class="fas fa-search"></i> Szukaj
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista postaci -->
<div class="card">
    <div class="card-header">
        <h4><i class="fas fa-list"></i> Lista Postaci</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Postać</th>
                        <th>Poziom/Exp</th>
                        <th>Status</th>
                        <th>Energia</th>
                        <th>Broń</th>
                        <th>Walki</th>
                        <th>Ostatnie logowanie</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $characters as $character}
                    <tr class="{if $character.status == 'banished'}table-warning{elseif $character.status == 'deleted'}table-danger{/if}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <img src="{if $character.avatar_image}{$character.avatar_image}{else}/images/avatars/default.png{/if}" 
                                         alt="{$character.name}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                         onerror="this.src='/images/avatars/default.png'">
                                </div>
                                <div>
                                    <strong>{$character.name}</strong><br>
                                    <small class="text-muted">PIN: {$character.pin}</small>
                                    {if $character.status == 'banished'}
                                        <br><span class="badge bg-warning">ZBIEG</span>
                                    {/if}
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>Poziom {$character.level}</strong><br>
                            <small class="text-muted">{$character.experience} exp</small>
                        </td>
                        <td>
                            {if $character.status == 'active'}
                                <span class="badge bg-success">Aktywna</span>
                            {elseif $character.status == 'banished'}
                                <span class="badge bg-warning">Zbieg</span>
                            {else}
                                <span class="badge bg-secondary">{$character.status}</span>
                            {/if}
                        </td>
                        <td>
                            <small>
                                Energia: {$character.energy_points}<br>
                                Wyzwania: {$character.challenge_points}
                            </small>
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
                            <div class="btn-group-vertical btn-group-sm">
                                <!-- Edycja -->
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{$character.id}" title="Edytuj">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Regeneracja energii -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="character_id" value="{$character.id}">
                                    <button type="submit" name="regenerate_energy" class="btn btn-outline-success" title="Regeneruj energię" onclick="return confirm('Zregenerować energię dla {$character.name}?')">
                                        <i class="fas fa-bolt"></i>
                                    </button>
                                </form>
                                
                                {if $character.status == 'banished'}
                                    <!-- Przywróć postać -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="character_id" value="{$character.id}">
                                        <button type="submit" name="restore_character" class="btn btn-outline-info" title="Przywróć postać" onclick="return confirm('Przywrócić postać {$character.name}?')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                {else}
                                    <!-- Odbierz postać (Zbieg) -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="character_id" value="{$character.id}">
                                        <button type="submit" name="banish_character" class="btn btn-outline-warning" title="Odbierz postać (Zbieg)" onclick="return confirm('Odebrać postać {$character.name}? Gracz straci do niej dostęp!')">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                    </form>
                                {/if}
                                
                                <!-- Usuń całkowicie -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="character_id" value="{$character.id}">
                                    <button type="submit" name="delete_character" class="btn btn-outline-danger" title="Usuń całkowicie" onclick="return confirm('UWAGA! Czy na pewno chcesz CAŁKOWICIE USUNĄĆ postać {$character.name}? Ta operacja jest nieodwracalna!')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Modal edycji -->
                    <div class="modal fade" id="editModal{$character.id}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edytuj postać: {$character.name}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="character_id" value="{$character.id}">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Zdrowie i Wytrzymałość</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Zdrowie</label>
                                                    <input type="number" class="form-control" name="health" value="{$character.health}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Maksymalne zdrowie</label>
                                                    <input type="number" class="form-control" name="max_health" value="{$character.max_health}" min="1">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Wytrzymałość</label>
                                                    <input type="number" class="form-control" name="stamina" value="{$character.stamina}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Maksymalna wytrzymałość</label>
                                                    <input type="number" class="form-control" name="max_stamina" value="{$character.max_stamina}" min="1">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Umiejętności</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Obrażenia</label>
                                                    <input type="number" class="form-control" name="damage" value="{$character.damage}" min="1">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Zręczność</label>
                                                    <input type="number" class="form-control" name="dexterity" value="{$character.dexterity}" min="1">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Zwinność</label>
                                                    <input type="number" class="form-control" name="agility" value="{$character.agility}" min="1">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Pancerz</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Pancerz</label>
                                                    <input type="number" class="form-control" name="armor" value="{$character.armor}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Maksymalny pancerz</label>
                                                    <input type="number" class="form-control" name="max_armor" value="{$character.max_armor}" min="1">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Przebicie pancerza</label>
                                                    <input type="number" class="form-control" name="armor_penetration" value="{$character.armor_penetration}" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Poziom i Energia</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Poziom</label>
                                                    <input type="number" class="form-control" name="level" value="{$character.level}" min="1">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Doświadczenie</label>
                                                    <input type="number" class="form-control" name="experience" value="{$character.experience}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Punkty energii</label>
                                                    <input type="number" class="form-control" name="energy_points" value="{$character.energy_points}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Punkty wyzwań</label>
                                                    <input type="number" class="form-control" name="challenge_points" value="{$character.challenge_points}" min="0">
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
                    {foreachelse}
                    <tr>
                        <td colspan="8" class="text-center text-muted">Brak postaci do wyświetlenia</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        
        <!-- Paginacja -->
        {if $total_pages > 1}
        <nav aria-label="Paginacja postaci" class="mt-4">
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
                    {elseif $i <= 3 || $i > $total_pages-3 || ($i >= $current_page-2 && $i <= $current_page+2)}
                        <li class="page-item">
                            <a class="page-link" href="?page={$i}&search={$search}&sort={$sort_by}&order={$sort_order}">{$i}</a>
                        </li>
                    {elseif $i == 4 && $current_page > 6}
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    {elseif $i == $total_pages-3 && $current_page < $total_pages-5}
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