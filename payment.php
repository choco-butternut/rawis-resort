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
    <title>Payment</title>
</head>
<body>

<h2>Reservation Payment</h2>

<p><strong>Check-in:</strong> <?= $reservation["check_in_date"]; ?></p>
<p><strong>Check-out:</strong> <?= $reservation["check_out_date"]; ?></p>
<p><strong>Nights:</strong> <?= $nights; ?></p>

<h3>Total Amount: ₱<?= number_format($total,2); ?></h3>

<form method="POST">
    <label>Payment Method:</label>
    <select name="payment_method" required>
        <option value="Cash">Cash</option>
        <option value="GCash">GCash</option>
        <option value="Card">Card</option>
    </select>

    <br><br>

    <label>Reference Number:</label>
    <input type="text" name="reference_number" placeholder="Optional">

    <br><br>

    <button type="submit">Pay Now</button>
</form>

</body>
</html>

