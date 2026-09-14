<?php
// flash message helper - displays styled popup/toast
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('outputFlash')) {
    function outputFlash($text, $type='success') {
        $textEsc = htmlspecialchars($text, ENT_QUOTES);
        echo <<<HTML
<div class="flash-message flash-$type" id="flash-message">
    $textEsc
</div>
<script>
    // after a short delay, fade out and remove
    setTimeout(function() {
        var el = document.getElementById('flash-message');
        if (el) {
            el.classList.add('hide');
            setTimeout(function(){ el.remove(); }, 600);
        }
    }, 3000);
</script>
HTML;
    }
}

// check session message first
if (isset($_SESSION['message'])) {
    $data = $_SESSION['message'];
    $text = '';
    $type = 'success';
    if (is_array($data)) {
        $text = $data['text'];
        if (isset($data['type'])) {
            $type = $data['type'];
        }
    } else {
        $text = $data;
    }
    outputFlash($text, $type);
    unset($_SESSION['message']);
} elseif (isset($_COOKIE['flash'])) {
    $cookie = json_decode($_COOKIE['flash'], true);
    if ($cookie && isset($cookie['text'])) {
        $type = $cookie['type'] ?? 'success';
        outputFlash($cookie['text'], $type);
    }
    setcookie('flash', '', time() - 3600, '/');
}
