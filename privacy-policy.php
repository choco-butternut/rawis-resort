<?php require_once "php/config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy | Rawis Resort Hotel</title>
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
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>Privacy Policy</h1>
    </div>

    <div class="policy-container">

        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>

        <section class="policy-intro">
            <h1>Your Privacy Matters</h1>
            <h2>Privacy Policy</h2>
            <p>Last updated: January 1, 2026</p>
        </section>

        <div class="policy-section">
            <h3><i class="fas fa-info-circle"></i> Overview</h3>
            <p>Rawis Resort Hotel ("we," "us," or "our") is committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or make a reservation with us. Please read this policy carefully.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-database"></i> Information We Collect</h3>
            <p>We may collect the following types of personal information:</p>
            <ul>
                <li><strong>Personal Identification:</strong> First name, last name, and phone number provided during the reservation process.</li>
                <li><strong>Reservation Details:</strong> Check-in and check-out dates, room preferences, extra guest or bed requests, and special requests.</li>
                <li><strong>Payment Information:</strong> Payment method, GCash reference numbers, and partial card details (last 4 digits only). We do not store full card numbers.</li>
                <li><strong>Communication Data:</strong> Messages or inquiries you send to us through our contact form.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-cogs"></i> How We Use Your Information</h3>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process and manage your reservations.</li>
                <li>Verify payment and confirm bookings.</li>
                <li>Contact you regarding your reservation status or changes.</li>
                <li>Respond to inquiries submitted through our contact form.</li>
                <li>Improve our services and website experience.</li>
                <li>Comply with legal obligations.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-share-alt"></i> Sharing of Information</h3>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
            <ul>
                <li>With our staff and management for the purpose of fulfilling your reservation.</li>
                <li>With payment processors (e.g., GCash) solely to verify transactions.</li>
                <li>When required by law or in response to valid legal processes.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-shield-alt"></i> Data Security</h3>
            <p>We implement reasonable administrative and technical measures to protect your personal information from unauthorized access, disclosure, or destruction. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-user-check"></i> Your Rights</h3>
            <p>You have the right to:</p>
            <ul>
                <li>Request access to the personal information we hold about you.</li>
                <li>Request correction of inaccurate or incomplete data.</li>
                <li>Request deletion of your personal data, subject to legal requirements.</li>
                <li>Withdraw consent for data processing where applicable.</li>
            </ul>
            <p>To exercise any of these rights, please contact us at <strong>rawisresorthotel@gmail.com</strong> or call <strong>0977 183 7288</strong>.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-cookie-bite"></i> Cookies</h3>
            <p>Our website may use session cookies to maintain your login state and improve your browsing experience. These cookies are temporary and are deleted when you close your browser. We do not use tracking or advertising cookies.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-edit"></i> Changes to This Policy</h3>
            <p>We reserve the right to update this Privacy Policy at any time. Changes will be posted on this page with an updated date. Continued use of our services after any changes constitutes your acceptance of the new policy.</p>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-envelope"></i> Contact Us</h3>
            <p>If you have any questions about this Privacy Policy, please reach out to us:</p>
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