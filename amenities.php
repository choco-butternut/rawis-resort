<?php
require_once "php/config.php";

$sql = "SELECT * FROM amenities ORDER BY amenity_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amenities | Rawis Resort Hotel</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ── Page Header ── */
        .amenities-hero {
            background: url('assets/images/facilities-banner.jpg') center/cover no-repeat;
            position: relative;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }
        .amenities-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.50);
        }
        .amenities-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            border-top: 5px solid #bbcc81;
            border-bottom: 5px solid #bbcc81;
            z-index: 3;
            pointer-events: none;
        }
        .amenities-hero-content {
            position: relative;
            z-index: 4;
        }
        .amenities-hero-content h1 {
            font-family: 'The Seasons', serif;
            font-size: 44px;
            font-weight: 300;
            margin: 0 0 10px;
            letter-spacing: 0.04em;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.5);
        }
        .amenities-hero-content p {
            font-family: Poppins, sans-serif;
            font-size: 16px;
            opacity: 0.88;
            margin: 0 auto;
        }

        /* ── Container ── */
        .amenities-container {
            width: 90%;
            max-width: 1100px;
            margin: 60px auto 80px;
        }

        /* ── Section Label ── */
        .section-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #bbcc81;
            margin-bottom: 8px;
            font-family: Poppins, sans-serif;
        }
        .section-heading {
            font-family: 'The Seasons', serif;
            font-size: 36px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 10px;
        }
        .section-divider {
            width: 52px;
            height: 4px;
            background: linear-gradient(to right, #bbcc81, #334937);
            border-radius: 2px;
            margin-bottom: 40px;
        }

        /* ── Grid ── */
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        /* ── Card ── */
        .amenity-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
        }
        .amenity-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(0,0,0,0.14);
        }
        .amenity-card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .amenity-card-img-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #bbcc81 0%, #334937 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: rgba(255,255,255,0.7);
        }
        .amenity-card-body {
            padding: 22px 24px 26px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .amenity-card-name {
            font-family: 'The Seasons', serif;
            font-size: 22px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 10px;
        }
        .amenity-card-desc {
            font-family: Poppins, sans-serif;
            font-size: 14px;
            color: #555;
            line-height: 1.7;
            margin: 0 0 16px;
            flex: 1;
        }
        .amenity-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid #f0ece6;
        }
        .amenity-price {
            font-family: 'The Seasons', serif;
            font-size: 20px;
            color: #334937;
            font-weight: 400;
        }
        .amenity-price span {
            font-family: Poppins, sans-serif;
            font-size: 12px;
            color: #888;
            font-weight: 400;
        }
        .amenity-status-pill {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 600;
        }
        .amenity-status-pill.available {
            background: #e8f0d8;
            color: #334937;
        }
        .amenity-status-pill.unavailable {
            background: #fde8e8;
            color: #9b2226;
        }

        /* ── Empty state ── */
        .amenities-empty {
            text-align: center;
            padding: 80px 20px;
            color: #888;
            font-family: Poppins, sans-serif;
        }
        .amenities-empty i {
            font-size: 52px;
            color: #bbcc81;
            margin-bottom: 16px;
        }
        .amenities-empty p {
            font-size: 16px;
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="amenities-hero">
        <div class="amenities-hero-content">
            <h1>Our Amenities</h1>
            <p>Everything you need for a perfect stay at Rawis Resort Hotel</p>
        </div>
    </div>

    <div class="amenities-container">

        <p class="section-label">What We Offer</p>
        <h2 class="section-heading">Resort Amenities</h2>
        <div class="section-divider"></div>

        <?php if ($result->num_rows > 0): ?>
            <div class="amenities-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="amenity-card">
                        <?php if (!empty($row['image_path'])): ?>
                            <img class="amenity-card-img"
                                 src="<?= htmlspecialchars($row['image_path']); ?>"
                                 alt="<?= htmlspecialchars($row['amenity_name']); ?>">
                        <?php else: ?>
                            <div class="amenity-card-img-placeholder">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                        <?php endif; ?>

                        <div class="amenity-card-body">
                            <h3 class="amenity-card-name"><?= htmlspecialchars($row['amenity_name']); ?></h3>
                            <p class="amenity-card-desc"><?= htmlspecialchars($row['description']); ?></p>

                            <div class="amenity-card-footer">
                                <div class="amenity-price">
                                    ₱<?= number_format($row['price'], 2); ?>
                                    <span>/add-on</span>
                                </div>
                                <span class="amenity-status-pill <?= strtolower($row['amenity_status']); ?>">
                                    <?= htmlspecialchars($row['amenity_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="amenities-empty">
                <i class="fas fa-concierge-bell"></i>
                <p>No amenities available at the moment.<br>Please check back soon.</p>
            </div>
        <?php endif; ?>

    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>