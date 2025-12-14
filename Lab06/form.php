<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../lab.css">
    <style>
        h2{
            margin: 0.2rem 0 0.5rem 0;
        }
    </style>
</head>
<body>
    <div style="padding:0.6rem; text-align:left;">
        <a href="../index.html" class="return-btn">← Powrót do strony głównej</a>
    </div>
    <div id="pageBackground">
        <h1 id="headlineTitle">Wynik formularza</h1>
        <?php
            if(empty($_POST)){
                echo"Formularz jest pusty";
            }
            else{
                echo"<div class='collumn'>";
                echo"<div class='cell'><h2>Dane personalne</h2><br>";
                $name = $_POST["firstName"] . " " . $_POST["lastName"];
                echo"<div class='row'><strong>Godność:</strong> $name</div><br>";
                $sex = $_POST["sex"];
                if($sex == "M"){
                    $sex = "y";
                    echo"<div class='row'><strong>Płeć:</strong> Mężczyzna</div><br>";
                }
                else if($sex == "K"){
                    $sex = "a";
                    echo"<div class='row'><strong>Płeć:</strong> Kobieta</div><br>";
                }
                $bdate = $_POST["birthDate"];
                if($bdate != ""){
                    echo"<div class='row'><strong>Urodzon$sex:</strong> $bdate</div><br>";
                }
                echo "</div><br>";
                echo"<div class='cell'><h2>Adres</h2><br>";
                $adress = $_POST["street"] . " " . $_POST["streetNumber"];
                if($_POST["flatNumber"] != null){
                    $adress .= " m. " . $_POST["flatNumber"];
                }
                $adress .= ", " . $_POST["countryCode"] . ",<br>" . $_POST["city"] . ", " . $_POST["region"];
                echo "<div class='row'>$adress</div>";
                echo "</div><br>";
                echo "<div class='cell'><h2>Dane kontaktowe</h2><br>";
                $email = $_POST["email"];
                echo "<div class='row'><strong>E-mail:</strong> $email</div><br>";
                $phone = $_POST["telephoneNumber"];
                $phone = substr(chunk_split($phone, 3, '-'), 0, -1);
                echo "<div class='row'><strong>Numer telefonu:</strong> $phone</div><br>";
                echo "</div><br>";
                echo "<div class='cell'><h2>Dane dodatkowe</h2><br>";
                $dl = $_POST["drivingLicence"];
                if(isset($_POST["drivingLicence"])){
                    $dl = "posiada";
                }
                else{
                    $dl = "brak";
                }
                echo "<div class='row'><strong>Prawo jazdy:</strong> $dl</div>";
                $remark = $_POST["remark"];
                if($remark == "")
                    $remark = "brak";
                echo "<div class='row'><strong>Uwagi:</strong> $remark</div>";
            }
        ?>
    </div>
</body>
</html>
