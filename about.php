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
            background: url('assets/images/facilities-banner.jpg') center/cover no-repeat;
            position: relative;
            height: 340px;
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
            background: rgba(0,0,0,0.52);
        }
        .about-hero-content {
            position: relative;
            z-index: 1;
        }
        .about-hero-content h1 {
            font-size: 44px;
            margin: 0 0 10px;
            letter-spacing: 0.04em;
        }
        .about-hero-content p {
            font-size: 17px;
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

        /* ── Section heading ── */
        .section-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        .section-heading {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 16px;
            line-height: 1.2;
        }
        .section-divider {
            width: 52px;
            height: 4px;
            background: #3b82f6;
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
            color: #475569;
            font-size: 15.5px;
            line-height: 1.8;
            margin-bottom: 18px;
        }
        .about-story-img {
            flex: 1;
            min-width: 280px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        }
        .about-story-img img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }

        /* ── Values cards ── */
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
            border-top: 4px solid #3b82f6;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .value-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }
        .value-icon {
            width: 60px;
            height: 60px;
            background: #eff6ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #3b82f6;
            margin: 0 auto 18px;
        }
        .value-card h3 {
            font-size: 17px;
            color: #1e293b;
            margin: 0 0 10px;
        }
        .value-card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.65;
            margin: 0;
        }

        /* ── Location strip ── */
        .location-strip {
            background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
            border-radius: 18px;
            padding: 44px 48px;
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            gap: 36px;
            align-items: center;
            justify-content: space-between;
        }
        .location-strip h2 {
            font-size: 26px;
            margin: 0 0 8px;
        }
        .location-strip p {
            margin: 0;
            opacity: 0.88;
            font-size: 15px;
            max-width: 400px;
            line-height: 1.6;
        }
        .location-strip a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #1d4ed8;
            font-weight: 700;
            padding: 13px 26px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.2s;
            white-space: nowrap;
        }
        .location-strip a:hover { opacity: 0.88; }

        @media (max-width: 680px) {
            .about-hero-content h1 { font-size: 30px; }
            .location-strip { flex-direction: column; }
        }
    </style>
</head>
<body>
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
        <div style="margin-bottom: 40px; text-align: center;">
            <p class="section-label">What We Stand For</p>
            <h2 class="section-heading" style="margin: 0 auto 8px">Our Core Values</h2>
            <div class="section-divider" style="margin: 0 auto 48px"></div>
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