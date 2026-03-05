<?php
require_once "php/config.php";

$success = false;
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = sanitize_input($_POST["name"] ?? "");
    $email   = sanitize_input($_POST["email"] ?? "");
    $subject = sanitize_input($_POST["subject"] ?? "");
    $message = sanitize_input($_POST["message"] ?? "");

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // You can add mail() or a DB insert here later.
        // For now we just flag success so the guest gets feedback.
        $success = true;
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
        /* ── Hero ── */
        .contact-hero {
            background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
            height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }
        .contact-hero h1 {
            font-size: 42px;
            margin: 0 0 10px;
        }
        .contact-hero p {
            font-size: 16px;
            opacity: 0.88;
        }

        /* ── Layout ── */
        .contact-wrapper {
            width: 90%;
            max-width: 1050px;
            margin: 60px auto 80px;
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 40px;
            align-items: start;
        }

        /* ── Info panel ── */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .contact-info-heading {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 6px;
        }
        .contact-info-sub {
            font-size: 14.5px;
            color: #64748b;
            line-height: 1.7;
            margin: 0 0 20px;
        }
        .info-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: 0 3px 16px rgba(0,0,0,0.07);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: transform 0.18s;
        }
        .info-card:hover { transform: translateY(-3px); }
        .info-card-icon {
            width: 46px;
            height: 46px;
            background: #eff6ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #3b82f6;
            flex-shrink: 0;
        }
        .info-card-body .label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .info-card-body .value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        .info-card-body .value a {
            color: #1e293b;
            text-decoration: none;
        }
        .info-card-body .value a:hover { color: #3b82f6; }
        .info-card-body .sub-value {
            font-size: 13px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Form card ── */
        .contact-form-card {
            background: #fff;
            border-radius: 18px;
            padding: 38px 40px;
            box-shadow: 0 6px 30px rgba(0,0,0,0.09);
        }
        .contact-form-card h2 {
            font-size: 22px;
            color: #1e293b;
            margin: 0 0 24px;
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
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14.5px;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
            background: #fff;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }

        .req { color: #ef4444; }

        .btn-send {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: opacity 0.2s, transform 0.15s;
            margin-top: 6px;
        }
        .btn-send:hover { opacity: 0.9; transform: translateY(-1px); }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ── Map embed ── */
        .contact-map {
            margin-top: 50px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(0,0,0,0.1);
        }
        .contact-map iframe {
            width: 100%;
            height: 300px;
            border: none;
            display: block;
        }

        @media (max-width: 780px) {
            .contact-wrapper { grid-template-columns: 1fr; }
            .contact-form-card { padding: 28px 22px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="contact-hero">
        <div>
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Reach out anytime!</p>
        </div>
    </div>

    <div class="contact-wrapper">

        <!-- Left: Info -->
        <div class="contact-info">
            <div>
                <h2 class="contact-info-heading">Get in Touch</h2>
                <p class="contact-info-sub">
                    Have a question about reservations, facilities, or your upcoming stay? 
                    Our team is ready to help.
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
            <h2><i class="fas fa-paper-plane" style="color:#3b82f6;margin-right:10px"></i>Send Us a Message</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Thank you! Your message has been sent. We'll be in touch soon.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Your Name <span class="req">*</span></label>
                        <input type="text" name="name" placeholder="Juan dela Cruz"
                               value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email" placeholder="you@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject">
                        <option value="">Select a topic…</option>
                        <option value="Reservation Inquiry" <?= (($_POST['subject'] ?? '') === 'Reservation Inquiry') ? 'selected' : ''; ?>>Reservation Inquiry</option>
                        <option value="Facilities & Amenities" <?= (($_POST['subject'] ?? '') === 'Facilities & Amenities') ? 'selected' : ''; ?>>Facilities & Amenities</option>
                        <option value="Payment & Billing" <?= (($_POST['subject'] ?? '') === 'Payment & Billing') ? 'selected' : ''; ?>>Payment & Billing</option>
                        <option value="Events & Functions" <?= (($_POST['subject'] ?? '') === 'Events & Functions') ? 'selected' : ''; ?>>Events & Functions</option>
                        <option value="General Inquiry" <?= (($_POST['subject'] ?? '') === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                        <option value="Other" <?= (($_POST['subject'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
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
            <?php else: ?>
                <a href="contact.php" style="display:inline-block;margin-top:8px;padding:10px 22px;background:#eff6ff;color:#1d4ed8;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px">
                    <i class="fas fa-redo"></i> Send Another Message
                </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- Map -->
    <div style="width:90%;max-width:1050px;margin:0 auto 80px">
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