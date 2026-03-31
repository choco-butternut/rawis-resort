<?php require_once "php/config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms &amp; Conditions | Rawis Resort Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .policy-container {
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 30px 80px;
        }
        .policy-intro {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            margin-bottom: 48px;
        }
        .policy-intro h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #5d330f;
            margin: 0 0 -12px;
        }
        .policy-intro h2 {
            font-family: 'The Seasons', serif;
            font-size: 30px;
            background: linear-gradient(to right, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1.8;
        }
        .policy-intro p {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #7c746b;
            margin: 4px auto 0;
        }
        .policy-section {
            margin-bottom: 36px;
        }
        .policy-section h3 {
            font-family: 'The Seasons', serif;
            font-size: 22px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .policy-section h3 i {
            background: linear-gradient(135deg, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 18px;
        }
        .policy-section p,
        .policy-section li {
            font-family: 'Poppins', sans-serif;
            font-size: 14.5px;
            color: #555;
            line-height: 1.8;
        }
        .policy-section ul {
            padding-left: 20px;
            margin: 8px 0;
        }
        .policy-section ul li {
            margin-bottom: 6px;
        }
        .policy-divider {
            border: none;
            border-top: 1px solid #ede8e1;
            margin: 36px 0;
        }
        .policy-updated {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #aaa;
            text-align: center;
            margin-top: 48px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #5d330f;
            text-decoration: none;
            margin-bottom: 28px;
            transition: opacity 0.2s;
        }
        .back-link:hover { opacity: 0.7; }
        .highlight-box {
            background: linear-gradient(135deg, rgba(219,181,149,0.12), rgba(93,51,15,0.05));
            border-left: 4px solid #dbb595;
            border-radius: 0 10px 10px 0;
            padding: 14px 18px;
            margin: 14px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #5d330f;
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>Terms &amp; Conditions</h1>
    </div>

    <div class="policy-container">

        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>

        <section class="policy-intro">
            <h1>Please Read Carefully</h1>
            <h2>Terms &amp; Conditions</h2>
            <p>Last updated: January 1, 2026</p>
        </section>

        <div class="policy-section">
            <h3><i class="fas fa-handshake"></i> Acceptance of Terms</h3>
            <p>By accessing our website, making a reservation, or using any of our services, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please refrain from using our services.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-calendar-check"></i> Reservations</h3>
            <ul>
                <li>All reservations are subject to room availability at the time of booking.</li>
                <li>A reservation is considered confirmed only after payment has been verified by our staff.</li>
                <li>Rawis Resort Hotel reserves the right to cancel unverified reservations after a reasonable waiting period.</li>
                <li>Guests are responsible for ensuring that all reservation details (dates, room type, contact information) are accurate at the time of booking.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-sign-in-alt"></i> Check-in &amp; Check-out</h3>
            <ul>
                <li><strong>Check-in time:</strong> 2:00 PM onwards.</li>
                <li><strong>Check-out time:</strong> 12:00 PM (noon).</li>
                <li>Early check-in or late check-out may be accommodated subject to availability and may incur additional charges.</li>
                <li>Guests must present valid government-issued identification upon check-in.</li>
                <li>For Cash reservations, full payment is required upon check-in.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-times-circle"></i> Cancellation Policy</h3>
            <div class="highlight-box">
                <i class="fas fa-exclamation-circle"></i> Please review our cancellation policy carefully before booking.
            </div>
            <ul>
                <li>Cancellations made <strong>48 hours or more</strong> before check-in may be eligible for a full refund or rebooking at the management's discretion.</li>
                <li>Cancellations made <strong>less than 48 hours</strong> before check-in may forfeit any advance payment made.</li>
                <li>No-show reservations will be treated as cancelled without refund.</li>
                <li>Refunds, where applicable, will be processed within 5–10 business days via the original payment method.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-credit-card"></i> Payment</h3>
            <ul>
                <li>We accept Cash, GCash, and Card payments.</li>
                <li>For GCash and Card payments, guests must submit a valid reference number for verification.</li>
                <li>All prices are in Philippine Peso (PHP) and are inclusive of applicable fees unless otherwise stated.</li>
                <li>Rawis Resort Hotel reserves the right to adjust prices without prior notice, but confirmed reservations will honor the price at the time of booking.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-users"></i> Guest Conduct</h3>
            <ul>
                <li>Guests are expected to behave respectfully toward staff and other guests at all times.</li>
                <li>Any damage to property caused by guests will be billed accordingly.</li>
                <li>The hotel management reserves the right to remove guests who violate house rules without refund.</li>
                <li>Quiet hours are observed from 10:00 PM to 7:00 AM.</li>
                <li>Smoking is not permitted inside guest rooms.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-swimming-pool"></i> Amenities &amp; Facilities</h3>
            <ul>
                <li>Use of shared facilities such as the swimming pool is subject to posted rules and operating hours.</li>
                <li>The hotel is not liable for injuries sustained from improper use of facilities.</li>
                <li>Amenities listed are subject to availability and may change without prior notice.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-gavel"></i> Limitation of Liability</h3>
            <p>Rawis Resort Hotel shall not be held liable for any loss, damage, theft, or injury to guests or their belongings during their stay, except where such liability cannot be excluded by law. Guests are encouraged to secure their valuables.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-edit"></i> Changes to Terms</h3>
            <p>We reserve the right to modify these Terms and Conditions at any time. The updated version will be posted on this page. Continued use of our services following any changes constitutes acceptance of the revised terms.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-envelope"></i> Contact</h3>
            <p>For questions or concerns regarding these Terms and Conditions, please contact us at:</p>
            <ul>
                <li><strong>Email:</strong> rawisresorthotel@gmail.com</li>
                <li><strong>Phone:</strong> 0977 183 7288</li>
                <li><strong>Address:</strong> Rawis Detour Road, Brgy. Alang-alang, Borongan City, Eastern Samar 6800</li>
            </ul>
        </div>

        <p class="policy-updated">© 2026 Rawis Resort Hotel. All rights reserved.</p>
    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>