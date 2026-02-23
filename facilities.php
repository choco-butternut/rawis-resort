<?php
require_once "php/config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facilities | Rawis Resort</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">

    <style>

        .hero {
            background: url('assets/images/facilities-banner.jpg') center/cover no-repeat;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            background-color: rgba(0,0,0,0.5);
        }

        .hero h1 {
            font-size: 40px;
            background: rgba(0,0,0,0.5);
            padding: 15px 30px;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 50px auto;
        }

        .facility {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 50px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .facility img {
            width: 100%;
            max-width: 450px;
            object-fit: cover;
        }

        .facility-content {
            flex: 1;
            padding: 30px;
        }

        .facility-content h2 {
            margin-top: 0;
            color: #2c3e50;
        }

        .facility-content p {
            line-height: 1.6;
            color: #555;
        }

        @media (max-width: 768px) {
            .facility {
                flex-direction: column;
            }

            .facility img {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Kathlyn gin ai kolain, bag oha nala kun ano it aadto rawis -->

    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="container">

        <!-- Swimming Pool -->
        <div class="facility">
            <img src="assets/images/pool.jpg" alt="Swimming Pool">
            <div class="facility-content">
                <h2>Swimming Pool Area</h2>
                <p>
                    Enjoy our spacious swimming pool perfect for relaxation and family bonding. 
                    The pool area includes a separate kiddie pool, poolside lounge chairs, 
                    and a clean shower area for your convenience.
                </p>
            </div>
        </div>

        <!-- Function Hall -->
        <div class="facility">
            <img src="assets/images/function-hall.jpg" alt="Function Hall">
            <div class="facility-content">
                <h2>Function Hall & Event Area</h2>
                <p>
                    Our function hall is ideal for weddings, birthdays, reunions, and corporate events. 
                    The spacious layout can accommodate large gatherings comfortably, 
                    making every celebration memorable.
                </p>
            </div>
        </div>

        <!-- Dining Area -->
        <div class="facility">
            <img src="assets/images/dining.jpg" alt="Dining Area">
            <div class="facility-content">
                <h2>Dining Area</h2>
                <p>
                    Guests can enjoy meals in our comfortable dining area. 
                    Whether you're having a casual meal or celebrating a special occasion, 
                    the space offers a relaxing ambiance for everyone.
                </p>
            </div>
        </div>

        <!-- Garden & Cottages -->
        <div class="facility">
            <img src="assets/images/garden.jpg" alt="Garden Area">
            <div class="facility-content">
                <h2>Garden & Open Cottages</h2>
                <p>
                    Surrounded by beautifully maintained greenery, our garden and open cottages 
                    provide a peaceful space for relaxation, bonding, and outdoor activities.
                </p>
            </div>
        </div>

        <!-- Parking -->
        <div class="facility">
            <img src="assets/images/parking.jpg" alt="Parking Area">
            <div class="facility-content">
                <h2>Secure Parking Area</h2>
                <p>
                    Rawis Resort offers a spacious and secure parking area for guests, 
                    ensuring convenience and peace of mind throughout your stay.
                </p>
            </div>
        </div>

    </div>
    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>