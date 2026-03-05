<?php
require_once __DIR__ . '/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /rooms.php");
    exit();
}

// ── 1. Guest info ──────────────────────────────────────────────
$room_id        = (int) $_POST["room_id"];
$first_name     = sanitize_input($_POST["first_name"]);
$last_name      = sanitize_input($_POST["last_name"]);
$email          = sanitize_input($_POST["email"]);
$phone_number   = sanitize_input($_POST["phone_number"]);
$address        = sanitize_input($_POST["address"] ?? "");
$check_in       = $_POST["check_in_date"];
$check_out      = $_POST["check_out_date"];
$extra_requests = sanitize_input($_POST["extra_requests"] ?? "");
$payment_method = sanitize_input($_POST["payment_method"] ?? "Cash");
$reference_number = sanitize_input($_POST["reference_number"] ?? "");

// ── 2. Validate dates ──────────────────────────────────────────
$today = date('Y-m-d');
if ($check_in < $today) {
    header("Location: /rooms.php?error=invalid_date");
    exit();
}
if ($check_out <= $check_in) {
    header("Location: /rooms.php?error=invalid_checkout");
    exit();
}

// ── 3. Check room is still available ──────────────────────────
$roomStmt = $conn->prepare("SELECT * FROM rooms WHERE room_id=? AND room_status='available'");
$roomStmt->bind_param("i", $room_id);
$roomStmt->execute();
$room = $roomStmt->get_result()->fetch_assoc();
$roomStmt->close();

if (!$room) {
    header("Location: /rooms.php?error=unavailable");
    exit();
}

// ── 4. Upsert guest user (match by email) ─────────────────────
$userStmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$userStmt->bind_param("s", $email);
$userStmt->execute();
$existingUser = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if ($existingUser) {
    $guest_id = $existingUser["id"];
    // Update their info in case it changed
    $upd = $conn->prepare(
        "UPDATE users SET first_name=?, last_name=?, phone_number=?, address=? WHERE id=?"
    );
    $upd->bind_param("ssssi", $first_name, $last_name, $phone_number, $address, $guest_id);
    $upd->execute();
    $upd->close();
} else {
    $username = $first_name . " " . $last_name . "_" . time();
    $ins = $conn->prepare(
        "INSERT INTO users (username, password, first_name, last_name, email, phone_number, address, role)
         VALUES (?, '', ?, ?, ?, ?, ?, 'guest')"
    );
    $ins->bind_param("ssssss", $username, $first_name, $last_name, $email, $phone_number, $address);
    $ins->execute();
    $guest_id = $ins->insert_id;
    $ins->close();
}

// ── 5. Create reservation (Pending) ───────────────────────────
$num_guests = 1; // placeholder until num_guests field is added to form
$res = $conn->prepare(
    "INSERT INTO reservations
     (guest_id, room_id, check_in_date, check_out_date, num_guests, reservation_status, extra_requests, created_at)
     VALUES (?, ?, ?, ?, ?, 'Pending', ?, NOW())"
);
$res->bind_param("iissis", $guest_id, $room_id, $check_in, $check_out, $num_guests, $extra_requests);
$res->execute();
$reservation_id = $res->insert_id;
$res->close();

// ── 6. Attach amenities ────────────────────────────────────────
if (!empty($_POST["amenities"])) {
    foreach ($_POST["amenities"] as $amenity_id => $dummy) {
        $amenity_id = (int) $amenity_id;
        $quantity   = isset($_POST["quantity"][$amenity_id]) ? (int) $_POST["quantity"][$amenity_id] : 1;

        $priceRes = $conn->query("SELECT price FROM amenities WHERE amenity_id=$amenity_id");
        $priceRow = $priceRes->fetch_assoc();
        $price    = $priceRow["price"];

        $am = $conn->prepare(
            "INSERT INTO reservation_amenities (reservation_id, amenity_id, quantity, price)
             VALUES (?, ?, ?, ?)"
        );
        $am->bind_param("iiid", $reservation_id, $amenity_id, $quantity, $price);
        $am->execute();
        $am->close();
    }
}

// ── 7. Compute total amount ────────────────────────────────────
$nights = (new DateTime($check_in))->diff(new DateTime($check_out))->days;
$room_total = $room["price_per_night"] * $nights;

$amTotal = 0;
$amRes = $conn->prepare(
    "SELECT SUM(price * quantity) as total FROM reservation_amenities WHERE reservation_id=?"
);
$amRes->bind_param("i", $reservation_id);
$amRes->execute();
$amRow = $amRes->get_result()->fetch_assoc();
$amTotal = $amRow["total"] ?? 0;
$amRes->close();

$total_amount = $room_total + $amTotal;

// ── 8. Create payment record ───────────────────────────────────
// Payment status logic:
//   Cash      → Pending        (pay at front desk)
//   GCash     → if ref given → Awaiting Verification, else Pending
//   Card      → if ref given → Awaiting Verification, else Pending
if ($payment_method === "Cash") {
    $payment_status = "Pending";
} else {
    $payment_status = !empty($reference_number) ? "Awaiting Verification" : "Pending";
}

$pay = $conn->prepare(
    "INSERT INTO payments (reservation_id, amount_paid, payment_method, payment_status, reference_number)
     VALUES (?, ?, ?, ?, ?)"
);
$pay->bind_param("idsss", $reservation_id, $total_amount, $payment_method, $payment_status, $reference_number);
$pay->execute();
$pay->close();

// ── 9. Redirect to booking confirmation page ───────────────────
header("Location: /booking-confirmation.php?r=" . $reservation_id . "&t=" . urlencode(base64_encode($email)));
exit();