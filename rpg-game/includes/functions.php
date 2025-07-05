<?php
function verifyRecaptcha($response) {
    if (empty(RECAPTCHA_SECRET)) {
        return true;
    }
    
    $data = [
        'secret' => RECAPTCHA_SECRET,
        'response' => $response
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $resultJson = json_decode($result, true);
    
    return $resultJson['success'] ?? false;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setCharacterCookie($characterData) {
    $cookieData = [
        'pin' => $characterData['pin'],
        'hash1' => $characterData['hash1'],
        'hash2' => $characterData['hash2']
    ];
    
    setcookie('rpg_character', json_encode($cookieData), time() + (86400 * 30), '/');
}

function getCharacterFromCookie() {
    if (isset($_COOKIE['rpg_character'])) {
        return json_decode($_COOKIE['rpg_character'], true);
    }
    return null;
}

function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'przed chwilą';
    if ($time < 3600) return floor($time/60) . ' min temu';
    if ($time < 86400) return floor($time/3600) . ' godz temu';
    if ($time < 2592000) return floor($time/86400) . ' dni temu';
    
    return date('d.m.Y', strtotime($datetime));
}

function isMobile() {
    return preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'] ?? '');
}
?>