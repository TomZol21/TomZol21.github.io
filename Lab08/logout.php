<?php
session_start();

// Usunięcie wszystkich danych sesji
$_SESSION = [];

// Usunięcie cookie sesji (jeśli używasz ciasteczek)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Na koniec niszczymy sesję
session_unset();
session_destroy();

// Przekierowanie na stronę logowania
header("Location: login.php");
exit;
?>