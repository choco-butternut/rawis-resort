<?php
require_once __DIR__ . '/php/config.php';

$rooms = $conn->query("SELECT * FROM rooms WHERE room_status='available' ORDER BY price_per_night ASC LIMIT 3");
$rooms_arr = [];
while ($r = $rooms->fetch_assoc()) $rooms_arr[] = $r;

$amenities = $conn->query("SELECT * FROM amenities WHERE amenity_status='Available' ORDER BY amenity_name ASC LIMIT 3");
$amenities_arr = [];
while ($a = $amenities->fetch_assoc()) $amenities_arr[] = $a;
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

                <!-- Rooms card group -->
                <div class="card-item">
                    <?php foreach ($rooms_arr as $i => $room): ?>
                    <div class="card-image"
                         style="background-image: url('<?= htmlspecialchars($room['image_path']); ?>');
                                background-size: cover;
                                background-position: center;
                                position: absolute;
                                inset: 0;
                                border-radius: inherit;
                                transform: rotate(<?= ($i - 1) * 5; ?>deg);
                                z-index: <?= count($rooms_arr) - $i; ?>;">
                    </div>
                    <?php endforeach; ?>
                    <div class="card-image" style="position: relative; z-index: 10; background-image: url('<?= htmlspecialchars($rooms_arr[0]['image_path'] ?? ''); ?>'); background-size: cover; background-position: center;">
                        <div class="card-overlay">
                            <h3>Rooms</h3>
                            <a href="rooms.php" class="view-details">View Details</a>
                        </div>
                    </div>
                </div>

                <!-- Amenities card group -->
                <div class="card-item">
                    <?php foreach ($amenities_arr as $i => $amenity): ?>
                    <div class="card-image"
                         style="background-image: url('<?= !empty($amenity['image_path']) ? htmlspecialchars($amenity['image_path']) : 'assets/rawis-bg.jpg'; ?>');
                                background-size: cover;
                                background-position: center;
                                position: absolute;
                                inset: 0;
                                border-radius: inherit;
                                transform: rotate(<?= ($i - 1) * 5; ?>deg);
                                z-index: <?= count($amenities_arr) - $i; ?>;">
                    </div>
                    <?php endforeach; ?>
                    <div class="card-image" style="position: relative; z-index: 10; background-image: url('<?= !empty($amenities_arr[0]['image_path']) ? htmlspecialchars($amenities_arr[0]['image_path']) : 'assets/rawis-bg.jpg'; ?>'); background-size: cover; background-position: center;">
                        <div class="card-overlay">
                            <h3>Amenities</h3>
                            <a href="amenities.php" class="view-details">View Details</a>
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
        document.getElementById("findRoomId").addEventListener("click", function(){
            window.location.href = "rooms.php";
        });
    </script>
</body>
</html>