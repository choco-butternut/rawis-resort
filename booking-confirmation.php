<?php
require_once __DIR__ . '/php/config.php';

if (!isset($_GET["r"])) {
    header("Location: /rooms.php");
    exit();
}

$reservation_id = (int) $_GET["r"];

$stmt = $conn->prepare("
    SELECT r.*,
           u.first_name, u.last_name, u.phone_number,
           rm.room_number, rm.room_type, rm.price_per_night, rm.image_path,
           p.payment_id, p.payment_method, p.payment_status, p.reference_number, p.amount_paid
    FROM reservations r
    JOIN users u  ON r.guest_id = u.id
    JOIN rooms rm ON r.room_id  = rm.room_id
    LEFT JOIN payments p ON p.reservation_id = r.reservation_id
    WHERE r.reservation_id = ?
    ORDER BY p.payment_id DESC
    LIMIT 1
");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: /rooms.php");
    exit();
}

$nights    = (new DateTime($data["check_in_date"]))->diff(new DateTime($data["check_out_date"]))->days;
$room_cost = $data["price_per_night"] * $nights;
$total     = $room_cost;

$amStmt = $conn->prepare("
    SELECT ra.quantity, a.amenity_name
    FROM reservation_amenities ra
    JOIN amenities a ON ra.amenity_id = a.amenity_id
    WHERE ra.reservation_id = ?
");
$amStmt->bind_param("i", $reservation_id);
$amStmt->execute();
$amenities_result = $amStmt->get_result();
$amenities_list   = [];
while ($row = $amenities_result->fetch_assoc()) {
    $amenities_list[] = $row;
}
$amStmt->close();

$success_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_reference"])) {
    $ref = sanitize_input($_POST["reference_number"]);
    $upd = $conn->prepare(
        "UPDATE payments SET reference_number=?, payment_status='Awaiting Verification'
         WHERE reservation_id=? AND payment_status='Pending'"
    );
    $upd->bind_param("si", $ref, $reservation_id);
    $upd->execute();
    $upd->close();
    header("Location: /booking-confirmation.php?r={$reservation_id}&updated=1");
    exit();
}

if (isset($_GET["updated"])) {
    $success_msg = "Reference number submitted. We'll verify your payment shortly.";
    $refetch = $conn->prepare("SELECT payment_status, reference_number FROM payments WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1");
    $refetch->bind_param("i", $reservation_id);
    $refetch->execute();
    $updated = $refetch->get_result()->fetch_assoc();
    $refetch->close();
    if ($updated) {
        $data["payment_status"]   = $updated["payment_status"];
        $data["reference_number"] = $updated["reference_number"];
    }
}

function reservationBadge($s) {
    $map = [
        'Pending'     => ['#8e4a0f', '#fff8f0'],
        'Confirmed'   => ['#334937', '#f0f7e6'],
        'Cancelled'   => ['#9b2226', '#fdf0ee'],
        'Completed'   => ['#531e07', '#fdf6f0'],
        'Checked-in'  => ['#2d5a27', '#e8f0d8'],
        'Checked-out' => ['#531e07', '#fdf6f0'],
    ];
    $d = $map[$s] ?? ['#666', '#f5f5f5'];
    return "<span class='status-badge' style='background:{$d[1]};color:{$d[0]};border-color:{$d[0]}'>"
         . htmlspecialchars($s) . "</span>";
}

