<?php require_once "php/config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Safety &amp; Security | Rawis Resort Hotel</title>
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
        .safety-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .safety-card {
            background: #fff;
            border: 1px solid #ede8e1;
            border-radius: 14px;
            padding: 22px 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.04);
            transition: transform 0.25s;
        }
        .safety-card:hover {
            transform: translateY(-4px);
        }
        .safety-card i {
            font-size: 28px;
            background: linear-gradient(135deg, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
            display: block;
        }
        .safety-card h4 {
            font-family: 'The Seasons', serif;
            font-size: 18px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 8px;
        }
        .safety-card p {
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            color: #7c746b;
            margin: 0;
            line-height: 1.7;
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>Safety &amp; Security</h1>
    </div>

    <div class="policy-container">

        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>

        <section class="policy-intro">
            <h1>Your Safety Is Our Priority</h1>
            <h2>Safety &amp; Security</h2>
            <p>We are committed to providing a safe and secure environment for all our guests.</p>
        </section>

        <!-- Quick overview cards -->
        <div class="safety-grid">
            <div class="safety-card">
                <i class="fas fa-shield-alt"></i>
                <h4>24-Hour Security</h4>
                <p>Our premises are monitored around the clock by dedicated security personnel to ensure guest safety at all times.</p>
            </div>
            <div class="safety-card">
                <i class="fas fa-video"></i>
                <h4>CCTV Surveillance</h4>
                <p>Property-wide CCTV cameras cover all common areas including entrances, hallways, parking areas, and the pool.</p>
            </div>
            <div class="safety-card">
                <i class="fas fa-fire-extinguisher"></i>
                <h4>Fire Safety</h4>
                <p>Fully automatic fire alarm systems and fire extinguishers are installed throughout the resort for rapid response.</p>
            </div>
            <div class="safety-card">
                <i class="fas fa-bolt"></i>
                <h4>Power Backup</h4>
                <p>A high-powered standby generator ensures uninterrupted power supply for your comfort and safety during outages.</p>
            </div>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-user-shield"></i> Guest Safety Measures</h3>
            <ul>
                <li>All guests are required to present valid government-issued identification upon check-in.</li>
                <li>Room keys are issued exclusively to registered guests.</li>
                <li>Unauthorized visitors are not permitted in guest room areas past designated hours.</li>
                <li>Our staff are trained to respond to emergencies including medical situations, fire, and weather events.</li>
                <li>Emergency contact numbers are posted in all guest rooms.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-swimming-pool"></i> Pool &amp; Facility Safety</h3>
            <ul>
                <li>The swimming pool is supervised during operating hours. Swimming is not permitted when staff are not present.</li>
                <li>Children must be accompanied by an adult at the pool at all times.</li>
                <li>Running, diving, and roughhousing near the pool area are strictly prohibited.</li>
                <li>Guests with medical conditions that may be affected by pool activities are advised to consult their physician before swimming.</li>
                <li>All facilities are regularly inspected and maintained for guest safety.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-lock"></i> Valuables &amp; Personal Belongings</h3>
            <ul>
                <li>Guests are advised to keep personal valuables secure at all times.</li>
                <li>Rawis Resort Hotel is not responsible for lost or stolen items left unattended in common areas.</li>
                <li>Please report any suspicious activity or lost items to the front desk immediately.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-lock"></i> Online Booking Security</h3>
            <ul>
                <li>Our website uses HTTPS encryption to protect data transmitted during the booking process.</li>
                <li>We do not store full payment card numbers. Only the last 4 digits are recorded for reference purposes.</li>
                <li>GCash and Card transactions are processed through their respective platforms and are not directly handled by us.</li>
                <li>We will never ask for your full card details, CVV, or banking passwords via email, text, or phone call.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-exclamation-triangle"></i> Emergency Procedures</h3>
            <p>In the event of an emergency during your stay:</p>
            <ul>
                <li>Contact the front desk immediately by calling the in-room phone or visiting the reception area.</li>
                <li>Follow the instructions of hotel staff during any emergency evacuation.</li>
                <li>Emergency exits are clearly marked throughout the property.</li>
                <li>First aid assistance is available through our staff. For serious medical emergencies, local emergency services will be contacted.</li>
            </ul>
        </div>

        <hr class="policy-divider">

        <div class="policy-section">
            <h3><i class="fas fa-envelope"></i> Report a Concern</h3>
            <p>If you experience or witness any safety or security concern during your stay, please report it to us immediately:</p>
            <ul>
                <li><strong>Front Desk:</strong> Available at the reception area.</li>
                <li><strong>Phone:</strong> 0977 183 7288</li>
                <li><strong>Email:</strong> rawisresorthotel@gmail.com</li>
            </ul>
            <p>Your safety is our top priority, and we take all reports seriously.</p>
        </div>

        <p class="policy-updated">© 2026 Rawis Resort Hotel. All rights reserved.</p>
    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>