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
    <title>Rejestracja</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../lab.css">
    <link rel="stylesheet" href="../account.css">
</head>
<body>
    <div style="padding:0.6rem; text-align:left;">
        <a href="../index.html" class="return-btn">← Powrót do strony głównej</a>
    </div>
    <div id="pageBackground">
        <h1 id="headlineTitle">Rejestracja</h1>
        <form class="form-wrap" action="authenticate.php" method="post" autocomplete="off">
            <fieldset class="fieldset-card">
                <label class="form-label" for="username"><b>Username</b></label>
                <div class="form-group">
                    <input class="form-input" type="text" name="username" placeholder="Username" id="username" required maxlength="20">
                </div>

                <label class="form-label" for="password"><b>Password</b></label>
                <div class="form-group">
                    <div id="message">
                        <h5>Hasło musi zawierać:</h5>
                        <p id="letter" class="invalid"><b>Małą</b> literę</p>
                        <p id="capital" class="invalid"><b>Dużą</b> literę</p>
                        <p id="number" class="invalid"><b>Cyfra</b></p>
                        <p id="length" class="invalid"><b>Przynajmniej 8 znaków</b></p>
                        <p id="specChar" class="invalid"><b>Znak specjalny</b></p>
                    </div>
                    <input class="form-input" type="password" name="password" placeholder="Password" id="password" required 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,255}">
                </div>

                <input type="hidden" name="type" value="register">
                <button class="btn blue" type="submit">Zarejestruj się</button>
            </fieldset>
        </form>
    </div>

    <script>
    var myInput = document.getElementById("password");
    var letter = document.getElementById("letter");
    var capital = document.getElementById("capital");
    var number = document.getElementById("number");
    var length = document.getElementById("length");
    var specChar = document.getElementById("specChar");

    // When the user clicks on the password field, show the message box
    myInput.onfocus = function() {
        document.getElementById("message").style.display = "block";
    }

    // When the user clicks outside of the password field, hide the message box
    myInput.onblur = function() {
        document.getElementById("message").style.display = "none";
    }

    // When the user starts to type something inside the password field
    myInput.onkeyup = function() {
        // Validate lowercase letters
        var lowerCaseLetters = /[a-z]/g;
        if(myInput.value.match(lowerCaseLetters)) {
            letter.classList.remove("invalid");
            letter.classList.add("valid");
        } else {
            letter.classList.remove("valid");
            letter.classList.add("invalid");
        }

        // Validate capital letters
        var upperCaseLetters = /[A-Z]/g;
        if(myInput.value.match(upperCaseLetters)) {
            capital.classList.remove("invalid");
            capital.classList.add("valid");
        } else {
            capital.classList.remove("valid");
            capital.classList.add("invalid");
        }

        // Validate numbers
        var numbers = /[0-9]/g;
        if(myInput.value.match(numbers)) {
            number.classList.remove("invalid");
            number.classList.add("valid");
        } else {
            number.classList.remove("valid");
            number.classList.add("invalid");
        }

        // Validate length
        if(myInput.value.length >= 8) {
            length.classList.remove("invalid");
            length.classList.add("valid");
        } else {
            length.classList.remove("valid");
            length.classList.add("invalid");
        }

        // Validate Special Characters
        var specialCharacter = /[^A-Za-z0-9]/g;
        if(myInput.value.match(specialCharacter)) {
            specChar.classList.remove("invalid");
            specChar.classList.add("valid");
        } else {
            specChar.classList.remove("valid");
            specChar.classList.add("invalid");
        }
    }
    </script>
</body>
</html>