{include file="header.tpl" page_title="Ustawienia Systemowe"}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-cogs"></i> Ustawienia Systemowe</h1>
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

<!-- Statystyki systemu -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x mb-2"></i>
                <h3>{$system_stats.total_characters}</h3>
                <p class="mb-0">Wszystkie postaci</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-user-check fa-2x mb-2"></i>
                <h3>{$system_stats.active_users}</h3>
                <p class="mb-0">Aktywni (7 dni)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-danger text-white">
            <div class="card-body text-center">
                <i class="fas fa-sword fa-2x mb-2"></i>
                <h3>{$system_stats.total_battles}</h3>
                <p class="mb-0">Wszystkie walki</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-database fa-2x mb-2"></i>
                <h3>{$system_stats.db_size} MB</h3>
                <p class="mb-0">Rozmiar bazy</p>
            </div>
        </div>
    </div>
</div>

<!-- GŁÓWNE USTAWIENIA SYSTEMOWE -->
<div class="card mb-4">
    <div class="card-header">
        <h4><i class="fas fa-sliders-h"></i> Ustawienia Gry i Rejestracji</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="save_settings" value="1">
            
            <!-- KONTROLA REJESTRACJI -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5><i class="fas fa-user-plus"></i> Kontrola Rejestracji</h5>
                    <hr>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tryb rejestracji</label>
                    <select class="form-select" name="registration_mode" id="registration_mode" onchange="toggleRegistrationSettings()">
                        <option value="open" {if $settings.registration_mode == 'open'}selected{/if}>
                            🟢 Otwarta rejestracja
                        </option>
                        <option value="invite_only" {if $settings.registration_mode == 'invite_only'}selected{/if}>
                            🟡 Tylko z kodem zaproszenia
                        </option>
                        <option value="closed" {if $settings.registration_mode == 'closed'}selected{/if}>
                            🔴 Rejestracja zamknięta
                        </option>
                    </select>
                    <div class="form-text">Kontroluje kto może tworzyć nowe postacie</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status rejestracji</label>
                    <div class="alert alert-sm" id="registration_status">
                        <span id="status_icon"></span> <span id="status_text"></span>
                    </div>
                </div>
            </div>
            
            <!-- KOMUNIKATY REJESTRACJI -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-check-circle text-success"></i> Komunikat otwartej rejestracji
                    </label>
                    <textarea class="form-control" name="registration_message" rows="2" 
                              placeholder="Komunikat wyświetlany gdy rejestracja jest otwarta">{$settings.registration_message}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-key text-warning"></i> Komunikat rejestracji z kodem
                    </label>
                    <textarea class="form-control" name="invite_only_message" rows="2" 
                              placeholder="Komunikat gdy rejestracja wymaga kodu">{$settings.invite_only_message}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="fas fa-times-circle text-danger"></i> Komunikat zamkniętej rejestracji
                    </label>
                    <textarea class="form-control" name="closed_registration_message" rows="2" 
                              placeholder="Komunikat wyświetlany gdy rejestracja jest zamknięta">{$settings.closed_registration_message}</textarea>
                </div>
            </div>
            
            <!-- USTAWIENIA GRY -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5><i class="fas fa-gamepad"></i> Ustawienia Rozgrywki</h5>
                    <hr>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Maksymalna liczba postaci</label>
                    <input type="number" class="form-control" name="max_characters" 
                           value="{$settings.max_characters}" min="1" max="10000" required>
                    <div class="form-text">Globalny limit postaci w grze</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Energia dziennie</label>
                    <input type="number" class="form-control" name="daily_energy" 
                           value="{$settings.daily_energy}" min="1" max="100" required>
                    <div class="form-text">Punkty energii na dzień</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Wyzwania dziennie</label>
                    <input type="number" class="form-control" name="daily_challenges" 
                           value="{$settings.daily_challenges}" min="1" max="20" required>
                    <div class="form-text">Punkty wyzwań na dzień</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Maksimum znajomych</label>
                    <input type="number" class="form-control" name="max_friends" 
                           value="{$settings.max_friends}" min="1" max="100" required>
                    <div class="form-text">Limit znajomych na postać</div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">EXP na poziom</label>
                    <input type="number" class="form-control" name="exp_per_level" 
                           value="{$settings.exp_per_level}" min="50" max="1000" required>
                    <div class="form-text">Doświadczenie potrzebne na awans</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Szansa na trait (%)</label>
                    <input type="number" class="form-control" name="trait_chance" 
                           value="{$settings.trait_chance}" min="0" max="100" step="0.1" required>
                    <div class="form-text">Prawdopodobieństwo otrzymania cechy</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status systemu</label>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success">Online</span>
                        <span class="badge bg-info">{$system_stats.total_characters} postaci</span>
                        <span class="badge bg-warning">{$system_stats.total_battles} walk</span>
                    </div>
                </div>
            </div>
            
            <!-- USTAWIENIA RECAPTCHA -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5><i class="fas fa-shield-alt"></i> Zabezpieczenia reCAPTCHA</h5>
                    <hr>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">reCAPTCHA Site Key</label>
                    <input type="text" class="form-control" name="recaptcha_site_key" 
                           value="{$settings.recaptcha_site_key}" placeholder="6Le...">
                    <div class="form-text">Klucz publiczny reCAPTCHA v2</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">reCAPTCHA Secret Key</label>
                    <input type="password" class="form-control" name="recaptcha_secret_key" 
                           value="{$settings.recaptcha_secret_key}" placeholder="6Le...">
                    <div class="form-text">Klucz prywatny reCAPTCHA v2</div>
                </div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Zapisz Wszystkie Ustawienia
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ZARZĄDZANIE KODAMI TAJNYMI -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-key"></i> Kody Tajne</h4>
        <div>
            <span class="badge bg-primary">Wszystkich: {$code_stats.total}</span>
            <span class="badge bg-success">Aktywnych: {$code_stats.active}</span>
            <span class="badge bg-warning">Bez limitu: {$code_stats.unlimited}</span>
            <span class="badge bg-danger">Wygasłych: {$code_stats.expired}</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Formularz dodawania kodu -->
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="code" placeholder="Kod tajny" required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" name="uses_left" value="1" min="-1" placeholder="Użycia (-1 = bez limitu)">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="description" placeholder="Opis kodu">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_secret_code" class="btn btn-success w-100">
                        <i class="fas fa-plus"></i> Dodaj
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Lista kodów -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Użycia</th>
                        <th>Opis</th>
                        <th>Status</th>
                        <th>Utworzony</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $secret_codes as $code}
                    <tr>
                        <td><code>{$code.code}</code></td>
                        <td>
                            {if $code.uses_left == -1}
                                <span class="badge bg-success">Bez limitu</span>
                            {elseif $code.uses_left == 0}
                                <span class="badge bg-danger">Wyczerpany</span>
                            {else}
                                <span class="badge bg-primary">{$code.uses_left}</span>
                            {/if}
                        </td>
                        <td>{$code.description}</td>
                        <td>
                            {if $code.is_active}
                                <span class="badge bg-success">Aktywny</span>
                            {else}
                                <span class="badge bg-secondary">Nieaktywny</span>
                            {/if}
                        </td>
                        <td><small>{$code.created_at|date_format:"%d.%m.%Y %H:%M"}</small></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="code_id" value="{$code.id}">
                                <button type="submit" name="delete_secret_code" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Czy na pewno usunąć kod {$code.code}?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    {foreachelse}
                    <tr>
                        <td colspan="6" class="text-center text-muted">Brak kodów tajnych</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ZARZĄDZANIE AVATARAMI -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-user-circle"></i> Avatary</h4>
        <div>
            <span class="badge bg-primary">Wszystkich: {$avatar_stats.total}</span>
            <span class="badge bg-success">Aktywnych: {$avatar_stats.active}</span>
            <span class="badge bg-info">Męskich: {$avatar_stats.male}</span>
            <span class="badge bg-danger">Żeńskich: {$avatar_stats.female}</span>
            <span class="badge bg-warning">Unisex: {$avatar_stats.unisex}</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Formularz dodawania avatara -->
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="image_path" placeholder="/images/avatars/avatar.png" required>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="gender" required>
                        <option value="male">Męski</option>
                        <option value="female">Żeński</option>
                        <option value="unisex">Uniwersalny</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" checked>
                        <label class="form-check-label">Aktywny</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_avatar" class="btn btn-success w-100">
                        <i class="fas fa-plus"></i> Dodaj
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Bulk import -->
        <div class="collapse" id="bulkImportCollapse">
            <form method="POST" class="border p-3 mb-3 bg-light rounded">
                <h6>Import wielu avatarów</h6>
                <div class="row">
                    <div class="col-md-8">
                        <textarea class="form-control" name="avatars_data" rows="4" 
                                  placeholder="/images/avatars/male/warrior1.png|male&#10;/images/avatars/female/mage1.png|female&#10;/images/avatars/unisex/mystery1.png|unisex"></textarea>
                        <small class="form-text">Każdy avatar w nowej linii. Format: ścieżka|płeć</small>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select mb-2" name="default_gender">
                            <option value="male">Domyślnie męski</option>
                            <option value="female">Domyślnie żeński</option>
                            <option value="unisex">Domyślnie unisex</option>
                        </select>
                        <button type="submit" name="bulk_add_avatars" class="btn btn-warning w-100">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <button class="btn btn-outline-secondary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#bulkImportCollapse">
            <i class="fas fa-upload"></i> Import wielu avatarów
        </button>
        
        <!-- Lista avatarów -->
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
                            <img src="{$avatar.image_path}" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%;" 
                                 onerror="this.src='/images/avatars/default.png'">
                        </td>
                        <td><code>{$avatar.image_path}</code></td>
                        <td>
                            {if $avatar.gender == 'male'}
                                <span class="badge bg-info">Męski</span>
                            {elseif $avatar.gender == 'female'}
                                <span class="badge bg-danger">Żeński</span>
                            {else}
                                <span class="badge bg-warning">Unisex</span>
                            {/if}
                        </td>
                        <td>
                            {if $avatar.is_active}
                                <span class="badge bg-success">Aktywny</span>
                            {else}
                                <span class="badge bg-secondary">Nieaktywny</span>
                            {/if}
                        </td>
                        <td><span class="badge bg-primary">{$avatar.usage_count}</span></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="avatar_id" value="{$avatar.id}">
                                <button type="submit" name="delete_avatar" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Czy na pewno usunąć ten avatar?')"
                                        {if $avatar.usage_count > 0}disabled title="Avatar jest używany przez postacie"{/if}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    {foreachelse}
                    <tr>
                        <td colspan="6" class="text-center text-muted">Brak avatarów</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

