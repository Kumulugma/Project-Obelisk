{include file="header.tpl" page_title="Ustawienia Systemowe"}

<div class="col-md-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-cogs"></i> Ustawienia Systemowe</h1>
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

    <!-- Statystyki systemu -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3>{$system_stats.total_characters}</h3>
                    <p class="mb-0">Wszystkie postaci</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h3>{$system_stats.active_users}</h3>
                    <p class="mb-0">Aktywni (7 dni)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-sword fa-2x mb-2"></i>
                    <h3>{$system_stats.total_battles}</h3>
                    <p class="mb-0">Wszystkie walki</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-2x mb-2"></i>
                    <h3>{$system_stats.db_size} MB</h3>
                    <p class="mb-0">Rozmiar bazy</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ustawienia gry -->
    <div class="card mb-4">
        <div class="card-header">
            <h4><i class="fas fa-gamepad"></i> Ustawienia Gry</h4>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_characters" class="form-label">Maksymalna liczba postaci</label>
                            <input type="number" class="form-control" id="max_characters" name="max_characters" value="{$settings.max_characters}" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="daily_energy" class="form-label">Dzienne punkty energii</label>
                            <input type="number" class="form-control" id="daily_energy" name="daily_energy" value="{$settings.daily_energy}" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="daily_challenges" class="form-label">Dzienne punkty wyzwań</label>
                            <input type="number" class="form-control" id="daily_challenges" name="daily_challenges" value="{$settings.daily_challenges}" min="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_friends" class="form-label">Maksymalna liczba znajomych</label>
                            <input type="number" class="form-control" id="max_friends" name="max_friends" value="{$settings.max_friends}" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="exp_per_level" class="form-label">Doświadczenie na poziom</label>
                            <input type="number" class="form-control" id="exp_per_level" name="exp_per_level" value="{$settings.exp_per_level}" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="trait_chance" class="form-label">Szansa na trait przy awansie</label>
                            <input type="number" class="form-control" id="trait_chance" name="trait_chance" value="{$settings.trait_chance}" min="0" max="1" step="0.01">
                        </div>
                    </div>
                </div>
                
                <h5 class="mt-4"><i class="fas fa-shield-alt"></i> Ustawienia reCAPTCHA</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="recaptcha_site_key" class="form-label">reCAPTCHA Site Key</label>
                            <input type="text" class="form-control" id="recaptcha_site_key" name="recaptcha_site_key" value="{$settings.recaptcha_site_key}" placeholder="6Lc...">
                            <div class="form-text">Klucz publiczny z Google reCAPTCHA</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="recaptcha_secret_key" class="form-label">reCAPTCHA Secret Key</label>
                            <input type="text" class="form-control" id="recaptcha_secret_key" name="recaptcha_secret_key" value="{$settings.recaptcha_secret_key}" placeholder="6Lc...">
                            <div class="form-text">Klucz prywatny z Google reCAPTCHA</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="save_settings" class="btn btn-primary">
                    <i class="fas fa-save"></i> Zapisz ustawienia
                </button>
            </form>
        </div>
    </div>

    <!-- Kody tajne -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-key"></i> Kody Tajne</h4>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCodeModal">
                <i class="fas fa-plus"></i> Dodaj kod
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>Pozostałe użycia</th>
                            <th>Opis</th>
                            <th>Status</th>
                            <th>Utworzony</th>
                            <th>Razy użyty</th>
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
                                    {else}
                                        <span class="badge bg-info">{$code.uses_left}</span>
                                    {/if}
                                </td>
                                <td>{$code.description}</td>
                                <td>
                                    {if $code.is_active}
                                        <span class="badge bg-success">Aktywny</span>
                                    {else}
                                        <span class="badge bg-danger">Nieaktywny</span>
                                    {/if}
                                </td>
                                <td>{$code.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
                                <td><span class="badge bg-secondary">{$code.times_used}</span></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="code_id" value="{$code.id}">
                                        {if $code.is_active}
                                            <button type="submit" name="toggle_code" class="btn btn-sm btn-warning">
                                                <i class="fas fa-pause"></i> Dezaktywuj
                                            </button>
                                        {else}
                                            <input type="hidden" name="is_active" value="1">
                                            <button type="submit" name="toggle_code" class="btn btn-sm btn-success">
                                                <i class="fas fa-play"></i> Aktywuj
                                            </button>
                                        {/if}
                                    </form>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td colspan="7" class="text-center text-muted">Brak kodów tajnych</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Avatary -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-user-circle"></i> Dostępne Avatary</h4>
        </div>
        <div class="card-body">
            <div class="row">
                {foreach $avatars as $avatar}
                    <div class="col-md-2 mb-3">
                        <div class="card text-center">
                            <div class="card-body p-2">
                                <img src="{$avatar.image_path}" width="64" height="64" alt="Avatar {$avatar.id}" class="mb-2">
                                <small class="d-block">ID: {$avatar.id}</small>
                                {if $avatar.is_active}
                                    <span class="badge bg-success">Aktywny</span>
                                {else}
                                    <span class="badge bg-danger">Nieaktywny</span>
                                {/if}
                            </div>
                        </div>
                    </div>
                {foreachelse}
                    <div class="col-12">
                        <p class="text-muted text-center">Brak dostępnych avatarów</p>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>

<!-- Modal dodawania kodu -->
<div class="modal fade" id="addCodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dodaj kod tajny</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label">Kod</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="uses_left" class="form-label">Liczba użyć (-1 = bez limitu)</label>
                        <input type="number" class="form-control" id="uses_left" name="uses_left" value="1" min="-1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Opis</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="submit" name="add_secret_code" class="btn btn-primary">Dodaj kod</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{include file="footer.tpl"}