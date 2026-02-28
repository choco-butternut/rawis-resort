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
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- room add/edit modal -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeRoomModal()">&times;</button>
            <h2 id="modalTitle"><?= $edit_room ? "Edit Room" : "Add Room"; ?></h2>

            <form id="roomForm" method="POST" enctype="multipart/form-data">
                <?php if ($edit_room): ?>
                    <input type="hidden" name="room_id" value="<?= $edit_room["room_id"]; ?>">
                <?php endif; ?>

                <input type="text" name="room_number" placeholder="Room Number"
                    value="<?= $edit_room["room_number"] ?? ""; ?>" required>

                
                <?php if (!$edit_room): ?>
                    <input type="file" name="room_image" accept="image/*"
                    value="<?= $edit_room["image_path"] ?? ""; ?>" required>
                <?php endif; ?>


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
        </div>
    </div>

    <main class="main-content">
        <div class="section-title">
            <h1>ROOMS</h1>
            <hr class="header-line">
            
            <div class="toolbar">
                <div class="filter-group">
                    <i class="fas fa-bars"></i> Filter by
                </div>
                <div class="search-add">
                    <div class="search-bar">
                        <input type="text" placeholder="Search">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn-add"><i class="fas fa-plus"></i> Add</button>
                </div>
            </div>

            <div class="status-filters">
                <a href="#" class="active">All rooms (<?= $rooms->num_rows ?>)</a>
                <a href="#">Available rooms (13)</a>
                <a href="#">Occupied rooms (6)</a>
                <a href="#">Under Maintenance (0)</a>
            </div>
        </div>

        <div class="rooms-grid">
            <?php while ($row = $rooms->fetch_assoc()): ?>
                <div class="room-card" 
                     data-room-id="<?= $row["room_id"]; ?>"
                     data-room-number="<?= htmlspecialchars($row["room_number"]); ?>"
                     data-room-type="<?= htmlspecialchars($row["room_type"]); ?>"
                     data-capacity="<?= $row["max_capacity"]; ?>"
                     data-price="<?= number_format($row["price_per_night"], 2); ?>"
                     data-status="<?= htmlspecialchars($row["room_status"]); ?>"
                     style="cursor: pointer;" onclick="openRoomDetailModal(event)">
                    <div class="room-image">
                        <img src="../<?= $row["image_path"] ?: 'assets/images/default-room.jpg'; ?>" alt="Room Image">
                        <div class="room-number-badge"><?= str_pad($row["room_number"], 2, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    
                    <div class="room-details">
                        <div class="room-title-row">
                            <?php
                                // split room type into two parts for styling
                                $parts = explode(' ', $row['room_type'], 2);
                                $first_word = $parts[0];
                                $second_word = $parts[1] ?? '';
                            ?>
                            <h3 class="room-title">
                                <span class="main-name"><?= htmlspecialchars($first_word); ?></span>
                                <span class="sub-name"><?= htmlspecialchars($second_word); ?></span>
                            </h3>
                            <div class="room-price">
                                <span class="currency">PHP</span>
                                <span class="amount"><?= number_format($row["price_per_night"], 0); ?></span>
                                <span class="period">/night</span>
                            </div>
                        </div>

                        <div class="room-info">
                            <span><i class="fas fa-bed"></i> 1 Double Bed</span> 
                            <!-- it yadi dapat upod hin ht igffill up ngin mag add room -->
                            <span><i class="fas fa-users"></i> <?= $row["max_capacity"]; ?> Guests</span>
                        </div>

                        <div class="room-footer">
                            <span class="status-pill <?= strtolower($row["room_status"]); ?>">
                                <?= ucfirst($row["room_status"]); ?>
                            </span>
                            <div class="card-actions" onclick="event.stopPropagation();">
                                <a href="#"><i class="fas fa-eye"></i></a>
                                <a href="rooms.php?delete=<?= $row["room_id"]; ?>" onclick="return confirm('Delete?');"><i class="fas fa-trash"></i></a>
                                <a href="rooms.php?edit=<?= $row["room_id"]; ?>"><i class="fas fa-edit"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Room Detail Modal -->
    <div id="roomDetailModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeRoomDetailModal()">&times;</button>
            <h2>Room Details</h2>
            
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Room Number</label>
                    <p id="modalRoomNumber"></p>
                </div>
                <div class="detail-item">
                    <label>Room Type</label>
                    <p id="modalRoomType"></p>
                </div>
                <div class="detail-item">
                    <label>Max Capacity</label>
                    <p id="modalCapacity"></p>
                </div>
                <div class="detail-item">
                    <label>Price per Night</label>
                    <p id="modalPrice"></p>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <p id="modalStatus"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openRoomDetailModal(event) {
        const card = event.currentTarget;
        
        document.getElementById('modalRoomNumber').textContent = '#' + card.dataset.roomNumber;
        document.getElementById('modalRoomType').textContent = card.dataset.roomType;
        document.getElementById('modalCapacity').textContent = card.dataset.capacity + ' Guests';
        document.getElementById('modalPrice').textContent = '₱' + card.dataset.price + ' /night';
        document.getElementById('modalStatus').textContent = card.dataset.status.charAt(0).toUpperCase() + card.dataset.status.slice(1);
        
        document.getElementById('roomDetailModal').classList.add('show');
    }

    function closeRoomDetailModal() {
        document.getElementById('roomDetailModal').classList.remove('show');
    }

    // Close modal when clicking backdrop
    document.getElementById('roomDetailModal').addEventListener('click', function(evt) {
        if(evt.target === this) {
            closeRoomDetailModal();
        }
    });
    </script>

</body>
</html>