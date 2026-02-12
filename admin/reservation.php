<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";


if (isset($_POST["update_status"])) {

    $reservation_id = (int) $_POST["reservation_id"];
    $status = sanitize_input($_POST["reservation_status"]);

    $stmt = $conn->prepare("SELECT room_id FROM reservations WHERE reservation_id=?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $room_id = $row["room_id"];
    $stmt->close();

    $stmt2 = $conn->prepare(
        "UPDATE reservations 
         SET reservation_status=? 
         WHERE reservation_id=?"
    );
    $stmt2->bind_param("si", $status, $reservation_id);
    $stmt2->execute();
    $stmt2->close();

    if ($status === "Confirmed") {
        $stmt3 = $conn->prepare(
            "UPDATE rooms SET room_status='occupied' WHERE room_id=?"
        );
        $stmt3->bind_param("i", $room_id);
        $stmt3->execute();
        $stmt3->close();
    }

    header("Location: reservation.php");
    exit();
}


if (isset($_GET["delete"])) {
    $reservation_id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM reservations WHERE reservation_id=?"
    );
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->close();

    header("Location: reservation.php");
    exit();
}


$reservations = $conn->query("
    SELECT r.*, 
           u.first_name, u.last_name, 
           rm.room_number, rm.room_type
    FROM reservations r
    JOIN users u ON r.guest_id = u.id
    JOIN rooms rm ON r.room_id = rm.room_id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href= dashboard.php>Dashboard</a>
    <a href="rooms.php">Rooms</a>
    <a href="reservation.php">Reservations</a>
    <a href="amenities.php">Amenities</a>
    <a href="logout.php">Logout</a>
    <br>
    <h2>Reservations</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Guest</th>
            <th>Room</th>
            <th>Amenities</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Guests</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

    <?php while ($row = $reservations->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?></td>
        <td><?= htmlspecialchars($row["room_number"] . " - " . $row["room_type"]); ?></td>
        <td>
        <?php
            $res_id = $row["reservation_id"];

            $amenityQuery = $conn->prepare("
                SELECT a.amenity_name, ra.quantity, ra.price
                FROM reservation_amenities ra
                JOIN amenities a ON ra.amenity_id = a.amenity_id
                WHERE ra.reservation_id = ?
            ");
            $amenityQuery->bind_param("i", $res_id);
            $amenityQuery->execute();
            $amenityResult = $amenityQuery->get_result();

            if ($amenityResult->num_rows > 0) {
                while ($a = $amenityResult->fetch_assoc()) {
                    $subtotal = $a["price"] * $a["quantity"];
                    echo htmlspecialchars($a["amenity_name"]) .
                        " (x" . $a["quantity"] . ") - ₱" .
                        number_format($subtotal,2) . "<br>";
                }
            } else {
                echo "None";
            }

            $amenityQuery->close();
        ?>
        </td>

        <td><?= $row["check_in_date"]; ?></td>
        <td><?= $row["check_out_date"]; ?></td>
        <td><?= $row["num_guests"]; ?></td>

        <td>
            <form method="POST">
                <input type="hidden" name="reservation_id" value="<?= $row["reservation_id"]; ?>">
                <select name="reservation_status">
                    <option <?= $row["reservation_status"]=='Pending'?'selected':''; ?>>Pending</option>
                    <option <?= $row["reservation_status"]=='Confirmed'?'selected':''; ?>>Confirmed</option>
                    <option <?= $row["reservation_status"]=='Cancelled'?'selected':''; ?>>Cancelled</option>
                    <option <?= $row["reservation_status"]=='Completed'?'selected':''; ?>>Completed</option>
                </select>
                <button type="submit" name="update_status">Update</button>
            </form>
        </td>

        <td>
            <a href="reservation.php?delete=<?= $row["reservation_id"]; ?>"
            onclick="return confirm('Delete reservation?');">
            Delete
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
    </table>


</body>
</html>
