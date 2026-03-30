<?php
// Connect DB
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$database = "kad_kahwin";

$connection = mysqli_connect($servername, $username, $password);
if (!$connection) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Crée la DB si elle n'existe pas
if (!mysqli_fetch_row(mysqli_query($connection, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'"))) {
    mysqli_query($connection, "CREATE DATABASE " . $database);
}

// Nouvelle connexion à la DB
$connection = mysqli_connect($servername, $username, $password, $database);
mysqli_set_charset($connection, 'utf8mb4');

// Création des tables si elles n'existent pas
$query1 = mysqli_query($connection, "SHOW TABLES LIKE 'ucapan_kahwin'");
if (mysqli_num_rows($query1) == 0) {
    $table_ucapan = "CREATE TABLE `ucapan_kahwin` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `nama_tetamu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `ucapan_tetamu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($connection, $table_ucapan);
}

$query2 = mysqli_query($connection, "SHOW TABLES LIKE 'kehadiran'");
if (mysqli_num_rows($query2) == 0) {
    $table_kehadiran = "CREATE TABLE `kehadiran` (
        `id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
        `jumlah_kehadiran` int UNSIGNED NOT NULL,
        `jumlah_tidak_hadir` int UNSIGNED NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($connection, $table_kehadiran);
    $seeder = "INSERT INTO kehadiran (id, jumlah_kehadiran, jumlah_tidak_hadir) VALUES (1, 0, 0)";
    mysqli_query($connection, $seeder);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>John & Sarah - Celebration</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="shortcut icon" type="image/x-icon" href="./images/logo.png" />
    <style>
        /* Texte personnalisé au centre */
        #custom-text {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            font-family: 'Arial', sans-serif;
            color: #ff3366;
            text-align: center;
            z-index: 9999;
            animation: fadeIn 3s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translate(-50%, -60%); }
            100% { opacity: 1; transform: translate(-50%, -50%); }
        }

        /* Animation cœurs */
        .heart {
            position: fixed;
            width: 20px;
            height: 20px;
            background: red;
            transform: rotate(-45deg);
            animation: floatUp 3s linear infinite;
            z-index: 9998;
        }
        .heart::before,
        .heart::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            background: red;
            border-radius: 50%;
        }
        .heart::before { top: -10px; left: 0; }
        .heart::after { left: 10px; top: 0; }

        @keyframes floatUp {
            0% { transform: translateY(0) rotate(-45deg); opacity: 1; }
            100% { transform: translateY(-300px) rotate(-45deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Texte personnalisé -->
    <div id="custom-text">Happy Birthday my love Omar, I wish for you...</div>

    <!-- Musique -->
    <audio id="romantic-audio" loop>
        <source src="./music/romantic.mp3" type="audio/mp3">
    </audio>
    <button id="play-audio" style="position: fixed; top: 10px; left: 10px; z-index:10000;">▶ Play Music</button>

    <!-- Ucapan Section -->
    <section class="ucapan">
        <h2>Ucapan Tetamu</h2>
        <div class="container-message">
            <?php
            $query = mysqli_query($connection, "SELECT * FROM ucapan_kahwin");
            if ($query) {
                $hasData = false;
                while ($row = mysqli_fetch_assoc($query)) {
                    $hasData = true;
                    $name = htmlspecialchars($row['nama_tetamu']);
                    $message = htmlspecialchars($row['ucapan_tetamu']);
                    echo '<div class="content">';
                    echo "<p class='name'>$name</p>";
                    echo "<p class='message'>$message</p>";
                    echo '</div>';
                }
                if (!$hasData) {
                    echo "<p>Tiada ucapan lagi. Silalah beri ucapan kepada dua mempelai ini!</p>";
                }
            } else {
                echo "Error: " . mysqli_error($connection);
            }
            mysqli_close($connection);
            ?>
        </div>
    </section>

    <!-- JS -->
    <!-- Popup Confirmation -->
<div id="popup" class="popup">
    <div class="popup-content">
        <h2>Veux-tu confirmer ta présence ?</h2>
        <div class="buttons">
            <button id="oui-btn">Oui</button>
            <button id="non-btn">Non</button>
        </div>
    </div>
</div>

<style>
/* Popup background */
.popup {
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Popup box */
.popup-content {
    background: #fff;
    padding: 30px 50px;
    border-radius: 12px;
    text-align: center;
    font-family: sans-serif;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

/* Buttons */
.buttons {
    margin-top: 20px;
}

button {
    padding: 15px 35px;
    margin: 10px;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

/* Oui button bigger and highlighted */
#oui-btn {
    background-color: #ff4d6d;
    color: #fff;
    font-weight: bold;
    transform: scale(1.2);
}

/* Non button style */
#non-btn {
    background-color: #aaa;
    color: #fff;
    position: relative;
}

/* Non button escape animation */
</style>

<script>
// Non button se déplace si on essaie de cliquer dessus
const nonBtn = document.getElementById('non-btn');
nonBtn.addEventListener('mouseenter', () => {
    const x = Math.random() * 150 - 75; // se déplace horizontal
    const y = Math.random() * 50 - 25;  // se déplace vertical
    nonBtn.style.transform = `translate(${x}px, ${y}px)`;
});

// Oui button click
const ouiBtn = document.getElementById('oui-btn');
ouiBtn.addEventListener('click', () => {
    if(confirm("Es-tu sûr de vouloir cliquer sur Oui ?")) {
        alert("Merci d'avoir confirmé ta présence ! ❤️");
        document.getElementById('popup').style.display = 'none';
    }
});
</script>
</body>
</html>
