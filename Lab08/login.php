<?php
session_start();
if (isset($_SESSION['account_loggedin'])) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Logowanie</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../lab.css">
    <link rel="stylesheet" href="../account.css">
</head>
<body>
    <div style="padding:0.6rem; text-align:left;">
        <a href="../index.html" class="return-btn">← Powrót do strony głównej</a>
    </div>
    <div id="pageBackground">
        <h1 id="headlineTitle">Logowanie</h1>
        <form class="form-wrap" action="authenticate.php" method="post" autocomplete="off">
            <fieldset class="fieldset-card">
                <label class="form-label" for="username">Username</label>
                <div class="form-group">
                    <input class="form-input" type="text" name="username" placeholder="Username" id="username" required maxlength="100">
                </div>

                <label class="form-label" for="password">Password</label>
                <div class="form-group">
                    <input class="form-input" type="password" name="password" placeholder="Password" id="password" required>
                </div>

                <input type="hidden" name="type" value="login">
                <button class="btn blue" type="submit">Zaloguj się</button>
            </fieldset>
        </form>
    </div>
</body>
</html>