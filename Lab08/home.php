<?php
session_start();

$session_timeout = 900; // 15 minut

function LogOut(){
    session_unset();
    session_destroy();
    echo'Wylogowano automatycznie';
    header('Location: login.php?timeout=1');
    exit;
}

if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > $session_timeout)) {
    
    // Sesja wygasła – usuń dane i przekieruj
    LogOut();
}

// aktualizuj czas ostatniej aktywności jeśli użytkownik coś robi
$_SESSION['last_activity'] = time();

if($_SESSION['account_loggedin']){
    echo 'Zalogowany';
}
else{
    echo 'Niezalogowany';
}

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../index.css">
        <link rel="stylesheet" href="../lab.css">
    </head>
    <body>
        <form class="form-wrap" action="logout.php">
            <button type="submit" name="log-out">Wyloguj się</button>
        </form>
    </body>
</html>