function paymentBadge($s) {
    $map = [
        'Pending'               => ['#8e4a0f', '#fff8f0'],
        'Awaiting Verification' => ['#334937', '#f0f7e6'],
        'Completed'             => ['#2d5a27', '#e8f0d8'],
        'Refunded'              => ['#9b2226', '#fdf0ee'],
    ];
    $d = $map[$s] ?? ['#666', '#f5f5f5'];
    return "<span class='status-badge' style='background:{$d[1]};color:{$d[0]};border-color:{$d[0]}'>"
         . htmlspecialchars($s) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Rawis Resort Hotel</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .confirm-page-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 20px 60px;
        }

        .confirm-intro {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            margin-bottom: 36px;
        }

        .confirm-intro h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #5d330f;
            margin: 0 0 -12px;
        }

        .confirm-intro h2 {
            font-family: 'The Seasons', serif;
            font-size: 30px;
            background: linear-gradient(to right, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1.8;
        }

        .confirm-intro p {
            font-family: 'Poppins', sans-serif;
            font-size: 14.5px;
            font-weight: 300;
            color: #7c746b;
            max-width: 540px;
            margin: 4px auto 0;
            line-height: 1.7;
        }

        .booking-id-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 8px 22px;
            background: linear-gradient(to right, #5d330f, #dbb595);
            color: #fff;
            border-radius: 50px;
            font-family: 'The Seasons', serif;
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 0.04em;
            box-shadow: 0 4px 14px rgba(93, 51, 15, 0.22);
        }

        .booking-id-pill span {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.85;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(219,181,149,0.15), rgba(93,51,15,0.06));
            color: #5d330f;
            border-color: #dbb595;
        }

        .confirm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .confirm-grid .full-width {
            grid-column: 1 / -1;
        }

        .card {
            background: #fff;
            border-radius: 15px;
            padding: 26px 28px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2ddd8;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.09);
        }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #7c746b;
            margin: 0 0 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            background: linear-gradient(135deg, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 13px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 9px 0;
            border-bottom: 1px solid #f5f0eb;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            gap: 16px;
        }

        .info-row:last-child { border-bottom: none; }

        .info-row .label { color: #7c746b; flex-shrink: 0; }

        .info-row .value {
            font-weight: 600;
            color: #341f0c;
            text-align: right;
        }

        .room-inline {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }

        .room-inline img {
            width: 100px;
            height: 72px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #e2ddd8;
        }

        .room-inline-meta { flex: 1; }

        .room-inline-type {
            font-family: 'The Seasons', serif;
            font-size: 22px;
            font-weight: 400;
            color: #341f0c;
            line-height: 1.2;
        }

        .room-inline-sub {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #7c746b;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .room-inline-sub i { color: #dbb595; font-size: 12px; }

        .date-strip {
            display: flex;
            align-items: center;
            gap: 0;
            margin-top: 4px;
        }

        .date-box {
            flex: 1;
            background: #faf8f5;
            border-radius: 10px;
            padding: 14px 18px;
            text-align: center;
            border: 1px solid #ede8e1;
        }

        .date-label {
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #7c746b;
            font-weight: 700;
        }

        .date-value {
            font-family: 'The Seasons', serif;
            font-size: 18px;
            color: #341f0c;
            margin-top: 4px;
        }

        .date-sub {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            color: #aaa;
        }

        .date-arrow { padding: 0 14px; color: #dbb595; font-size: 16px; }

        .nights-pill {
            padding: 8px 20px;
            background: linear-gradient(135deg, #5d330f, #dbb595);
            color: #fff;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            box-shadow: 0 4px 12px rgba(93,51,15,0.2);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid;
        }

        .cost-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #555;
            border-bottom: 1px dashed #ede8e1;
        }

        .cost-row:last-child { border-bottom: none; }

        .cost-row.total-row {
            border-top: 2px solid #ede8e1;
            border-bottom: none;
            padding-top: 14px;
            margin-top: 4px;
        }

        .cost-row.total-row .cost-label {
            font-size: 15px;
            font-weight: 700;
            color: #341f0c;
        }

        .cost-row.total-row .cost-value {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(to right, #5d330f, #dbb595);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .amenity-included {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #555;
            padding: 7px 0;
            border-bottom: 1px dashed #ede8e1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .amenity-included:last-of-type { border-bottom: none; }
        .amenity-included i { color: #dbb595; font-size: 11px; flex-shrink: 0; }
        .amenity-included .amenity-qty { color: #aaa; margin-left: 4px; }

        .amenities-subhead {
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #aaa;
            margin: 4px 0 8px;
        }

        .pm-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 50px;
        }

        .pm-cash  { background: #f0f7e6; color: #334937; }
        .pm-gcash { background: #eff6ff; color: #1d4ed8; }
        .pm-card  { background: #fdf6f0; color: #8e4a0f; }

        .instructions-box {
            background: #faf8f5;
            border-radius: 12px;
            padding: 18px 20px;
            margin-top: 18px;
            border: 1px solid #ede8e1;
        }

        .step {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            color: #555;
            margin-bottom: 14px;
        }

        .step:last-child { margin-bottom: 0; }

        .step-num {
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, #5d330f, #dbb595);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .ref-form {
            margin-top: 16px;
            padding: 18px 20px;
            background: #fff8f0;
            border: 1px dashed #dbb595;
            border-radius: 12px;
        }

        .ref-form p {
            font-family: 'Poppins', sans-serif;
            margin: 0 0 12px;
            font-size: 13px;
            color: #8e4a0f;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ref-form-row { display: flex; gap: 10px; }

        .ref-form input {
            flex: 1;
            padding: 10px 14px;
            border: 1.5px solid #e2ddd8;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #341f0c;
            background: #faf8f6;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .ref-form input:focus {
            border-color: #dbb595;
            box-shadow: 0 0 0 3px rgba(219, 181, 149, 0.2);
            background: #fff;
        }

        .ref-form button {
            padding: 10px 22px;
            background: linear-gradient(to right, #5d330f, #dbb595);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        .ref-form button:hover { opacity: 0.88; transform: translateY(-1px); }

        .confirm-actions {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            background: #fff;
            color: #5d330f;
            border: 2px solid #e2ddd8;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: border-color 0.2s, background 0.15s;
        }

        .btn-secondary:hover { border-color: #dbb595; background: #faf8f5; }

        @media (max-width: 700px) {
            .confirm-grid { grid-template-columns: 1fr; }
            .date-strip { flex-direction: column; align-items: stretch; }
            .date-arrow { transform: rotate(90deg); text-align: center; padding: 6px 0; }
            .nights-pill { text-align: center; }
            .room-inline { flex-direction: column; align-items: flex-start; }
            .room-inline img { width: 100%; height: 160px; }
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="page-header">
        <h1>Booking Confirmation</h1>
    </div>

    <div class="confirm-page-inner">

        <section class="confirm-intro">
            <h1>Thank You</h1>
            <h2>Booking Received, <?= htmlspecialchars($data["first_name"]); ?></h2>
            <p>Your reservation is being processed. Please review your details below and follow the payment instructions.</p>
            <div class="booking-id-pill">
                <span>Booking ID</span>
                #<?= str_pad($reservation_id, 5, '0', STR_PAD_LEFT); ?>
            </div>
        </section>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <div class="confirm-grid">

            <!-- Room + Dates -->
            <div class="card full-width">
                <p class="card-title"><i class="fas fa-bed"></i> Room Details</p>

                <div class="room-inline">
                    <?php if ($data["image_path"]): ?>
                        <img src="<?= htmlspecialchars($data["image_path"]); ?>" alt="Room">
                    <?php endif; ?>
                    <div class="room-inline-meta">
                        <div class="room-inline-type"><?= htmlspecialchars($data["room_type"]); ?></div>
                        <div class="room-inline-sub">
                            <span><i class="fas fa-door-open"></i> Room <?= htmlspecialchars($data["room_number"]); ?></span>
                            <span><i class="fas fa-moon"></i> <?= $nights; ?> night<?= $nights > 1 ? 's' : ''; ?></span>
                        </div>
                    </div>
                    <div>
                        <?= reservationBadge($data["reservation_status"]); ?>
                    </div>
                </div>

                <div class="date-strip">
                    <div class="date-box">
                        <div class="date-label">Check-in</div>
                        <div class="date-value"><?= date("M d, Y", strtotime($data["check_in_date"])); ?></div>
                        <div class="date-sub"><?= date("l", strtotime($data["check_in_date"])); ?></div>
                    </div>
                    <div class="date-arrow"><i class="fas fa-arrow-right"></i></div>
                    <div class="date-box">
                        <div class="date-label">Check-out</div>
                        <div class="date-value"><?= date("M d, Y", strtotime($data["check_out_date"])); ?></div>
                        <div class="date-sub"><?= date("l", strtotime($data["check_out_date"])); ?></div>
                    </div>
                    <div class="date-arrow"></div>
                    <div class="nights-pill"><?= $nights; ?> Night<?= $nights > 1 ? 's' : ''; ?></div>
                </div>
            </div>

            <!-- Guest Info -->
            <div class="card">
                <p class="card-title"><i class="fas fa-user"></i> Guest Information</p>
                <div class="info-row">
                    <span class="label">Name</span>
                    <span class="value"><?= htmlspecialchars($data["first_name"] . " " . $data["last_name"]); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Phone</span>
                    <span class="value"><?= htmlspecialchars($data["phone_number"]); ?></span>
                </div>
                <?php if (!empty($data["extra_requests"])): ?>
                <div class="info-row">
                    <span class="label">Special Requests</span>
                    <span class="value" style="max-width:60%"><?= htmlspecialchars($data["extra_requests"]); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Cost Breakdown -->
            <div class="card">
                <p class="card-title"><i class="fas fa-receipt"></i> Cost Breakdown</p>
                <div class="cost-row">
                    <span><?= htmlspecialchars($data["room_type"]); ?> &times; <?= $nights; ?> night<?= $nights > 1 ? 's' : ''; ?></span>
                    <span>&#8369;<?= number_format($room_cost, 2); ?></span>
                </div>
                <?php if (!empty($amenities_list)): ?>
                <div class="cost-row" style="flex-direction:column; gap:4px;">
                    <div class="amenities-subhead">Included Amenities</div>
                    <?php foreach ($amenities_list as $a): ?>
                        <div class="amenity-included">
                            <i class="fas fa-check"></i>
                            <span><?= htmlspecialchars($a["amenity_name"]); ?></span>
                            <?php if ($a["quantity"] > 1): ?>
                                <span class="amenity-qty">&times;<?= $a["quantity"]; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="cost-row total-row">
                    <span class="cost-label">Total</span>
                    <span class="cost-value">&#8369;<?= number_format($total, 2); ?></span>
                </div>
            </div>

            <!-- Payment -->
            <div class="card full-width">
                <p class="card-title"><i class="fas fa-credit-card"></i> Payment</p>

                <div class="info-row">
                    <span class="label">Method</span>
                    <span class="value">
                        <?php
                        $pm      = $data["payment_method"];
                        $pmClass = match($pm) {
                            "Cash"  => "pm-cash",
                            "GCash" => "pm-gcash",
                            "Card"  => "pm-card",
                            default => "pm-cash"
                        };
                        $pmIcon  = match($pm) {
                            "Cash"  => "fas fa-money-bill-wave",
                            "GCash" => "fas fa-mobile-alt",
                            "Card"  => "fas fa-credit-card",
                            default => "fas fa-money-bill-wave"
                        };
                        ?>
                        <span class="pm-pill <?= $pmClass; ?>">
                            <i class="<?= $pmIcon; ?>"></i> <?= htmlspecialchars($pm); ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Amount Due</span>
                    <span class="value" style="font-size:17px; background:linear-gradient(to right,#5d330f,#dbb595); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">
                        &#8369;<?= number_format($total, 2); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Payment Status</span>
                    <span class="value"><?= paymentBadge($data["payment_status"]); ?></span>
                </div>
                <?php if (!empty($data["reference_number"])): ?>
                <div class="info-row">
                    <span class="label">Reference #</span>
                    <span class="value"><?= htmlspecialchars($data["reference_number"]); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($data["payment_method"] === "Cash"): ?>
                    <div class="instructions-box">
                        <div class="step">
                            <div class="step-num">1</div>
                            <div>Your reservation is <strong>pending</strong>. No payment is needed right now.</div>
                        </div>
                        <div class="step">
                            <div class="step-num">2</div>
                            <div>Present <strong>Booking ID #<?= str_pad($reservation_id, 5, '0', STR_PAD_LEFT); ?></strong> at the front desk on check-in.</div>
                        </div>
                        <div class="step">
                            <div class="step-num">3</div>
                            <div>Pay <strong>&#8369;<?= number_format($total, 2); ?></strong> in cash upon arrival. Your reservation will be confirmed by our staff.</div>
                        </div>
                    </div>

                <?php elseif ($data["payment_method"] === "GCash"): ?>
                    <div class="instructions-box">
                        <div class="step">
                            <div class="step-num">1</div>
                            <div>Send <strong>&#8369;<?= number_format($total, 2); ?></strong> via GCash to <strong>0977 183 7288</strong> (Rawis Resort Hotel).</div>
                        </div>
                        <div class="step">
                            <div class="step-num">2</div>
                            <div>Enter your GCash transaction reference number below.</div>
                        </div>
                        <div class="step">
                            <div class="step-num">3</div>
                            <div>Our team will verify your payment and confirm your reservation within a few hours.</div>
                        </div>
                    </div>
                    <?php if ($data["payment_status"] === "Pending"): ?>
                        <div class="ref-form">
                            <p><i class="fas fa-exclamation-triangle"></i> Please submit your GCash reference number so we can verify your payment.</p>
                            <form method="POST">
                                <input type="hidden" name="submit_reference" value="1">
                                <div class="ref-form-row">
                                    <input type="text" name="reference_number" placeholder="e.g. 2024031512345678" required>
                                    <button type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                <?php elseif ($data["payment_method"] === "Card"): ?>
                    <div class="instructions-box">
                        <div class="step">
                            <div class="step-num">1</div>
                            <div>Your card payment reference number has been recorded.</div>
                        </div>
                        <div class="step">
                            <div class="step-num">2</div>
                            <div>Our team will verify the transaction and confirm your reservation within a few hours.</div>
                        </div>
                        <div class="step">
                            <div class="step-num">3</div>
                            <div>You'll receive a confirmation once verified. Contact us if you have any questions.</div>
                        </div>
                    </div>
                    <?php if ($data["payment_status"] === "Pending"): ?>
                        <div class="ref-form">
                            <p><i class="fas fa-exclamation-triangle"></i> Please submit your card transaction reference number.</p>
                            <form method="POST">
                                <input type="hidden" name="submit_reference" value="1">
                                <div class="ref-form-row">
                                    <input type="text" name="reference_number" placeholder="Transaction / approval code" required>
                                    <button type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>

        <div class="confirm-actions">
            <a href="/rooms.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Browse More Rooms
            </a>
            <a href="/index.php" class="btn-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <button onclick="window.print()" class="btn-secondary">
                <i class="fas fa-print"></i> Print Confirmation
            </button>
        </div>

    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>
</body>
</html>