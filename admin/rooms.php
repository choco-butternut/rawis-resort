<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $room_number     = sanitize_input($_POST["room_number"]);
    $room_type       = sanitize_input($_POST["room_type"]);
    $max_capacity    = (int) $_POST["max_capacity"];
    $price_per_night = (float) $_POST["price_per_night"];
    $room_status     = sanitize_input($_POST["room_status"]);
    $image_path      = "";

    if (isset($_FILES["room_image"]) && $_FILES["room_image"]["error"] === 0) {

        $upload_dir = "../uploads/rooms/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["room_image"]["name"]);
        $target_file = $upload_dir . $filename;

        move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file);

        $image_path = "uploads/rooms/" . $filename;
    }


    
    if (!empty($_POST["room_id"])) {
        $room_id = (int) $_POST["room_id"];

        $stmt = $conn->prepare(
            "UPDATE rooms 
             SET room_number=?, room_type=?, max_capacity=?, price_per_night=?, room_status=?
             WHERE room_id=?"
        );
        $stmt->bind_param(
            "ssidsi",
            $room_number,
            $room_type,
            $max_capacity,
            $price_per_night,
            $room_status,
            $room_id
        );
        $stmt->execute();
        $stmt->close();
    }
    
    else {
        $stmt = $conn->prepare(
            "INSERT INTO rooms (room_number, room_type, max_capacity, price_per_night, room_status, image_path)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            "ssidss",
            $room_number,
            $room_type,
            $max_capacity,
            $price_per_night,
            $room_status,
            $image_path
        );
        $stmt->execute();
        $stmt->close();
    }

    header("Location: rooms.php");
    exit();
}

if (isset($_GET["delete"])) {
    $room_id = (int) $_GET["delete"];

    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id=?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();

    header("Location: rooms.php");
    exit();
}


$edit_room = null;
if (isset($_GET["edit"])) {
    $room_id = (int) $_GET["edit"];

    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id=?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_room = $result->fetch_assoc();
    $stmt->close();
}


$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms</title>
</head>
<body>
    <a href= dashboard.php>Dashboard</a>
    <a href="rooms.php">Rooms</a>
    <a href="reservation.php">Reservations</a>
    <a href="amenities.php">Amenities</a>
    <a href="logout.php">Logout</a>
    <br>
    <h2><?= $edit_room ? "Edit Room" : "Add Room"; ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($edit_room): ?>
            <input type="hidden" name="room_id" value="<?= $edit_room["room_id"]; ?>">
        <?php endif; ?>

        <input type="text" name="room_number" placeholder="Room Number"
            value="<?= $edit_room["room_number"] ?? ""; ?>" required>

        <input type="file" name="room_image" accept="image/*"
            value="<?= $edit_room["image_path"] ?? ""; ?>" required>


        <input type="text" name="room_type" placeholder="Room Type"
            value="<?= $edit_room["room_type"] ?? ""; ?>" required>

        <input type="number" name="max_capacity" placeholder="Max Capacity"
            value="<?= $edit_room["max_capacity"] ?? ""; ?>" required>

        <input type="number" step="0.01" name="price_per_night" placeholder="Price per Night"
            value="<?= $edit_room["price_per_night"] ?? ""; ?>" required>

        <select name="room_status" required>
            <option value="available" <?= (isset($edit_room) && $edit_room["room_status"]=="available")?"selected":""; ?>>Available</option>
            <option value="occupied" <?= (isset($edit_room) && $edit_room["room_status"]=="occupied")?"selected":""; ?>>Occupied</option>
            <option value="maintenance" <?= (isset($edit_room) && $edit_room["room_status"]=="maintenance")?"selected":""; ?>>Maintenance</option>
        </select>

        <button type="submit">
            <?= $edit_room ? "Update Room" : "Add Room"; ?>
        </button>
    </form>

    <h2>Rooms</h2>

    <table  border="1" cellpadding="8">
        <tr>
            <th>Room #</th>
            <th>Type</th>
            <th>Capacity</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $rooms->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["room_number"]); ?></td>
                <td><?= htmlspecialchars($row["room_type"]); ?></td>
                <td><?= $row["max_capacity"]; ?></td>
                <td><?= number_format($row["price_per_night"], 2); ?></td>
                <td><?= htmlspecialchars($row["room_status"]); ?></td>
                <td>
                    <a href="rooms.php?edit=<?= $row["room_id"]; ?>">Edit</a> |
                    <a href="rooms.php?delete=<?= $row["room_id"]; ?>"
                    onclick="return confirm('Delete this room?');">
                    Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>


</body>
</html>