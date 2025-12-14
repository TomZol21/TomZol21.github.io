<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Baza danych</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../lab.css">
    <style>
        :root {
            /* --- Red palette --- */
            --delete-bg: rgb(200, 40, 60);
            --delete-border: rgb(170, 30, 50);
            --delete-hover: linear-gradient(145deg, rgb(220, 60, 80), rgb(170, 30, 50));
            /* --- Edit button palette (light) --- */
            --edit-bg:    rgb(245, 155, 70);
            --edit-border: rgb(225, 140, 65);
            --edit-hover: linear-gradient(145deg, rgb(255, 170, 90), rgb(225, 140, 65));
            /* --- Confirm edit button palette (light) --- */
            --confirm-edit-bg: rgb(125, 185, 95);       /* stonowana, nasycona zieleń */
            --confirm-edit-border: rgb(110, 165, 85);   /* lekko ciemniejsza dla subtelnego efektu */
            --confirm-edit-hover: linear-gradient(145deg, rgb(140, 200, 110), rgb(110, 165, 85));
        }

        h2{ margin: 0.2rem 0 0.5rem 0; }
        .form-wrap{ width: 100%; }
        .four-cols{ display: flex; flex-direction: row; }
        .name{ flex: 4; }
        .other{ flex: 1; }
        .edit{}

        button[type="submit"].delete { background: var(--delete-bg); border: 2px solid var(--delete-border); }
        button[type="submit"].delete:hover { background: var(--delete-hover); transform: translateY(-1px); }
        button[type="submit"].edit { background: var(--edit-bg); border: 2px solid var(--edit-border); }
        button[type="submit"].edit:hover { background: var(--edit-hover); transform: translateY(-1px); }
        button[type="submit"].confirm-edit { background: var(--confirm-edit-bg); border: 2px solid var(--confirm-edit-border); }
        button[type="submit"].confirm-edit:hover { background: var(--confirm-edit-hover); transform: translateY(-1px); }

        /* simple cell layout for rows */
        .cell{ padding:0.4rem; border-bottom:1px solid #ddd; }
        .row{ display:flex; gap:0.6rem; align-items:center; }
        .row .collumn{ flex:1; }
        .row .collumn.small{ flex:0 0 6rem; }
        form.inline{ display:inline-block; margin:0; }
    </style>
</head>
<body>
    <div style="padding:0.6rem; text-align:left;">
        <a href="../index.html" class="return-btn">← Powrót do strony głównej</a>
    </div>

    <?php
    // --- KONFIGURACJA ---
    $servername = "localhost";
    $username   = "tomekzoladek";
    $password   = "PogMega1234";
    $dbname     = "tomekzoladek";

    // --- POŁĄCZENIE ---
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Błąd połączenia: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // --- ZMIENNE I STAN ---
    $errors = [];
    $editingId = null; // kiedy niepuste - renderujemy pola edycji dla tego rekordu

    // --- OBSŁUGA POST (ADD / START_EDIT / CANCEL / CONFIRM_EDIT / DELETE) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ROZPOCZNIJ TRYB EDYCJI (pokazuje inputy dla jednego wiersza)
        if (isset($_POST['start_edit'])) {
            $editingId = $_POST['start_edit'];
        }

        // ANULUJ EDYCJĘ - użyjemy PRG, żeby wyczyścić POST
        if (isset($_POST['cancel'])) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        // POTWIERDŹ EDYCJĘ
        if (isset($_POST['confirm_edit'])) {
            $id = $_POST['confirm_edit'];
            $first = trim($_POST['firstName'] ?? '');
            $last  = trim($_POST['lastName'] ?? '');
            $age   = trim($_POST['age'] ?? '');

            if ($first === '') $errors[] = 'Imię jest wymagane.';
            if ($last === '')  $errors[] = 'Nazwisko jest wymagane.';
            if ($age === '' || !is_numeric($age) || (int)$age < 0) $errors[] = 'Wiek musi być liczbą nieujemną.';

            if (empty($errors)) {
                $wiekInt = (int)$age;
                $stmt = $conn->prepare("UPDATE studenci SET Imie = ?, Nazwisko = ?, Wiek = ? WHERE Id = ?");
                if ($stmt === false) {
                    error_log("Prepare failed: " . $conn->error);
                    $errors[] = 'Błąd serwera (prepare).';
                } else {
                    $stmt->bind_param('ssii', $first, $last, $wiekInt, $id);
                    if (!$stmt->execute()) {
                        error_log("Execute failed: " . $stmt->error);
                        $errors[] = 'Błąd zapisu do bazy: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }

            if (empty($errors)) {
                // PRG - redirect to clear POST and exit editing mode
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                // jeśli błąd - zostań w trybie edycji dla tego rekordu i pokaż błędy
                $editingId = $id;
            }
        }

        // USUWANIE
        if (isset($_POST['delete'])) {
            $id = $_POST['delete'];
            $stmt = $conn->prepare("DELETE FROM studenci WHERE Id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
            } else {
                error_log("Prepare failed: " . $conn->error);
                $errors[] = 'Błąd serwera (prepare delete).';
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        // DODANIE NOWEGO REKORDU
        if (isset($_POST['add'])) {
            $firstName = trim($_POST['firstName'] ?? '');
            $lastName  = trim($_POST['lastName'] ?? '');
            $age       = trim($_POST['age'] ?? '');

            if ($firstName === '') $errors[] = 'Imię jest wymagane.';
            if ($lastName === '')  $errors[] = 'Nazwisko jest wymagane.';
            if ($age === '' || !is_numeric($age) || (int)$age < 0) $errors[] = 'Wiek musi być liczbą nieujemną.';

            if (empty($errors)) {
                $stmt = $conn->prepare("INSERT INTO studenci (Imie, Nazwisko, Wiek) VALUES (?, ?, ?)");
                if ($stmt === false) {
                    error_log("Prepare failed: " . $conn->error);
                    $errors[] = 'Błąd serwera (prepare).';
                } else {
                    $wiekInt = (int)$age;
                    $stmt->bind_param('ssi', $firstName, $lastName, $wiekInt);
                    if (!$stmt->execute()) {
                        error_log("Execute failed: " . $stmt->error);
                        $errors[] = 'Błąd zapisu do bazy: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }

            if (empty($errors)) {
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    }

    // --- FUNKCJA WYŚWIETLANIA ---
    function DisplayStudents($conn, $editingId = null, &$errors = null): void {
        $sql = "SELECT * FROM studenci ORDER BY Id";
        $result = $conn->query($sql);

        if ($result === false) {
            echo "<p>Błąd zapytania: " . htmlspecialchars($conn->error) . "</p>";
            return;
        }

        if ($result->num_rows === 0) {
            echo "<p>Brak wyników.</p>";
            return;
        }

        echo "<div class='row'>";
        echo "<div class='collumn small'> Id </div>";
        echo "<div class='collumn'> Imie </div>";
        echo "<div class='collumn'> Nazwisko </div>";
        echo "<div class='collumn small'> Wiek </div>";
        echo "<div class='collumn small'></div>";
        echo "<div class='collumn small'></div>";
        echo "</div>";
        while ($row = $result->fetch_assoc()) {
            $id = $row['Id'];
            echo "<div class='cell'>";
            echo "<div class='row'>";

            // jeśli obecnie edytujemy ten rekord — pokaż formularz z inputami
            if ($editingId !== null && $editingId === $id) {
                // prefill values (escape for HTML)
                $imie = htmlspecialchars($row['Imie']);
                $nazw = htmlspecialchars($row['Nazwisko']);
                $wiek = (int)$row['Wiek'];

                echo "<form class='inline' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='post'>";
                echo "<input class='collumn' type='text' name='firstName' value='" . $imie . "' required>";
                echo "<input class='collumn' type='text' name='lastName' value='" . $nazw . "' required>";
                echo "<input class='collumn small' type='number' name='age' value='" . $wiek . "' required>";
                echo "<button type='submit' name='confirm_edit' value='" . $id . "' class='confirm-edit' onsubmit=\"return confirm('Na pewno zmienić rekord?');\">Zatwierdź</button>";
                echo "<button type='submit' name='cancel' value='1' class='delete' onsubmit=\"return confirm('Czy na pewno chcesz się wycofać? Stracisz zmiany.');\">Cofnij</button>";
                echo "</form>";
            } else {
                // normalny widok wiersza
                echo "<div class='collumn small'>" . $row['Id'] . "</div>";
                echo "<div class='collumn'>" . htmlspecialchars($row['Imie']) . "</div>";
                echo "<div class='collumn'>" . htmlspecialchars($row['Nazwisko']) . "</div>";
                echo "<div class='collumn small'>" . $row['Wiek'] . "</div>";

                // przycisk start_edit
                echo "<form class='inline' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='post'>";
                echo "<button type='submit' name='start_edit' value='" . $id . "' class='edit'>Edytuj</button>";
                echo "</form>";

                // przycisk delete
                echo "<form class='inline' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='post' onsubmit=\"return confirm('Na pewno usunąć rekord?');\">";
                echo "<button type='submit' name='delete' value='" . $id . "' class='delete'>Usuń</button>";
                echo "</form>";
            }

            echo "</div>"; 
            echo "</div>";
        }

        $result->free();
    }
    ?>

    <div id="pageBackground">
        <h1 id="headlineTitle">Baza danych</h1>

        <div id="studentsList" class="collumn">
            <?php
                // wyświetl błędy (jeśli są)
                if (!empty($errors)) {
                    echo "<div class='errors' style='color:darkred;padding:0.5rem;'>";
                    foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>";
                    echo "</div>";
                }

                DisplayStudents($conn, $editingId, $errors);
            ?>

            <form class="form-wrap" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <fieldset class="fieldset-card">
                    <legend>Dane osobowe do dodania</legend>

                    <div class="form-row four-cols">
                        <div class="form-group name">
                            <label for="fname">Imię</label>
                            <input type="text" id="fname" name="firstName" required>
                        </div>

                        <div class="form-group name">
                            <label for="lname">Nazwisko</label>
                            <input type="text" id="lname" name="lastName" required>
                        </div>

                        <div class="form-group other">
                            <label for="age">Wiek</label>
                            <input type="number" id="age" name="age" required>
                        </div>

                        <div class="form-group other" style="margin-top: auto;">
                            <input type="submit" value="Wyślij" name="add">
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</body>
</html>
