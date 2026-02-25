<?php
require_once __DIR__ . "/php/config.php";

if (!isset($_GET["reservation_id"])) {
    die("Invalid access.");
}

$reservation_id = (int) $_GET["reservation_id"];

$stmt = $conn->prepare("
    SELECT r.*, rm.price_per_night
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.room_id
    WHERE r.reservation_id = ?
");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Reservation not found.");
}

$reservation = $result->fetch_assoc();
$stmt->close();

$checkin = new DateTime($reservation["check_in_date"]);
$checkout = new DateTime($reservation["check_out_date"]);
$nights = $checkin->diff($checkout)->days;

$total = $reservation["price_per_night"] * $nights;

$stmt2 = $conn->prepare("
    SELECT quantity, price
    FROM reservation_amenities
    WHERE reservation_id = ?
");
$stmt2->bind_param("i", $reservation_id);
$stmt2->execute();
$amenities = $stmt2->get_result();

while ($a = $amenities->fetch_assoc()) {
    $total += $a["price"] * $a["quantity"];
}
$stmt2->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $method = sanitize_input($_POST["payment_method"]);
    $reference = sanitize_input($_POST["reference_number"]);

    $stmt3 = $conn->prepare("
        INSERT INTO payments
        (reservation_id, amount_paid, payment_method, payment_status, reference_number)
        VALUES (?,?,?,?,?)
    ");

    $payment_status = "Completed";

    $stmt3->bind_param(
        "idsss",
        $reservation_id,
        $total,
        $method,
        $payment_status,
        $reference
    );

    $stmt3->execute();
    $stmt3->close();

    $stmt4 = $conn->prepare("
        UPDATE reservations
        SET reservation_status='Confirmed'
        WHERE reservation_id=?
    ");
    $stmt4->bind_param("i", $reservation_id);
    $stmt4->execute();
    $stmt4->close();

    header("Location: /index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="customer-page">
    <div class="payment-page">
        <?php require_once __DIR__ . '/php/header.php'; ?>

        <div class="page-header">
            <h1>Payment</h1>
        </div>

        <div class="payment-container">
            <div class="payment-summary">
                <h2>Reservation Summary</h2>
                
                <div class="summary-details">
                    <div class="detail-row">
                        <span class="detail-label">Check-in:</span>
                        <span class="detail-value"><?= $reservation["check_in_date"]; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Check-out:</span>
                        <span class="detail-value"><?= $reservation["check_out_date"]; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Number of Nights:</span>
                        <span class="detail-value"><?= $nights; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Room Cost:</span>
                        <span class="detail-value">₱<?= number_format($reservation["price_per_night"] * $nights, 2); ?></span>
                    </div>
                </div>

                <div class="total-amount">
                    <h3>Total Amount Due</h3>
                    <p class="amount">₱<?= number_format($total, 2); ?></p>
                </div>
            </div>

            <div class="payment-form-section">
                <h2>Payment Information</h2>
                
                <form method="POST" class="payment-form">
                    <div class="form-group">
                        <label for="payment_method">Payment Method <span class="required">*</span></label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select a payment method</option>
                            <option value="Cash">Cash</option>
                            <option value="GCash">GCash</option>
                            <option value="Card">Debit/Credit Card</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" placeholder="Enter transaction reference (optional)">
                    </div>

                    <button type="submit" class="btn-pay">Pay Now</button>
                </form>
            </div>
        </div>

        <?php require_once __DIR__ . '/php/footer.php'; ?>
    </div>
</body>
</html>

