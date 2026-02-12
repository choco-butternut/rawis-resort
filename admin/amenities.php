<?php
require_once "../php/config.php";
require_once "../php/admin_auth.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amenity_name = sanitize_input($_POST["amenity_name"]);
    $description  = sanitize_input($_POST["description"]);
    $price        = (float) $_POST["price"];
    $status       = sanitize_input($_POST["amenity_status"]);

   
    if (!empty($_POST["amenity_id"])) {
        $amenity_id = (int) $_POST["amenity_id"];

        $stmt = $conn->prepare(
            "UPDATE amenities 
             SET amenity_name=?, description=?, price=?, amenity_status=? 
             WHERE amenity_id=?"
        );
        $stmt->bind_param("ssdsi",
            $amenity_name,
            $description,
            $price,
            $status,
            $amenity_id
        );
        $stmt->execute();
        $stmt->close();
    }
    
    else {
        $stmt = $conn->prepare(
            "INSERT INTO amenities (amenity_name, description, price, amenity_status)
             VALUES (?,?,?,?)"
        );
        $stmt->bind_param("ssds",
            $amenity_name,
            $description,
            $price,
            $status
        );
        $stmt->execute();
        $stmt->close();
    }

    header("Location: amenities.php");
    exit();
}


if (isset($_GET["delete"])) {
    $amenity_id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM amenities WHERE amenity_id=?"
    );
    $stmt->bind_param("i", $amenity_id);
    $stmt->execute();
    $stmt->close();

    header("Location: amenities.php");
    exit();
}

$edit = null;
if (isset($_GET["edit"])) {
    $amenity_id = (int) $_GET["edit"];
    $result = $conn->query("SELECT * FROM amenities WHERE amenity_id=$amenity_id");
    $edit = $result->fetch_assoc();
}

$amenities = $conn->query("SELECT * FROM amenities ORDER BY amenity_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amenities</title>
</head>
<body>
    <a href="/admin/dashboard.php">Dashboard</a>
    <a href="/admin/rooms.php">Rooms</a>
    <a href="/admin/reservation.php">Reservations</a>
    <a href="/admin/amenities.php">Amenities</a>
    <a href="/admin/logout.php">Logout</a>
    <br>
    <h2><?= $edit ? "Edit Amenity" : "Add Amenity"; ?></h2>

    <form method="POST">
        <?php if ($edit): ?>
            <input type="hidden" name="amenity_id" value="<?= $edit["amenity_id"]; ?>">
        <?php endif; ?>

        <input type="text" name="amenity_name" placeholder="Amenity Name"
            value="<?= $edit["amenity_name"] ?? ""; ?>" required>

        <textarea name="description" placeholder="Description"><?= $edit["description"] ?? ""; ?></textarea>

        <input type="number" step="0.01" name="price" placeholder="Price"
            value="<?= $edit["price"] ?? 0; ?>" required>

        <select name="amenity_status">
            <option value="Available" <?= (isset($edit) && $edit["amenity_status"]=="Available")?"selected":""; ?>>
                Available
            </option>
            <option value="Unavailable" <?= (isset($edit) && $edit["amenity_status"]=="Unavailable")?"selected":""; ?>>
                Unavailable
            </option>
        </select>

        <button type="submit">
            <?= $edit ? "Update Amenity" : "Add Amenity"; ?>
        </button>
    </form>

    <h2>Amenities List</h2>

    <table border="1" cellpadding="8">
    <tr>
        <th>Name</th>
        <th>Price</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $amenities->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row["amenity_name"]); ?></td>
        <td>₱<?= number_format($row["price"],2); ?></td>
        <td><?= $row["amenity_status"]; ?></td>
        <td>
            <a href="amenities.php?edit=<?= $row["amenity_id"]; ?>">Edit</a> |
            <a href="amenities.php?delete=<?= $row["amenity_id"]; ?>"
            onclick="return confirm('Delete amenity?');">
            Delete
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
    </table>


</body>
</html>