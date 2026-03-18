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


<body class="home-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="hero">
        <div class="hero-content">

            <?php
                $showImage = false;
                $showText  = true;
                include __DIR__ . '/php/logo.php';
            ?>

            <div class="tagline">
                <p>Located in Borongan City, Eastern Samar, 
                    Rawis Resort Hotel welcomes guests to a place where the sun greets the shore, 
                    creating a serene space for rest and relaxation.
                </p>
            </div>

            <div class="card-stack">                

                <div class="card-item">
                    <div class="card-image" style="background-image: url('path/to/rooms.jpg');">
                        <div class="card-overlay">
                            <h3>Rooms</h3>
                            <a href="#" class="view-details">View Details</a>
                        </div>
                    </div>
                </div>
                

                <div class="card-item">
                    <div class="card-image" style="background-image: url('path/to/amenities.jpg');">
                        <div class="card-overlay">
                            <h3>Amenities</h3>
                            <a href="#" class="view-details">View Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cta">
                <button id="findRoomId" type="button">BOOK NOW</button>
            </div>

        </div>
    </div>



    <?php require_once __DIR__ . '/php/footer.php'; ?> 
    
    <script>
        document.getElementById("findRoomId").addEventListener("click",function(){
            window.location.href="rooms.php"
        })

    </script>
</body>
</html>
