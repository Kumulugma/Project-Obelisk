{include file="header.tpl" page_title="RPG Game - Główna" show_recaptcha=true}

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h1><i class="fas fa-dragon"></i> RPG Game</h1>
                    <p class="mb-0">Stwórz swoją postać i walcz o chwałę!</p>
                </div>
                
                <div class="card-body">
                    {if $error}
                        <div class="alert alert-danger">{$error}</div>
                    {/if}
                    
                    {if $success}
                        <div class="alert alert-success">{$success}</div>
                    {/if}
                    
                    {if $show_pin_info}
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Ważne!</h5>
                            <p><strong>Twój PIN: {$pin}</strong></p>
                            <p>PIN jest jednorazowy i nie może być zmieniony. Zapisz go bezpiecznie!</p>
                            <p>Link do Twojej postaci: <a href="{$character_url}" class="btn btn-sm btn-primary">Przejdź do profilu</a></p>
                        </div>
                    {else}
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fas fa-user-plus"></i> Stwórz Postać</h4>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Imię postaci</label>
                                        <input type="text" class="form-control" id="name" name="name" required maxlength="50">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="secret_code" class="form-label">Kod tajny (opcjonalnie)</label>
                                        <input type="text" class="form-control" id="secret_code" name="secret_code" placeholder="Jeśli posiadasz kod specjalny">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="g-recaptcha" data-sitekey="your_recaptcha_site_key"></div>
                                    </div>
                                    
                                    <button type="submit" name="create_character" class="btn btn-primary w-100">
                                        <i class="fas fa-magic"></i> Stwórz Postać
                                    </button>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h4><i class="fas fa-sign-in-alt"></i> Zaloguj się przez PIN</h4>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="pin" class="form-label">PIN postaci</label>
                                        <input type="text" class="form-control" id="pin" name="pin" required maxlength="6" pattern="[0-9]{6}" placeholder="123456">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="g-recaptcha" data-sitekey="your_recaptcha_site_key"></div>
                                    </div>
                                    
                                    <button type="submit" name="login_pin" class="btn btn-success w-100">
                                        <i class="fas fa-key"></i> Zaloguj przez PIN
                                    </button>
                                </form>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
            
            {if !$show_pin_info}
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle"></i> Jak grać?</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Stwórz postać podając imię</li>
                                <li><i class="fas fa-check text-success"></i> Otrzymasz jednorazowy PIN</li>
                                <li><i class="fas fa-check text-success"></i> Codziennie dostajesz 10 punktów energii</li>
                                <li><i class="fas fa-check text-success"></i> Walcz z losowymi przeciwnikami</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Dodawaj przeciwników do znajomych</li>
                                <li><i class="fas fa-check text-success"></i> Używaj 2 punkty wyzwań na znajomych</li>
                                <li><i class="fas fa-check text-success"></i> Zdobywaj doświadczenie i awansuj</li>
                                <li><i class="fas fa-check text-success"></i> Znajdź lepszą broń i traity</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
        </div>
    </div>
</div>

{include file="footer.tpl"}