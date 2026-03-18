<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once "php/config.php";

$success = false;
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = sanitize_input($_POST["name"] ?? "");
    // $email   = sanitize_input($_POST["email"] ?? "");
    $subject = sanitize_input($_POST["subject"] ?? "");
    $message = sanitize_input($_POST["message"] ?? "");

    if (empty($name) || empty($message)) {
        $error = "Please fill in all required fields.";
    }else {
        require_once __DIR__ . '/vendor/autoload.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($_ENV['MAIL_USERNAME'], 'Rawis Resort Hotel');
            $mail->addAddress($_ENV['MAIL_USERNAME'], 'Gabriel');
            $mail->addReplyTo($_ENV['MAIL_USERNAME'], $name);

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
        

        /* ── Layout ── */
        .contact-wrapper {
            width: 90%;
            max-width: 1050px;
            margin: 60px auto 60px;
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 40px;
            align-items: start;
        }

        /* ── Section label ── */
        .section-label {
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #bbcc81;
            margin-bottom: 6px;
        }
        .section-heading {
            font-family: 'The Seasons', serif;
            font-size: 30px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 10px;
        }
        .section-divider {
            width: 52px;
            height: 4px;
            background: linear-gradient(to right, #bbcc81, #334937);
            border-radius: 2px;
            margin-bottom: 24px;
        }

        /* ── Info panel ── */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .contact-info-sub {
            font-family: Poppins, sans-serif;
            font-size: 14px;
            color: #666;
            line-height: 1.7;
            margin: 0 0 8px;
        }

        .info-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            display: flex;
            align-items: flex-start;
            gap: 14px;
            border-left: 4px solid #bbcc81;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.11);
        }
        .info-card-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #bbcc81 0%, #334937 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }
        .info-card-body .label {
            font-family: Poppins, sans-serif;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #888;
            margin-bottom: 3px;
        }
        .info-card-body .value {
            font-family: Poppins, sans-serif;
            font-size: 14.5px;
            font-weight: 600;
            color: #341f0c;
        }
        .info-card-body .value a {
            color: #341f0c;
            text-decoration: none;
            transition: color 0.2s;
        }
        .info-card-body .value a:hover { color: #334937; }
        .info-card-body .sub-value {
            font-family: Poppins, sans-serif;
            font-size: 12.5px;
            color: #888;
            margin-top: 2px;
        }

        /* ── Form card ── */
        .contact-form-card {
            background: #fff;
            border-radius: 18px;
            padding: 36px 38px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.09);
        }
        .contact-form-card h2 {
            font-family: 'The Seasons', serif;
            font-size: 24px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .contact-form-card h2 i {
            color: #bbcc81;
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
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #531e07;
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
            font-family: Poppins, sans-serif;
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
            border-color: #bbcc81;
            box-shadow: 0 0 0 3px rgba(187, 204, 129, 0.2);
            background: #fff;
        }
        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23531e07' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-color: #faf8f6;
            padding-right: 36px;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .req { color: #c0392b; }

        .btn-send {
            width: 100%;
            padding: 13px;
            background: linear-gradient(to right, #bbcc81 10%, #334937 80%);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            box-shadow: 0 4px 14px rgba(51,73,55,0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 6px;
        }
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(51,73,55,0.35);
        }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }
        .alert-success {
            background: #f0f7e6;
            color: #2d5a27;
            border-color: #bbcc81;
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
            color: #334937;
            border: 2px solid #bbcc81;
            border-radius: 50px;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .btn-resend:hover {
            background: #334937;
            color: #fff;
        }

        /* ── Map ── */
        .contact-map-wrap {
            width: 90%;
            max-width: 1050px;
            margin: 0 auto 80px;
        }
        .contact-map-wrap h3 {
            font-family: 'The Seasons', serif;
            font-size: 22px;
            font-weight: 400;
            color: #341f0c;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .contact-map-wrap h3 i { color: #bbcc81; }
        .contact-map {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(0,0,0,0.1);
            border: 3px solid #bbcc81;
        }
        .contact-map iframe {
            width: 100%;
            height: 300px;
            border: none;
            display: block;
        }

        @media (max-width: 780px) {
            .contact-wrapper { grid-template-columns: 1fr; }
            .contact-form-card { padding: 26px 20px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>Contact Us</h1>
    </div>

    <div class="contact-wrapper">

        <!-- Left: Info -->
        <div class="contact-info">
            <div>
                <p class="section-label">Reach Out</p>
                <h2 class="section-heading">Get in Touch</h2>
                <div class="section-divider"></div>
                <p class="contact-info-sub">
                    Have a question about reservations, facilities, or your upcoming stay?
                    Our team is ready to help you.
                </p>
            </div>

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
                            <option value="Reservation Inquiry"   <?= (($_POST['subject'] ?? '') === 'Reservation Inquiry')   ? 'selected' : ''; ?>>Reservation Inquiry</option>
                            <option value="Facilities & Amenities"<?= (($_POST['subject'] ?? '') === 'Facilities & Amenities')? 'selected' : ''; ?>>Facilities & Amenities</option>
                            <option value="Payment & Billing"     <?= (($_POST['subject'] ?? '') === 'Payment & Billing')     ? 'selected' : ''; ?>>Payment & Billing</option>
                            <option value="Events & Functions"    <?= (($_POST['subject'] ?? '') === 'Events & Functions')    ? 'selected' : ''; ?>>Events & Functions</option>
                            <option value="General Inquiry"       <?= (($_POST['subject'] ?? '') === 'General Inquiry')       ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="Other"                 <?= (($_POST['subject'] ?? '') === 'Other')                 ? 'selected' : ''; ?>>Other</option>
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

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>