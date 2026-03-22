<?php
require_once __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /rooms.php");
    exit();
}

$room_id        = (int) $_POST["room_id"];
$first_name     = sanitize_input($_POST["first_name"]);
$last_name      = sanitize_input($_POST["last_name"]);
$phone_number   = sanitize_input($_POST["phone_number"]);
$check_in       = $_POST["check_in_date"];
$check_out      = $_POST["check_out_date"];
$extra_requests = sanitize_input($_POST["extra_requests"] ?? "");
$payment_method = sanitize_input($_POST["payment_method"] ?? "Cash");
$reference_number = sanitize_input($_POST["reference_number"] ?? "");

$card_last4 = null;
if ($payment_method === 'Card' && preg_match('/CARD-XXXX-(\d{4})$/', $reference_number, $m)) {
    $card_last4 = $m[1];
    $reference_number = $card_last4; // store just the last4 as the reference
}

$today = date('Y-m-d');
if ($check_in < $today) {
    header("Location: /rooms.php?error=invalid_date");
    exit();
}
if ($check_out <= $check_in) {
    header("Location: /rooms.php?error=invalid_checkout");
    exit();
}

$roomStmt = $conn->prepare("SELECT * FROM rooms WHERE room_id=? AND room_status='available'");
$roomStmt->bind_param("i", $room_id);
$roomStmt->execute();
$room = $roomStmt->get_result()->fetch_assoc();
$roomStmt->close();

if (!$room) {
    header("Location: /rooms.php?error=unavailable");
    exit();
}

$userStmt = $conn->prepare("SELECT id FROM users WHERE phone_number=?");
$userStmt->bind_param("s", $phone_number);
$userStmt->execute();
$existingUser = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if ($existingUser) {
    $guest_id = $existingUser["id"];
    $upd = $conn->prepare(
        "UPDATE users SET first_name=?, last_name=?, phone_number=? WHERE id=?"
    );
    $upd->bind_param("sssi", $first_name, $last_name, $phone_number, $guest_id);
    $upd->execute();
    $upd->close();
} else {
    $username = $first_name . " " . $last_name . "_" . time();
    $ins = $conn->prepare(
        "INSERT INTO users (username, password, first_name, last_name, phone_number, role)
         VALUES (?, '', ?, ?, ?, 'guest')"
    );
    $ins->bind_param("ssss", $username, $first_name, $last_name, $phone_number);
    $ins->execute();
    $guest_id = $ins->insert_id;
    $ins->close();
}

$num_guests = 1;
$res = $conn->prepare(
    "INSERT INTO reservations
     (guest_id, room_id, check_in_date, check_out_date, num_guests, reservation_status, extra_requests, created_at)
     VALUES (?, ?, ?, ?, ?, 'Pending', ?, NOW())"
);
$res->bind_param("iissis", $guest_id, $room_id, $check_in, $check_out, $num_guests, $extra_requests);
$res->execute();
$reservation_id = $res->insert_id;
$res->close();

if (!empty($_POST["amenities"])) {
    foreach ($_POST["amenities"] as $amenity_id => $dummy) {
        $amenity_id = (int) $amenity_id;
        $quantity   = isset($_POST["quantity"][$amenity_id]) ? (int) $_POST["quantity"][$amenity_id] : 1;

        $am = $conn->prepare(
            "INSERT INTO reservation_amenities (reservation_id, amenity_id, quantity)
             VALUES (?, ?, ?)"
        );
        $am->bind_param("iii", $reservation_id, $amenity_id, $quantity);
        $am->execute();
        $am->close();
    }
}
$extra_guests    = max(0, (int) ($_POST["extra_guests"] ?? 0));
$extra_beds      = max(0, (int) ($_POST["extra_beds"]   ?? 0));

$nights       = (new DateTime($check_in))->diff(new DateTime($check_out))->days;
$total_amount = $room["price_per_night"] * $nights
              + ($room["extra_guest_fee"] * $extra_guests * $nights)
              + ($room["extra_bed_fee"]   * $extra_beds   * $nights);

              
if ($payment_method === "Cash") {
    $payment_status = "Pending";
} else {
    $payment_status = !empty($reference_number) ? "Awaiting Verification" : "Pending";
}

$pay = $conn->prepare(
    "INSERT INTO payments (reservation_id, amount_paid, payment_method, payment_status, reference_number, card_last4)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$pay->bind_param("idssss", $reservation_id, $total_amount, $payment_method, $payment_status, $reference_number, $card_last4);
$pay->execute();
$pay->close();

header("Location: /booking-confirmation.php?r=" . $reservation_id);
exit();