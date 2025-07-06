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
            <!-- USTAWIENIA REJESTRACJI -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5><i class="fas fa-user-plus"></i> Rejestracja</h5>
                    <hr>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Tryb rejestracji</label>
                    <select class="form-select" name="registration_mode" required>
                        <option value="open" {if $settings.registration_mode == 'open'}selected{/if}>Otwarta</option>
                        <option value="invite_only" {if $settings.registration_mode == 'invite_only'}selected{/if}>Tylko z kodem</option>
                        <option value="closed" {if $settings.registration_mode == 'closed'}selected{/if}>Zamknięta</option>
                    </select>
                </div>
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
            </div>
            
            <div class="row mb-4">
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
                    <label class="form-label">Maksymalna liczba znajomych</label>
                    <input type="number" class="form-control" name="max_friends" 
                           value="{$settings.max_friends}" min="1" max="100" required>
                    <div class="form-text">Limit znajomych na postać</div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Doświadczenie na poziom</label>
                    <input type="number" class="form-control" name="exp_per_level" 
                           value="{$settings.exp_per_level}" min="50" max="1000" required>
                    <div class="form-text">Ile doświadczenia potrzeba na każdy poziom</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Szansa na trait (%)</label>
                    <input type="number" class="form-control" name="trait_chance" 
                           value="{$settings.trait_chance}" min="0" max="100" step="0.1" required>
                    <div class="form-text">Szansa na zdobycie traita po walce</div>
                </div>
            </div>
            
            <!-- USTAWIENIA RECAPTCHA -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5><i class="fas fa-shield-alt"></i> Zabezpieczenia (reCAPTCHA)</h5>
                    <hr>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">reCAPTCHA Site Key</label>
                    <input type="text" class="form-control" name="recaptcha_site_key" 
                           value="{$settings.recaptcha_site_key}" placeholder="6Lc...">
                    <div class="form-text">Klucz publiczny reCAPTCHA</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">reCAPTCHA Secret Key</label>
                    <input type="text" class="form-control" name="recaptcha_secret_key" 
                           value="{$settings.recaptcha_secret_key}" placeholder="6Lc...">
                    <div class="form-text">Klucz prywatny reCAPTCHA</div>
                </div>
            </div>
            
            <div class="text-end">
                <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Zapisz Ustawienia
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ZARZĄDZANIE KODAMI TAJNYMI -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-key"></i> Kody Tajne</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCodeModal">
            <i class="fas fa-plus"></i> Dodaj Kod
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Pozostałe użycia</th>
                        <th>Opis</th>
                        <th>Status</th>
                        <th>Data utworzenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $secret_codes as $code}
                    <tr>
                        <td><code>{$code.code}</code></td>
                        <td>
                            <span class="badge bg-{if $code.uses_left > 0}primary{else}secondary{/if}">
                                {$code.uses_left}
                            </span>
                        </td>
                        <td>{$code.description}</td>
                        <td>
                            {if $code.is_active}
                                <span class="badge bg-success">Aktywny</span>
                            {else}
                                <span class="badge bg-secondary">Nieaktywny</span>
                            {/if}
                        </td>
                        <td>{$code.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCodeModal{$code.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="code_id" value="{$code.id}">
                                <button type="submit" name="delete_secret_code" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Czy na pewno usunąć kod {$code.code}?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    
                    <!-- Modal edycji kodu -->
                    <div class="modal fade" id="editCodeModal{$code.id}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edytuj Kod Tajny</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="code_id" value="{$code.id}">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Kod</label>
                                            <input type="text" class="form-control" name="code" value="{$code.code}" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Pozostałe użycia</label>
                                            <input type="number" class="form-control" name="uses_left" value="{$code.uses_left}" min="0">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Opis</label>
                                            <textarea class="form-control" name="description" rows="2">{$code.description}</textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_active" {if $code.is_active}checked{/if}>
                                                <label class="form-check-label">Aktywny</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                                        <button type="submit" name="edit_secret_code" class="btn btn-primary">Zapisz</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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

<!-- Modal dodawania kodu -->
<div class="modal fade" id="addCodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dodaj Nowy Kod Tajny</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kod</label>
                        <input type="text" class="form-control" name="code" placeholder="np. BETA2024" required>
                        <div class="form-text">Kod który będą wpisywać gracze</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Liczba użyć</label>
                        <input type="number" class="form-control" name="uses_left" value="100" min="1" required>
                        <div class="form-text">Ile razy kod może być użyty</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Opis</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Opis kodu dla adminów"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" name="add_secret_code" class="btn btn-primary">Dodaj Kod</button>
                </div>
            </form>
        </div>
    </div>
</div>

{include file="footer.tpl"}