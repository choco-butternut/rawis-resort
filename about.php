<?php require_once "php/config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Rawis Resort Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="customer-page about-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>About Us</h1>
    </div>

    <div class="about-container">
        <section class="about-intro">
            <div class="intro-content">
                <h1>About</h1>
                <h2>Rawis Resort Hotel</h2>
                <p class="lead-text">
                    Rawis Resort Hotel is a welcoming getaway located in Borongan City, Eastern Samar, known as the “City of the Golden Sunrise”. It offers guests a relaxed and comfortable place to stay whether they’re visiting for a vacation, a weekend escape, or just passing through the area. 
                </p>
            </div>
        </section>

        <div class="values-wrapper">
            <div class="about-intro">
                <div class="intro-content">
                <h1>What we stand for</h1>
                <h2>Our Core Values</h2>
            </div>
            </div>
            
            <div class="values-grid">
                <div class="value-item">
                    <i class="fas fa-heart"></i>
                    <h3>Warm Hospitality</h3>
                    <p>We treat every guest like family—attentive and always ready to help.</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-leaf"></i>
                    <h3>Nature First</h3>
                    <p>Designed to honor the stunning natural landscape of the Samar coast.</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safety & Comfort</h3>
                    <p>Clean, secure, and well-maintained facilities for a worry-free stay.</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-users"></i>
                    <h3>Community</h3>
                    <p>Proudly local, supporting Borongan City through sustainable tourism.</p>
                </div>
            </div>
        </div>

        <!-- <div class="location-banner">
            <div class="location-info">
                <h2><i class="fas fa-map-marker-alt"></i> Find Us</h2>
                <p>Rawis Detour Road, Brgy. Alang-alang, Borongan City</p>
            </div>
            <a href="https://maps.google.com" target="_blank" class="room-finder-btn">
                Open in Google Maps
            </a>
        </div> -->
    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>