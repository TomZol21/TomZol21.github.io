<?php
// login_register.php
declare(strict_types=1);
session_start();

// --- Konfiguracja DB (zmień zgodnie ze środowiskiem) ---
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'tomekzoladek';
$DATABASE_PASS = "PogMega1234";
$DATABASE_NAME = 'tomekzoladek';

// --- Ustawienia behawioru ---
/**
 * Jeśli true — przy błędnym haśle zostanie wykonane przekierowanie do register.php.
 * UWAGA: ujawnia to, że konto istnieje (różnicuje "nieistniejący użytkownik" vs "złe hasło"),
 * co jest potencjalnym ryzykiem informacyjnym. Zalecane: false w środowisku produkcyjnym.
 */
$REDIRECT_ON_WRONG_PASSWORD = false;
$REGISTER_URL = 'register.php';

// --- Ustawienia sesji dla bezpieczeństwa (opcjonalne, konfigurowalne) ---
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

// --- Połączenie z DB ---
$con = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if ($con->connect_errno) {
    // Nie ujawniamy szczegółów w produkcji
    error_log("DB connect error: " . $con->connect_error);
    http_response_code(500);
    exit('Internal server error.');
}
$con->set_charset('utf8mb4');

// --- Odbierz dane (bez powtarzania pytań użytkownikowi) ---
$username = isset($_POST['username']) ? trim((string)$_POST['username']) : null;
$password_plain = isset($_POST['password']) ? (string)$_POST['password'] : null;
$type = isset($_POST['type']) ? (string)$_POST['type'] : null;

if ($username === null || $password_plain === null || $type === null) {
    exit('Uzupełnij pola username, password i type.');
}

// --- Prosta walidacja nazwy użytkownika ---
if ($username === '' || $password_plain === '') {
    exit('Username i hasło nie mogą być puste.');
}
if (mb_strlen($username) > 100) {
    exit('Nazwa użytkownika jest za długa.');
}
// dozwolone znaki: litery, cyfry, . _ - @ (dostosuj według potrzeby)
if (!preg_match('/^[A-Za-z0-9._@-]{3,100}$/u', $username)) {
    exit('Nazwa użytkownika zawiera niedozwolone znaki lub jest za krótka.');
}

if ($type === 'register') {
    // ----- WALIDACJA HASŁA (przykład minimalnych reguł) -----
    // wymagamy: min 8 znaków, przynajmniej mała litera, wielka litera, cyfra, znak specjalny
    $minLen = 8;
    $hasLower = preg_match('/[a-z]/', $password_plain) === 1;
    $hasUpper = preg_match('/[A-Z]/', $password_plain) === 1;
    $hasDigit = preg_match('/\d/', $password_plain) === 1; // uwzględnia 0
    $hasSpec  = preg_match('/[^A-Za-z0-9]/', $password_plain) === 1;
    $lenOk    = mb_strlen($password_plain, 'UTF-8') >= $minLen;

    if (!($hasLower && $hasUpper && $hasDigit && $hasSpec && $lenOk)) {
        exit("Hasło musi mieć co najmniej {$minLen} znaków i zawierać małą i wielką literę, cyfrę oraz znak specjalny.");
    }

    // Sprawdź czy użytkownik już istnieje
    $stmt = $con->prepare('SELECT Id FROM users WHERE Username = ? LIMIT 1');
    if (!$stmt) {
        error_log("Prepare failed (select user): " . $con->error);
        exit('Błąd wewnętrzny.');
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        exit('Konto o tej nazwie użytkownika już istnieje.');
    }
    $stmt->close();

    // Wstaw nowe konto
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $date = date("Y-m-d H:i:s");
    $stmt = $con->prepare('INSERT INTO users (Username, Password, Date) VALUES (?, ?, ?)');
    if (!$stmt) {
        error_log("Prepare failed (insert): " . $con->error);
        exit('Błąd wewnętrzny.');
    }
    $stmt->bind_param('sss', $username, $password_hash, $date);
    if (!$stmt->execute()) {
        error_log("Insert user error: " . $stmt->error);
        $stmt->close();
        exit('Nie udało się utworzyć konta.');
    }
    $newId = $stmt->insert_id;
    $stmt->close();

    // Sesja
    session_regenerate_id(true);
    $_SESSION['account_loggedin'] = true;
    $_SESSION['account_name'] = $username;
    $_SESSION['account_id'] = $newId;

    header('Location: home.php');
    exit;

} elseif ($type === 'login') {
    // Pobierz hash z DB (bez ujawniania zbyt wielu informacji)
    $stmt = $con->prepare('SELECT Id, Password FROM users WHERE Username = ? LIMIT 1');
    if (!$stmt) {
        error_log("Prepare failed (select for login): " . $con->error);
        exit('Błąd wewnętrzny.');
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Nie znajdujemy użytkownika — nie precyzujemy dlaczego (bezpieczniej)
        $stmt->close();
        // Możemy przekierować do rejestracji z prefillem nazwy:
        $redirectUrl = $REGISTER_URL . '?prefill=' . urlencode($username) . '&reason=not_found';
        header('Location: ' . $redirectUrl);
        exit;
    }

    $stmt->bind_result($id, $password_hash_from_db);
    $stmt->fetch();

    // Weryfikacja hasła
    if (password_verify($password_plain, $password_hash_from_db)) {
        // poprawne hasło
        session_regenerate_id(true);
        $_SESSION['account_loggedin'] = true;
        $_SESSION['account_name'] = $username;
        $_SESSION['account_id'] = $id;

        $stmt->close();
        header('Location: home.php');
        exit;
    } else {
        $stmt->close();

        // Jeśli chcesz przekierować tylko przy złym haśle (ryzyko info leak), możesz to zrobić:
        if ($REDIRECT_ON_WRONG_PASSWORD) {
            // przekierowujemy do register.php (nie dołączamy hasła!)
            $redirectUrl = $REGISTER_URL . '?prefill=' . urlencode($username) . '&reason=wrong_password';
            header('Location: ' . $redirectUrl);
            exit;
        }

        // Domyślnie: nie ujawniamy który element był nieprawidłowy
        exit('Niepoprawny login lub hasło.');
    }
} else {
    exit('Nieznany typ żądania.');
}