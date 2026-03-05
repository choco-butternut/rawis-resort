<?php
require_once __DIR__ . '/php/config.php';

if (!isset($_GET["r"])) {
    header("Location: /rooms.php");
    exit();
}

$reservation_id = (int) $_GET["r"];
// Simple token check: base64-encoded email must match guest's email
$token_email = base64_decode(urldecode($_GET["t"] ?? ""));

$stmt = $conn->prepare("
    SELECT r.*,
           u.first_name, u.last_name, u.email, u.phone_number,
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

if (!$data || strtolower($data["email"]) !== strtolower($token_email)) {
    http_response_code(403);
    die("Access denied.");
}

$nights = (new DateTime($data["check_in_date"]))->diff(new DateTime($data["check_out_date"]))->days;
$room_cost = $data["price_per_night"] * $nights;

// Amenities breakdown
$amStmt = $conn->prepare("
    SELECT ra.quantity, ra.price, a.amenity_name
    FROM reservation_amenities ra
    JOIN amenities a ON ra.amenity_id = a.amenity_id
    WHERE ra.reservation_id = ?
");
$amStmt->bind_param("i", $reservation_id);
$amStmt->execute();
$amenities_result = $amStmt->get_result();
$amenities_list = [];
$amenities_total = 0;
while ($row = $amenities_result->fetch_assoc()) {
    $sub = $row["price"] * $row["quantity"];
    $amenities_total += $sub;
    $amenities_list[] = $row + ["subtotal" => $sub];
}
$amStmt->close();

$total = $room_cost + $amenities_total;

// Handle reference number submission (for GCash/Card without ref at booking time)
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
    $success_msg = "Reference number submitted! We'll verify your payment shortly.";
    // Refresh data
    header("Location: /booking-confirmation.php?r={$reservation_id}&t=" . urlencode(base64_encode($data["email"])) . "&updated=1");
    exit();
}
if (isset($_GET["updated"])) {
    $success_msg = "Reference number submitted! We'll verify your payment shortly.";
    // Re-fetch updated payment status
    $refetch = $conn->prepare("SELECT payment_status, reference_number FROM payments WHERE reservation_id=? ORDER BY payment_id DESC LIMIT 1");
    $refetch->bind_param("i", $reservation_id);
    $refetch->execute();
    $updated = $refetch->get_result()->fetch_assoc();
    $refetch->close();
    if ($updated) {
        $data["payment_status"]    = $updated["payment_status"];
        $data["reference_number"]  = $updated["reference_number"];
    }
}

// Status helpers
function reservationBadge($s) {
    $map = [
        'Pending'   => ['#f59e0b','#fffbeb','⏳'],
        'Confirmed' => ['#10b981','#ecfdf5','✅'],
        'Cancelled' => ['#ef4444','#fef2f2','✗'],
        'Completed' => ['#6366f1','#eef2ff','★'],
        'Checked-in'  => ['#3b82f6','#eff6ff','🔑'],
        'Checked-out' => ['#8b5cf6','#f5f3ff','👋'],
    ];
    $d = $map[$s] ?? ['#6b7280','#f9fafb','•'];
    return "<span class='status-badge' style='background:{$d[1]};color:{$d[0]};border-color:{$d[0]}'>{$d[2]} {$s}</span>";
}
function paymentBadge($s) {
    $map = [
        'Pending'                => ['#f59e0b','#fffbeb'],
        'Awaiting Verification'  => ['#3b82f6','#eff6ff'],
        'Completed'              => ['#10b981','#ecfdf5'],
        'Refunded'               => ['#ef4444','#fef2f2'],
    ];
    $d = $map[$s] ?? ['#6b7280','#f9fafb'];
    return "<span class='status-badge' style='background:{$d[1]};color:{$d[0]};border-color:{$d[0]}'>{$s}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Rawis Resort</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; }

        body { background: #f1f5f9; }

        .confirm-page {
            max-width: 900px;
            margin: 40px auto 80px;
            padding: 0 16px;
        }

        /* ── Hero banner ── */
        .confirm-hero {
            background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
            border-radius: 20px;
            padding: 36px 40px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }
        .confirm-hero::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 220px; height: 220px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .confirm-hero-icon {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; flex-shrink: 0;
        }
        .confirm-hero h1 { margin: 0 0 6px; font-size: 26px; font-weight: 800; }
        .confirm-hero p  { margin: 0; opacity: 0.85; font-size: 15px; }
        .confirm-booking-id {
            margin-left: auto;
            text-align: right;
            flex-shrink: 0;
        }
        .confirm-booking-id .id-label { font-size: 12px; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.08em; }
        .confirm-booking-id .id-value { font-size: 28px; font-weight: 900; letter-spacing: 0.04em; }

        /* ── Alert ── */
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex; align-items: center; gap: 10px;
        }

        /* ── Grid layout ── */
        .confirm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .confirm-grid .full-width { grid-column: 1 / -1; }

        /* ── Cards ── */
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .card-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin: 0 0 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .card-title i { color: #3b82f6; }

        /* ── Info rows ── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { color: #64748b; }
        .info-row .value { font-weight: 600; color: #0f172a; text-align: right; }

        /* ── Room card ── */
        .room-card-inline {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .room-card-inline img {
            width: 100px; height: 70px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .room-card-inline .room-type { font-size: 17px; font-weight: 700; color: #0f172a; }
        .room-card-inline .room-meta { font-size: 13px; color: #64748b; margin-top: 4px; }

        /* ── Date strip ── */
        .date-strip {
            display: flex;
            align-items: center;
            gap: 0;
            margin: 16px 0;
        }
        .date-box {
            flex: 1;
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px 18px;
            text-align: center;
        }
        .date-box .date-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; font-weight: 600; }
        .date-box .date-value { font-size: 17px; font-weight: 700; color: #0f172a; margin-top: 4px; }
        .date-box .date-sub   { font-size: 12px; color: #64748b; }
        .date-arrow {
            padding: 0 12px;
            color: #cbd5e1;
            font-size: 20px;
        }
        .nights-pill {
            padding: 6px 16px;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid;
        }

        /* ── Cost breakdown ── */
        .cost-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #475569;
            border-bottom: 1px dashed #e2e8f0;
        }
        .cost-row:last-child { border-bottom: none; }
        .cost-row.total-row {
            border-top: 2px solid #e2e8f0;
            border-bottom: none;
            padding-top: 12px;
            margin-top: 4px;
        }
        .cost-row.total-row span:first-child { font-size: 15px; font-weight: 700; color: #0f172a; }
        .cost-row.total-row span:last-child  { font-size: 20px; font-weight: 800; color: #1d4ed8; }

        /* ── Payment method icons ── */
        .pm-icon {
            display: inline-flex; align-items: center; gap: 7px;
            font-weight: 600; font-size: 14px;
        }
        .pm-icon i { font-size: 18px; }
        .pm-cash   { color: #059669; }
        .pm-gcash  { color: #0070f3; }
        .pm-card   { color: #7c3aed; }

        /* ── Reference number form ── */
        .ref-form {
            margin-top: 14px;
            padding: 16px;
            background: #fffbeb;
            border: 1px dashed #fbbf24;
            border-radius: 10px;
        }
        .ref-form p { margin: 0 0 10px; font-size: 13px; color: #92400e; }
        .ref-form-row { display: flex; gap: 10px; }
        .ref-form input {
            flex: 1;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        .ref-form button {
            padding: 9px 20px;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        /* ── Payment instructions ── */
        .instructions-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 18px;
            margin-top: 14px;
        }
        .instructions-box .step {
            display: flex; gap: 12px; align-items: flex-start;
            font-size: 13px; color: #334155;
            margin-bottom: 12px;
        }
        .instructions-box .step:last-child { margin-bottom: 0; }
        .step-num {
            width: 24px; height: 24px;
            background: #1d4ed8;
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }

        /* ── Actions ── */
        .confirm-actions {
            display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg,#1d4ed8,#2563eb);
            color: #fff; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: opacity 0.2s;
        }
        .btn-primary:hover { opacity: 0.88; }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px;
            background: #fff; color: #374151;
            border: 1px solid #d1d5db; border-radius: 10px;
            font-size: 14px; font-weight: 500; cursor: pointer;
            text-decoration: none; transition: background 0.15s;
        }
        .btn-secondary:hover { background: #f3f4f6; }

        @media (max-width: 640px) {
            .confirm-grid { grid-template-columns: 1fr; }
            .confirm-hero { flex-direction: column; text-align: center; }
            .confirm-booking-id { margin-left: 0; text-align: center; }
            .date-strip { flex-direction: column; }
            .date-arrow { transform: rotate(90deg); }
        }
    </style>
</head>
<body class="customer-page">
    <?php require_once __DIR__ . '/php/header.php'; ?>

    <div class="confirm-page">

        <!-- Hero -->
        <div class="confirm-hero">
            <!-- <div class="confirm-hero-icon"></div> -->
            <div>
                <h1>Booking Received!</h1>
                <p>Thank you, <?= htmlspecialchars($data["first_name"]); ?>. Your reservation is being processed.</p>
            </div>
            <div class="confirm-booking-id">
                <div class="id-label">Booking ID</div>
                <div class="id-value">#<?= str_pad($reservation_id, 5, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <div class="confirm-grid">

            <!-- Room + Dates -->
            <div class="card full-width">
                <p class="card-title"><i class="fas fa-bed"></i> Room Details</p>

                <div class="room-card-inline">
                    <?php if ($data["image_path"]): ?>
                        <img src="<?= htmlspecialchars($data["image_path"]); ?>" alt="Room">
                    <?php endif; ?>
                    <div>
                        <div class="room-type"><?= htmlspecialchars($data["room_type"]); ?></div>
                        <div class="room-meta">
                            <i class="fas fa-door-open"></i> Room <?= htmlspecialchars($data["room_number"]); ?>
                            &nbsp;·&nbsp;
                            <?= $nights; ?> night<?= $nights > 1 ? 's' : ''; ?>
                        </div>
                    </div>
                    <div style="margin-left:auto;text-align:right">
                        <?= reservationBadge($data["reservation_status"]); ?>
                    </div>
                </div>

                <div class="date-strip" style="margin-top:20px">
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
                    <span class="label">Email</span>
                    <span class="value"><?= htmlspecialchars($data["email"]); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Phone</span>
                    <span class="value"><?= htmlspecialchars($data["phone_number"]); ?></span>
                </div>
                <?php if ($data["extra_requests"]): ?>
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
                    <span><?= htmlspecialchars($data["room_type"]); ?> × <?= $nights; ?> night<?= $nights > 1 ? 's' : ''; ?></span>
                    <span>₱<?= number_format($room_cost, 2); ?></span>
                </div>
                <?php foreach ($amenities_list as $a): ?>
                <div class="cost-row">
                    <span><?= htmlspecialchars($a["amenity_name"]); ?> ×<?= $a["quantity"]; ?></span>
                    <span>₱<?= number_format($a["subtotal"], 2); ?></span>
                </div>
                <?php endforeach; ?>
                <div class="cost-row total-row">
                    <span>Total</span>
                    <span>₱<?= number_format($total, 2); ?></span>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="card full-width">
                <p class="card-title"><i class="fas fa-credit-card"></i> Payment</p>

                <div class="info-row">
                    <span class="label">Method</span>
                    <span class="value">
                        <?php
                        $pm = $data["payment_method"];
                        $pmClass = $pm === "Cash" ? "pm-cash" : ($pm === "GCash" ? "pm-gcash" : "pm-card");
                        $pmIcon  = $pm === "Cash" ? "fas fa-money-bill-wave" : ($pm === "GCash" ? "fas fa-mobile-alt" : "fas fa-credit-card");
                        ?>
                        <span class="pm-icon <?= $pmClass; ?>">
                            <i class="<?= $pmIcon; ?>"></i> <?= htmlspecialchars($pm); ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Amount Due</span>
                    <span class="value" style="font-size:18px;color:#1d4ed8">₱<?= number_format($total, 2); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Payment Status</span>
                    <span class="value"><?= paymentBadge($data["payment_status"]); ?></span>
                </div>
                <?php if ($data["reference_number"]): ?>
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
                            <div>Pay ₱<?= number_format($total, 2); ?> in cash upon arrival. Your reservation will be confirmed by our staff.</div>
                        </div>
                    </div>

                <?php elseif ($data["payment_method"] === "GCash"): ?>
                    <div class="instructions-box">
                        <div class="step">
                            <div class="step-num">1</div>
                            <div>Send <strong>₱<?= number_format($total, 2); ?></strong> via GCash to <strong>0977 183 7288</strong> (Rawis Resort Hotel).</div>
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
                            <div>You'll receive a confirmation once verified. Contact us if you have questions.</div>
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

        </div><!-- /.confirm-grid -->

        <!-- Actions -->
        <div class="confirm-actions" style="margin-top:24px">
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