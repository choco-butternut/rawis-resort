<?php
require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

require_once "php/config.php";

$success = false;
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = sanitize_input($_POST["name"] ?? "");
    $subject = sanitize_input($_POST["subject"] ?? "");
    $message = sanitize_input($_POST["message"] ?? "");

    if (empty($name) || empty($message)) {
        $error = "Please fill in all required fields.";
    } else {

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'gabrielmontes155@gmail.com';
            $mail->Password   = 'wihuzwpsuivpxtll';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('gabrielmontes155@gmail.com', 'Rawis Resort Hotel');
            $mail->addAddress('gabrielmontes155@gmail.com', 'Gabriel');
            $mail->addReplyTo('gabrielmontes155@gmail.com', $name);

            $mail->isHTML(true);
            $mail->Subject = $subject ? "Contact Form: $subject" : "New Contact Form Message";
            $mail->Body    = "
                <h3>New message from Rawis Resort Hotel contact form</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
            ";
            $mail->AltBody = "Name: $name\nSubject: $subject\nMessage: $message";

            $mail->send();
            $success = true;
        } catch (Exception $e) {
            $error = "Message could not be sent. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us | Rawis Resort Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>

        /* ── Page wrapper ── */
        .contact-page-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ── Section heading (mirrors about.php intro-content) ── */
        .contact-intro {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            margin-bottom: 36px;
        }

        .contact-intro h1 {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: #5d330f;
            margin-bottom: -15px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .contact-intro h2 {
            font-family: 'The Seasons', serif;
            line-height: 1.8;
            background: linear-gradient(to right, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            font-size: 30px;
        }

        .contact-intro p {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 300;
            color: #7c746b;
            max-width: 540px;
            margin: 10px auto 0;
            line-height: 1.7;
        }

        /* ── Two-column layout ── */
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 40px;
            align-items: start;
            margin-bottom: 60px;
        }

        /* ── Info cards (mirrors value-item style) ── */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .info-card {
            background: #fff;
            border-radius: 15px;
            padding: 22px 24px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            border: 1px solid #575454;
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #5d330f, #dbb595);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .info-card-body .label {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #7c746b;
            margin-bottom: 3px;
        }

        .info-card-body .value {
            font-family: 'Poppins', sans-serif;
            font-size: 14.5px;
            font-weight: 600;
            color: #341f0c;
        }

        .info-card-body .value a {
            color: #341f0c;
            text-decoration: none;
            transition: color 0.2s;
        }

        .info-card-body .value a:hover {
            color: #5d330f;
        }

        .info-card-body .sub-value {
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            color: #7c746b;
            margin-top: 2px;
        }

        /* ── Form card (mirrors value-item card) ── */
        .contact-form-card {
            background: #fff;
            border-radius: 15px;
            padding: 36px 38px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border: 1px solid #575454;
        }

        .contact-form-card h2 {
            font-family: 'The Seasons', serif;
            font-size: 26px;
            font-weight: 400;
            background: linear-gradient(to right, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0 0 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* icon inside gradient heading needs its own color */
        .contact-form-card h2 i {
            background: linear-gradient(135deg, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #5d330f;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e2ddd8;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #341f0c;
            background: #faf8f6;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #dbb595;
            box-shadow: 0 0 0 3px rgba(219, 181, 149, 0.2);
            background: #fff;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%235d330f' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-color: #faf8f6;
            padding-right: 36px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .req { color: #c0392b; }

        /* ── Send button (mirrors room-finder button) ── */
        .btn-send {
            width: 100%;
            padding: 13px;
            background: linear-gradient(to right, #5d330f, #dbb595);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            box-shadow: 0 4px 14px rgba(93, 51, 15, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 6px;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(93, 51, 15, 0.3);
        }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(219,181,149,0.15), rgba(93,51,15,0.08));
            color: #5d330f;
            border-color: #dbb595;
        }

        .alert-error {
            background: #fdf0ee;
            color: #8b2020;
            border-color: #c0392b;
        }

        .btn-resend {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 10px 22px;
            background: transparent;
            color: #5d330f;
            border: 2px solid #dbb595;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .btn-resend:hover {
            background: linear-gradient(to right, #5d330f, #dbb595);
            color: #fff;
            border-color: transparent;
        }

        /* ── Map section ── */
        .contact-map-wrap {
            margin-bottom: 60px;
        }

        .contact-map-wrap h3 {
            font-family: 'The Seasons', serif;
            font-size: 22px;
            font-weight: 400;
            background: linear-gradient(to right, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-map-wrap h3 i {
            background: linear-gradient(135deg, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .contact-map {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border: 1px solid #575454;
        }

        .contact-map iframe {
            width: 100%;
            height: 300px;
            border: none;
            display: block;
        }

        @media (max-width: 780px) {
            .contact-wrapper  { grid-template-columns: 1fr; }
            .contact-form-card { padding: 26px 20px; }
            .form-row          { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>Contact Us</h1>
    </div>

    <div class="contact-page-inner">

        <!-- Section heading — mirrors about.php intro -->
        <section class="contact-intro">
            <h1>Reach Out</h1>
            <h2>Get in Touch</h2>
            <p>Have a question about reservations, facilities, or your upcoming stay?
               Our team is ready to help you.</p>
        </section>

        <div class="contact-wrapper">

            <!-- Left: Info cards -->
            <div class="contact-info">

                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-phone"></i></div>
                    <div class="info-card-body">
                        <div class="label">Phone</div>
                        <div class="value"><a href="tel:09771837288">0977 183 7288</a></div>
                        <div class="sub-value">Available during business hours</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-card-body">
                        <div class="label">Email</div>
                        <div class="value"><a href="mailto:rawisresorthotel@gmail.com">rawisresorthotel@gmail.com</a></div>
                        <div class="sub-value">We'll reply within 24 hours</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-icon"><i class="fab fa-facebook-messenger"></i></div>
                    <div class="info-card-body">
                        <div class="label">Facebook Messenger</div>
                        <div class="value">Rawis Resort Hotel</div>
                        <div class="sub-value">Message us directly on Facebook</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-card-body">
                        <div class="label">Address</div>
                        <div class="value">Rawis Detour Road, Brgy. Alang-alang</div>
                        <div class="sub-value">Borongan City, Eastern Samar 6800</div>
                    </div>
                </div>

            </div>

            <!-- Right: Form -->
            <div class="contact-form-card">
                <h2><i class="fas fa-paper-plane"></i> Send Us a Message</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Thank you! Your message has been sent. We'll be in touch soon.
                    </div>
                    <a href="contacts.php" class="btn-resend">
                        <i class="fas fa-redo"></i> Send Another Message
                    </a>

                <?php else: ?>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Your Name <span class="req">*</span></label>
                                <input type="text" name="name" placeholder="Juan dela Cruz"
                                       value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject</label>
                            <select name="subject">
                                <option value="">Select a topic…</option>
                                <option value="Reservation Inquiry"    <?= (($_POST['subject'] ?? '') === 'Reservation Inquiry')    ? 'selected' : ''; ?>>Reservation Inquiry</option>
                                <option value="Facilities & Amenities" <?= (($_POST['subject'] ?? '') === 'Facilities & Amenities') ? 'selected' : ''; ?>>Facilities & Amenities</option>
                                <option value="Payment & Billing"      <?= (($_POST['subject'] ?? '') === 'Payment & Billing')      ? 'selected' : ''; ?>>Payment & Billing</option>
                                <option value="Events & Functions"     <?= (($_POST['subject'] ?? '') === 'Events & Functions')     ? 'selected' : ''; ?>>Events & Functions</option>
                                <option value="General Inquiry"        <?= (($_POST['subject'] ?? '') === 'General Inquiry')        ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Other"                  <?= (($_POST['subject'] ?? '') === 'Other')                  ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Message <span class="req">*</span></label>
                            <textarea name="message" placeholder="Write your message here…" required><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-send">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>

                <?php endif; ?>
            </div>

        </div>

        <!-- Map -->
        <div class="contact-map-wrap">
            <h3><i class="fas fa-map-marker-alt"></i> Find Us</h3>
            <div class="contact-map">
                <iframe
                    src="https://www.google.com/maps?q=Rawis+Resort+Hotel,+Borongan+City,+Eastern+Samar&output=embed"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>