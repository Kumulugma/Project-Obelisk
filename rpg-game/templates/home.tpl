<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPG Game - Strona Główna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    {if $recaptcha_site_key}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    {/if}
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-dragon"></i> RPG Game
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Status rejestracji -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-{$registration_info.type} alert-dismissible fade show">
                    <i class="fas {if $registration_info.type == 'success'}fa-check-circle{elseif $registration_info.type == 'warning'}fa-exclamation-triangle{else}fa-times-circle{/if}"></i>
                    {$registration_info.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>

        <!-- Komunikaty błędów i sukcesów -->
        {if $error}
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {$error}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        {/if}

        {if $success}
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {$success}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        {/if}

        <div class="row">
            <!-- Lewa kolumna - Formularze -->
            <div class="col-lg-8">
                <!-- Formularz tworzenia postaci -->
                {if $show_registration_form}
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-user-plus"></i> Stwórz Nową Postać</h3>
                        {if $registration_mode == 'invite_only'}
                            <small>Wymagany jest kod zaproszenia</small>
                        {/if}
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="create_character">
                            
                            <!-- Nazwa postaci -->
                            <div class="mb-3">
                                <label for="character_name" class="form-label">
                                    <i class="fas fa-user"></i> Nazwa postaci <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="character_name" 
                                       name="character_name" 
                                       placeholder="Wpisz nazwę swojej postaci"
                                       maxlength="50"
                                       required>
                                <div class="form-text">Maksymalnie 50 znaków. Dozwolone: litery, cyfry, spacje, myślniki.</div>
                            </div>
                            
                            <!-- Płeć -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-venus-mars"></i> Płeć <span class="text-danger">*</span>
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" checked>
                                            <label class="form-check-label" for="male">
                                                <i class="fas fa-mars text-primary"></i> Mężczyzna
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                                            <label class="form-check-label" for="female">
                                                <i class="fas fa-venus text-danger"></i> Kobieta
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Kod tajny - zawsze widoczne, ale wymagane tylko w trybie invite_only -->
                            <div class="mb-3">
                                <label for="secret_code" class="form-label">
                                    <i class="fas fa-key"></i> Kod tajny 
                                    {if $registration_mode == 'invite_only'}
                                        <span class="text-danger">*</span>
                                    {else}
                                        <span class="text-muted">(opcjonalny)</span>
                                    {/if}
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="secret_code" 
                                       name="secret_code" 
                                       placeholder="{if $registration_mode == 'invite_only'}Wpisz wymagany kod zaproszenia{else}Wpisz kod, jeśli go posiadasz{/if}"
                                       {if $registration_mode == 'invite_only'}required{/if}>
                                <div class="form-text">
                                    {if $registration_mode == 'invite_only'}
                                        <span class="text-danger">Kod zaproszenia jest wymagany do rejestracji</span>
                                    {else}
                                        Kod pozwala na utworzenie postaci mimo limitu
                                    {/if}
                                </div>
                            </div>
                            
                            <!-- reCAPTCHA -->
                            {if $recaptcha_site_key}
                            <div class="mb-3">
                                <div class="g-recaptcha" data-sitekey="{$recaptcha_site_key}"></div>
                            </div>
                            {/if}
                            
                            <!-- Przycisk tworzenia -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-magic"></i> Stwórz Postać
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                {/if}

                <!-- Formularz logowania -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-sign-in-alt"></i> Zaloguj się do Istniejącej Postaci</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="login_character">
                            
                            <div class="mb-3">
                                <label for="pin" class="form-label">
                                    <i class="fas fa-lock"></i> PIN postaci <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="pin" 
                                       name="pin" 
                                       placeholder="123456"
                                       inputmode="numeric"
                                       maxlength="6"
                                       required>
                                <div class="form-text">PIN składa się z 6 cyfr</div>
                            </div>
                            
                            {if $recaptcha_site_key}
                            <div class="mb-3">
                                <div class="g-recaptcha" data-sitekey="{$recaptcha_site_key}"></div>
                            </div>
                            {/if}
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-play"></i> Zaloguj się
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Prawa kolumna - Statystyki -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Statystyki Gry</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12 mb-3">
                                <i class="fas fa-users fa-2x text-primary mb-2 d-block"></i>
                                <h3 class="mb-0">{$stats.total_characters}</h3>
                                <p class="text-muted mb-0">Wszystkich bohaterów</p>
                            </div>
                            <div class="col-6">
                                <i class="fas fa-sword fa-2x text-danger mb-2 d-block"></i>
                                <h4 class="mb-0">{$stats.total_battles}</h4>
                                <p class="text-muted mb-0">Stoczonych walk</p>
                            </div>
                            <div class="col-6">
                                <i class="fas fa-user-check fa-2x text-success mb-2 d-block"></i>
                                <h4 class="mb-0">{$stats.active_today}</h4>
                                <p class="text-muted mb-0">Aktywni dziś</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informacja o grze -->
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> O Grze</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><i class="fas fa-gamepad text-primary"></i> Prosta gra RPG w przeglądarce</p>
                        <p class="mb-2"><i class="fas fa-users text-success"></i> Walcz z innymi graczami</p>
                        <p class="mb-2"><i class="fas fa-sword text-danger"></i> Zdobywaj bronie i umiejętności</p>
                        <p class="mb-0"><i class="fas fa-level-up-alt text-warning"></i> Rozwijaj swoją postać</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h5><i class="fas fa-dragon"></i> RPG Game</h5>
                    <p class="mb-0">Prosta gra RPG w przeglądarce. Stwórz postać i rozpocznij przygodę!</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-0 text-muted">
                        <small>Wersja 1.0</small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Walidacja PIN-u po stronie klienta
        document.getElementById('pin').addEventListener('input', function(e) {
            // Usuń wszystko co nie jest cyfrą
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Ogranicz do 6 cyfr
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
        
        // Automatyczne przesyłanie formularza po wpisaniu 6 cyfr
        document.getElementById('pin').addEventListener('keyup', function(e) {
            if (this.value.length === 6 && e.key !== 'Backspace') {
                // Sprawdź czy reCAPTCHA jest wymagana
                const recaptcha = document.querySelector('.g-recaptcha');
                if (!recaptcha) {
                    // Jeśli nie ma reCAPTCHA, automatycznie wyślij formularz
                    this.form.submit();
                }
            }
        });
    </script>
</body>
</html>