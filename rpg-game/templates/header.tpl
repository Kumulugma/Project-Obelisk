<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page_title|default:"RPG Game"}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .stat-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .stat-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .health-bar { background: #dc3545; }
        .stamina-bar { background: #28a745; }
        .energy-bar { background: #007bff; }
        .armor-bar { background: #6c757d; }
        
        .trait-badge {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 8px;
            padding: 4px 8px;
            margin: 2px;
            font-size: 0.8em;
        }
        
        .battle-log {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
        }
        
        .battle-round {
            border-bottom: 1px solid #dee2e6;
            padding: 8px 0;
        }
        
        .battle-round:last-child {
            border-bottom: none;
        }
        
        .trait-activated {
            background: rgba(255, 193, 7, 0.2);
            border-radius: 4px;
            padding: 2px 4px;
            margin: 0 2px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }
    </style>
    {if isset($smarty.get.recaptcha) || $show_recaptcha}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    {/if}
</head>
<body>
