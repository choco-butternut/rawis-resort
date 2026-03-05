<?php
require_once "php/config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Rawis Resort Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ── Hero ── */
        .about-hero {
            position: relative;
            background: url('assets/images/facilities-banner.jpg') center/cover no-repeat;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }
        .about-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.52);
        }
        .about-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            border-top: 5px solid #bbcc81;
            border-bottom: 5px solid #bbcc81;
            z-index: 3;
            pointer-events: none;
        }
        .about-hero-content {
            position: relative;
            z-index: 4;
        }
        .about-hero-content h1 {
            font-family: 'The Seasons', serif;
            font-size: 44px;
            font-weight: 300;
            margin: 0 0 10px;
            letter-spacing: 0.04em;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
        }
        .about-hero-content p {
            font-family: Poppins, sans-serif;
            font-size: 16px;
            opacity: 0.88;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ── Container ── */
        .about-container {
            width: 90%;
            max-width: 1050px;
            margin: 60px auto 80px;
        }

        /* ── Section label shared ── */
        .section-label {
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #bbcc81;
            margin-bottom: 8px;
        }
        .section-heading {
            font-family: 'The Seasons', serif;
            font-size: 36px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 12px;
            line-height: 1.2;
        }
        .section-divider {
            width: 52px;
            height: 4px;
            background: linear-gradient(to right, #bbcc81, #334937);
            border-radius: 2px;
            margin-bottom: 28px;
        }

        /* ── Our Story ── */
        .about-story {
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
            align-items: center;
            margin-bottom: 80px;
        }
        .about-story-text {
            flex: 1;
            min-width: 280px;
        }
        .about-story-text p {
            font-family: Poppins, sans-serif;
            color: #555;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 18px;
        }
        .about-story-img {
            flex: 1;
            min-width: 280px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
            border: 4px solid #bbcc81;
        }
        .about-story-img img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }

        /* ── Values ── */
        .values-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .values-header .section-label,
        .values-header .section-heading {
            display: block;
        }
        .values-header .section-divider {
            margin: 0 auto 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-bottom: 80px;
        }
        .value-card {
            background: #fff;
            border-radius: 14px;
            padding: 32px 26px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
            text-align: center;
            border-top: 4px solid #bbcc81;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .value-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }
        .value-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #bbcc81 0%, #334937 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            margin: 0 auto 18px;
        }
        .value-card h3 {
            font-family: 'The Seasons', serif;
            font-size: 18px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 10px;
        }
        .value-card p {
            font-family: Poppins, sans-serif;
            font-size: 13.5px;
            color: #666;
            line-height: 1.65;
            margin: 0;
        }

        /* ── Location CTA ── */
        .location-strip {
            background: linear-gradient(to right, #bbcc81 10%, #334937 80%);
            border-radius: 18px;
            padding: 44px 48px;
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 28px rgba(51,73,55,0.3);
        }
        .location-strip h2 {
            font-family: 'The Seasons', serif;
            font-size: 26px;
            font-weight: 400;
            margin: 0 0 8px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.25);
        }
        .location-strip p {
            font-family: Poppins, sans-serif;
            margin: 0;
            opacity: 0.9;
            font-size: 14.5px;
            max-width: 400px;
            line-height: 1.6;
        }
        .location-strip a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #334937;
            font-family: Poppins, sans-serif;
            font-weight: 700;
            padding: 13px 26px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .location-strip a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        @media (max-width: 680px) {
            .about-hero-content h1 { font-size: 30px; }
            .location-strip { flex-direction: column; padding: 30px 24px; }
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="about-hero">
        <div class="about-hero-content">
            <h1>About Rawis Resort Hotel</h1>
            <p>A beachside retreat in the heart of Eastern Samar, built for rest and real connection.</p>
        </div>
    </div>

    <div class="about-container">

        <!-- Our Story -->
        <div class="about-story">
            <div class="about-story-text">
                <p class="section-label">Our Story</p>
                <h2 class="section-heading">Where Every Stay<br>Feels Like Home</h2>
                <div class="section-divider"></div>
                <p>
                    Nestled along the shores of Borongan City in Eastern Samar, Rawis Resort Hotel was built
                    on a simple idea: that every guest deserves a place to truly unwind. Surrounded by the
                    natural beauty of the Philippine coast, we offer a peaceful retreat away from the rush
                    of everyday life.
                </p>
                <p>
                    Since opening, we have welcomed families, couples, and solo travelers seeking both
                    comfort and a genuine taste of Eastern Samar's warm hospitality. Every room, every
                    facility, and every interaction is shaped by our commitment to making you feel welcome.
                </p>
            </div>
            <div class="about-story-img">
                <img src="assets/images/pool.jpg" alt="Rawis Resort">
            </div>
        </div>

        <!-- Values -->
        <div class="values-header">
            <p class="section-label">What We Stand For</p>
            <h2 class="section-heading">Our Core Values</h2>
            <div class="section-divider"></div>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-heart"></i></div>
                <h3>Warm Hospitality</h3>
                <p>We treat every guest like family — attentive, genuine, and always ready to help.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-leaf"></i></div>
                <h3>Nature First</h3>
                <p>Our resort is designed to harmonize with the natural beauty of Eastern Samar's coastline.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Safety & Comfort</h3>
                <p>Clean, well-maintained facilities and secure premises so you can relax without worry.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-users"></i></div>
                <h3>Community</h3>
                <p>Proudly local. We support the community of Borongan City through sustainable tourism.</p>
            </div>
        </div>

        <!-- Location CTA -->
        <div class="location-strip">
            <div>
                <h2><i class="fas fa-map-marker-alt"></i> Find Us in Borongan City</h2>
                <p>Rawis Detour Road, Brgy. Alang-alang, Borongan City, Eastern Samar, Philippines 6800</p>
            </div>
            <a href="https://maps.google.com/?q=Rawis+Resort+Hotel" target="_blank">
                <i class="fas fa-location-arrow"></i> Open in Google Maps
            </a>
        </div>

    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>