<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amenity_name = sanitize_input($_POST["amenity_name"]);
    $description  = sanitize_input($_POST["description"]);
    $price        = (float) $_POST["price"];
    $status       = sanitize_input($_POST["amenity_status"]);
    $image_path   = "";

    // Handle image upload
    if (isset($_FILES["amenity_image"]) && $_FILES["amenity_image"]["error"] === 0) {

        $upload_dir = "../uploads/amenities/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["amenity_image"]["name"]);
        $target_file = $upload_dir . $filename;

        move_uploaded_file($_FILES["amenity_image"]["tmp_name"], $target_file);

        $image_path = "uploads/amenities/" . $filename;
    }

    // UPDATE
    if (!empty($_POST["amenity_id"])) {
        $amenity_id = (int) $_POST["amenity_id"];

        // If no new image uploaded, keep old one
        if (empty($image_path)) {
            $stmt = $conn->prepare("SELECT image_path FROM amenities WHERE amenity_id=?");
            $stmt->bind_param("i", $amenity_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old = $result->fetch_assoc();
            $image_path = $old["image_path"];
            $stmt->close();
        }

        $stmt = $conn->prepare(
            "UPDATE amenities 
             SET amenity_name=?, description=?, price=?, image_path=?, amenity_status=? 
             WHERE amenity_id=?"
        );
        $stmt->bind_param("ssdssi",
            $amenity_name,
            $description,
            $price,
            $image_path,
            $status,
            $amenity_id
        );
        $stmt->execute();
        $stmt->close();
    }
    // INSERT
    else {
        $stmt = $conn->prepare(
            "INSERT INTO amenities (amenity_name, description, price, image_path, amenity_status)
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("ssdss",
            $amenity_name,
            $description,
            $price,
            $image_path,
            $status
        );
        $stmt->execute();
        $stmt->close();
    }

    header("Location: amenities.php");
    exit();
}

// DELETE
if (isset($_GET["delete"])) {
    $amenity_id = (int) $_GET["delete"];

    $stmt = $conn->prepare("DELETE FROM amenities WHERE amenity_id=?");
    $stmt->bind_param("i", $amenity_id);
    $stmt->execute();
    $stmt->close();

    header("Location: amenities.php");
    exit();
}

// EDIT
$edit = null;
if (isset($_GET["edit"])) {
    $amenity_id = (int) $_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM amenities WHERE amenity_id=?");
    $stmt->bind_param("i", $amenity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit = $result->fetch_assoc();
    $stmt->close();
}

$amenities = $conn->query("SELECT * FROM amenities ORDER BY amenity_name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Amenities</title>
</head>
<body>

<a href="dashboard.php">Dashboard</a>
<a href="rooms.php">Rooms</a>
<a href="reservation.php">Reservations</a>
<a href="amenities.php">Amenities</a>
<a href="logout.php">Logout</a>

<h2><?= $edit ? "Edit Amenity" : "Add Amenity"; ?></h2>

<form method="POST" enctype="multipart/form-data">

    <?php if ($edit): ?>
        <input type="hidden" name="amenity_id" value="<?= $edit["amenity_id"]; ?>">
    <?php endif; ?>

    <input type="text" name="amenity_name" placeholder="Amenity Name"
        value="<?= $edit["amenity_name"] ?? ""; ?>" required>

    <textarea name="description" placeholder="Description"><?= $edit["description"] ?? ""; ?></textarea>

    <input type="number" step="0.01" name="price" placeholder="Price"
        value="<?= $edit["price"] ?? 0; ?>" required>

    <input type="file" name="amenity_image" accept="image/*">

    <?php if (!empty($edit["image_path"])): ?>
        <br>
        <img src="../<?= $edit["image_path"]; ?>" width="100">
    <?php endif; ?>

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
    <th>Image</th>
    <th>Name</th>
    <th>Price</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while($row = $amenities->fetch_assoc()): ?>
<tr>
    <td>
        <?php if (!empty($row["image_path"])): ?>
            <img src="../<?= $row["image_path"]; ?>" width="80">
        <?php endif; ?>
    </td>
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