{include file="footer.tpl"}

<script>
function toggleRegistrationSettings() {
    const mode = document.getElementById('registration_mode').value;
    const statusAlert = document.getElementById('registration_status');
    const statusIcon = document.getElementById('status_icon');
    const statusText = document.getElementById('status_text');
    
    // Resetuj klasy alertu
    statusAlert.className = 'alert alert-sm';
    
    switch(mode) {
        case 'open':
            statusAlert.classList.add('alert-success');
            statusIcon.innerHTML = '<i class="fas fa-unlock"></i>';
            statusText.textContent = 'Każdy może tworzyć nowe postacie (z ograniczeniem limitu)';
            break;
            
        case 'invite_only':
            statusAlert.classList.add('alert-warning');
            statusIcon.innerHTML = '<i class="fas fa-key"></i>';
            statusText.textContent = 'Tylko osoby z kodem zaproszenia mogą się zarejestrować';
            break;
            
        case 'closed':
            statusAlert.classList.add('alert-danger');
            statusIcon.innerHTML = '<i class="fas fa-lock"></i>';
            statusText.textContent = 'Rejestracja całkowicie zamknięta - nikt nie może się zarejestrować';
            break;
    }
}

// Wywołaj przy ładowaniu strony
document.addEventListener('DOMContentLoaded', toggleRegistrationSettings);
</script>

<style>
.stat-card {
    transition: transform 0.2s;
    border: none;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}

.form-text {
    font-size: 0.8em;
}
</style>