<?php
require_once __DIR__ . '/php/config.php';

$rooms = $conn->query("SELECT * FROM rooms WHERE room_status='available'");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rawis Resort Hotel</title>
    <link rel="stylesheet" href="assets/css/base.css">
</head>


<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="hero">
        <div class="hero-content">

            <?php
                $showImage = false;
                $showText  = true;
                include __DIR__ . '/php/logo.php';
            ?>

            <div class="tagline">
                <p>Where the sun rises early, one and only spot in Borongan, Eastern Samar.
                    LOREM IPSUM DHDSKFHKAJDFHKAJDHKJAH!!!!???
                </p>
            </div>

            <div class="cta">
                <button type="button">FIND ME A ROOM</button>
            </div>

        </div>
    </div>

    <div class="room-cards">
        

    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?> 
    

</body>
</html>
