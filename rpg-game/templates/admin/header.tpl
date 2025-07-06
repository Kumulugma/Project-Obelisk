<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page_title|default:"Panel Administracyjny"} - RPG Game</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-group-vertical .btn {
            margin-bottom: 2px;
        }
        .table th {
            border-top: none;
            background: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            font-size: 0.75em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h4 class="text-white mb-4">
                    <i class="fas fa-shield-alt"></i> Admin Panel
                </h4>
                
                <div class="text-white-50 small mb-3">
                    Zalogowany jako: <strong class="text-white">{$admin_username}</strong>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'index.php'}active{/if}" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'characters.php'}active{/if}" href="characters.php">
                        <i class="fas fa-users"></i> Postaci
                    </a>
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'weapons.php'}active{/if}" href="weapons.php">
                        <i class="fas fa-sword"></i> Bronie
                    </a>
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'traits.php'}active{/if}" href="traits.php">
                        <i class="fas fa-magic"></i> Traity
                    </a>
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'battles.php'}active{/if}" href="battles.php">
                        <i class="fas fa-fist-raised"></i> Walki
                    </a>
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'avatars.php'}active{/if}" href="avatars.php">
                        <i class="fas fa-user-circle"></i> Avatary
                    </a>
                    <a class="nav-link {if $smarty.server.PHP_SELF|basename == 'settings.php'}active{/if}" href="settings.php">
                        <i class="fas fa-cog"></i> Ustawienia
                    </a>
                    <hr class="my-3">
                    <a class="nav-link" href="../" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Strona główna
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Wyloguj
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content p-4">