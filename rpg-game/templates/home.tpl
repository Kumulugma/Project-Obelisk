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
            <a class="navbar-brand" href="{$site_url}">
                <i class="fas fa-dragon"></i> RPG Game
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{$site_url}/admin" target="_blank">
                    <i class="fas fa-cog"></i> Panel Admin
                </a>
            </div>
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

        <div class="row">
            <!-- Lewa kolumna - Formularze -->
            <div class="col-lg-8">
                <!-- Formularz tworzenia postaci - tylko gdy dozwolone -->
                {if $show_registration_form}
                <div class="character-creation-form mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0"><i class="fas fa-user-plus"></i> Stwórz Nową Postać</h3>
                            {if $registration_mode == 'invite_only'}
                            <small class="text-light">
                                <i class="fas fa-key"></i> Wymagany kod zaproszenia
                            </small>
                            {/if}
                        </div>
                        <div class="card-body">
                            {if $error}
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> {$error}
                                </div>
                            {/if}
                            
                            {if $success}
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> {$success}
                                </div>
                            {/if}
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="create_character">
                                
                                <!-- Nazwa postaci -->
                                <div class="mb-3">
                                    <label for="character_name" class="form-label">
                                        <i class="fas fa-signature"></i> Nazwa postaci
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="character_name" 
                                           name="character_name" 
                                           placeholder="Wpisz imię swojego bohatera..."
                                           maxlength="50" 
                                           required>
                                    <div class="form-text">Maksymalnie 50 znaków</div>
                                </div>
                                
                                <!-- Wybór płci -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-venus-mars"></i> Płeć postaci
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-check-lg">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="gender" 
                                                       id="gender_male" 
                                                       value="male" 
                                                       checked 
                                                       onchange="previewAvatars('male')">
                                                <label class="form-check-label" for="gender_male">
                                                    <i class="fas fa-mars text-primary"></i> Mężczyzna
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-check-lg">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="gender" 
                                                       id="gender_female" 
                                                       value="female"
                                                       onchange="previewAvatars('female')">
                                                <label class="form-check-label" for="gender_female">
                                                    <i class="fas fa-venus text-danger"></i> Kobieta
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Podgląd dostępnych avatarów -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-user-circle"></i> Dostępne avatary dla wybranej płci
                                    </label>
                                    <div class="avatar-preview-container">
                                        <div id="male-avatars" class="avatar-grid">
                                            <div class="text-muted mb-2">
                                                <i class="fas fa-dice"></i> Avatary męskie będą losowane podczas tworzenia postaci
                                            </div>
                                            <div class="row">
                                                <div class="col-2"><img src="/images/avatars/male/warrior1.png" class="img-fluid rounded avatar-preview" alt="Wojownik"></div>
                                                <div class="col-2"><img src="/images/avatars/male/knight1.png" class="img-fluid rounded avatar-preview" alt="Rycerz"></div>
                                                <div class="col-2"><img src="/images/avatars/male/mage1.png" class="img-fluid rounded avatar-preview" alt="Mag"></div>
                                                <div class="col-2"><img src="/images/avatars/male/rogue1.png" class="img-fluid rounded avatar-preview" alt="Łotrzyk"></div>
                                                <div class="col-2"><img src="/images/avatars/male/barbarian1.png" class="img-fluid rounded avatar-preview" alt="Barbarzyńca"></div>
                                                <div class="col-2"><img src="/images/avatars/male/paladin1.png" class="img-fluid rounded avatar-preview" alt="Paladyn"></div>
                                            </div>
                                        </div>
                                        
                                        <div id="female-avatars" class="avatar-grid" style="display: none;">
                                            <div class="text-muted mb-2">
                                                <i class="fas fa-dice"></i> Avatary żeńskie będą losowane podczas tworzenia postaci
                                            </div>
                                            <div class="row">
                                                <div class="col-2"><img src="/images/avatars/female/warrior1.png" class="img-fluid rounded avatar-preview" alt="Wojowniczka"></div>
                                                <div class="col-2"><img src="/images/avatars/female/knight1.png" class="img-fluid rounded avatar-preview" alt="Rycerka"></div>
                                                <div class="col-2"><img src="/images/avatars/female/mage1.png" class="img-fluid rounded avatar-preview" alt="Czarodziejka"></div>
                                                <div class="col-2"><img src="/images/avatars/female/rogue1.png" class="img-fluid rounded avatar-preview" alt="Łotrzyczka"></div>
                                                <div class="col-2"><img src="/images/avatars/female/archer1.png" class="img-fluid rounded avatar-preview" alt="Łuczniczka"></div>
                                                <div class="col-2"><img src="/images/avatars/female/sorceress1.png" class="img-fluid rounded avatar-preview" alt="Czarodziejka"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Kod tajny -->
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
                                            Kod zaproszenia jest wymagany do rejestracji
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
                </div>
                {else}
                <!-- Komunikat gdy rejestracja jest zamknięta -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0"><i class="fas fa-user-slash"></i> Rejestracja Niedostępna</h3>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-lock fa-4x text-muted mb-3"></i>
                        <h4>Tworzenie nowych postaci jest obecnie niemożliwe</h4>
                        <p class="text-muted">Rejestracja została tymczasowo wyłączona przez administratorów. Spróbuj ponownie później lub skontaktuj się z administracją.</p>
                        
                        <!-- Możliwość logowania się istniejącymi postaciami -->
                        <div class="mt-4">
                            <h5>Masz już postać? Zaloguj się!</h5>
                            <a href="#login-section" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt"></i> Przejdź do logowania
                            </a>
                        </div>
                    </div>
                </div>
                {/if}

                <!-- Sekcja logowania - zawsze dostępna -->
                <div class="card shadow" id="login-section">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="fas fa-sign-in-alt"></i> Zaloguj się do Istniejącej Postaci</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="login_character">
                            
                            <div class="mb-3">
                                <label for="pin" class="form-label">
                                    <i class="fas fa-key"></i> PIN postaci
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="pin" 
                                       name="pin" 
                                       placeholder="Wpisz 6-cyfrowy PIN swojej postaci..."
                                       maxlength="6" 
                                       pattern="[0-9]{6}"
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

            <!-- Prawa kolumna - Informacje -->
            <div class="col-lg-4">
                <!-- Statystyki gry -->
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

                <!-- Informacje o grze -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-info-circle"></i> O Grze</h4>
                    </div>
                    <div class="card-body">
                        <h5><i class="fas fa-star"></i> Cechy gry:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Tworzenie unikalnych postaci</li>
                            <li><i class="fas fa-check text-success"></i> System walk i doświadczenia</li>
                            <li><i class="fas fa-check text-success"></i> Zróżnicowane bronie i cechy</li>
                            <li><i class="fas fa-check text-success"></i> System znajomych</li>
                            <li><i class="fas fa-check text-success"></i> Codzienne wyzwania</li>
                        </ul>

                        <h5 class="mt-4"><i class="fas fa-gamepad"></i> Jak zacząć:</h5>
                        <ol>
                            <li>Stwórz swoją postać</li>
                            <li>Wybierz płeć i otrzymaj losowy avatar</li>
                            <li>Rozpocznij walkę z przeciwnikami</li>
                            <li>Zdobywaj doświadczenie i nowe umiejętności</li>
                            <li>Dodawaj znajomych i rzucaj im wyzwania</li>
                        </ol>
                    </div>
                </div>

                <!-- Wskazówki -->
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0"><i class="fas fa-lightbulb"></i> Wskazówki</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-shield-alt"></i> Bezpieczeństwo:</h6>
                            <small>Zapamiętaj swój 6-cyfrowy PIN! To jedyny sposób na odzyskanie dostępu do postaci.</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-clock"></i> Energia:</h6>
                            <small>Energia odnawia się codziennie. Wykorzystaj ją mądrze!</small>
                        </div>
                        
                        <div class="alert alert-success">
                            <h6><i class="fas fa-users"></i> Znajomi:</h6>
                            <small>Dodawaj znajomych aby rzucać im wyzwania i zdobywać bonusowe doświadczenie.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-dragon"></i> RPG Game</h5>
                    <p class="mb-0">Prosta gra RPG w przeglądarce. Stwórz swoją postać i rozpocznij przygodę!</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Przydatne linki</h5>
                    <a href="{$site_url}/admin" class="text-light me-3" target="_blank">
                        <i class="fas fa-cog"></i> Panel Admin
                    </a>
                    <a href="#" class="text-light">
                        <i class="fas fa-question-circle"></i> Pomoc
                    </a>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <small>&copy; 2024 RPG Game. Wszystkie prawa zastrzeżone.</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function previewAvatars(gender) {
        // Ukryj wszystkie podglądy avatarów
        const maleAvatars = document.getElementById('male-avatars');
        const femaleAvatars = document.getElementById('female-avatars');
        
        if (maleAvatars) maleAvatars.style.display = 'none';
        if (femaleAvatars) femaleAvatars.style.display = 'none';
        
        // Pokaż avatary dla wybranej płci
        const targetAvatars = document.getElementById(gender + '-avatars');
        if (targetAvatars) targetAvatars.style.display = 'block';
    }

    // Domyślnie pokaż avatary męskie
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('male-avatars')) {
            previewAvatars('male');
        }
        
        // Smooth scroll do sekcji logowania
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
    </script>

    <style>
    .avatar-preview {
        max-height: 60px;
        border: 2px solid transparent;
        transition: border-color 0.3s, transform 0.2s;
    }

    .avatar-preview:hover {
        border-color: #007bff;
        transform: scale(1.1);
    }

    .avatar-grid {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .character-creation-form {
        max-width: 100%;
    }

    .stat-card {
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .form-check-lg .form-check-input {
        width: 1.25em;
        height: 1.25em;
    }

    .form-check-lg .form-check-label {
        font-size: 1.1em;
        font-weight: 500;
    }

    .card {
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .alert {
        border: none;
        border-radius: 8px;
    }

    .btn-lg {
        padding: 12px 30px;
        font-size: 1.1em;
        font-weight: 600;
    }

    footer {
        margin-top: auto;
    }

    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .container {
        flex: 1;
    }

    @media (max-width: 768px) {
        .col-2 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        
        .avatar-preview {
            max-height: 45px;
        }
    }
    </style>
</body>
</html>