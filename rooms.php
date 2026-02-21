<?php
require_once __DIR__ . '/php/config.php';

$amenities = $conn->query(
    "SELECT * FROM amenities WHERE amenity_status='Available'"
);

$rooms = $conn->query("SELECT * FROM rooms WHERE room_status='available'");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms</title>
</head>
<div id="reserveModal" style="display:none;">
    <div class="modal-content">
        <form method="POST" action="/php/reserve.php">
            <input type="hidden" name="room_id" id="room_id">

            <h3>Reserve Room</h3>

            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="text" name="address" placeholder="Address">

            <label>Check-in</label>
            <input type="date" name="check_in_date" required>

            <label>Check-out</label>
            <input type="date" name="check_out_date" required>

            <input type="number" name="num_guests" placeholder="Number of Guests" required>

            <h4>Select Amenities</h4>

            <?php while($amenity = $amenities->fetch_assoc()): ?>
                <div>
                    <input type="checkbox"
                        name="amenities[<?= $amenity['amenity_id']; ?>]"
                        value="<?= $amenity['price']; ?>">
                    
                    <?= htmlspecialchars($amenity['amenity_name']); ?>
                    (₱<?= number_format($amenity['price'],2); ?>)

                    Quantity:
                    <input type="number"
                        name="quantity[<?= $amenity['amenity_id']; ?>]"
                        min="1"
                        value="1">
                </div>
            <?php endwhile; ?>


            <textarea name="extra_requests" placeholder="Special Requests"></textarea>

            <button type="submit">Confirm Reservation</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>
<body>
    <?php require_once __DIR__ . '/php/header.php'; ?>
    <div class="room-cards">
        <?php while($room = $rooms->fetch_assoc()): ?>
            <div class="room-card">
                <h3><?= htmlspecialchars($room["room_type"]); ?></h3>
                <p>Room #: <?= htmlspecialchars($room["room_number"]); ?></p>
                <p>Capacity: <?= $room["max_capacity"]; ?> guests</p>
                <p>₱<?= number_format($room["price_per_night"],2); ?> / night</p>

                <button onclick="openModal(<?= $room['room_id']; ?>)">
                    Reserve
                </button>
            </div>
        <?php endwhile; ?>
    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>

    <script>
    function openModal(roomId){
        document.getElementById("reserveModal").style.display = "block";
        document.getElementById("room_id").value = roomId;
    }

    function closeModal(){
        document.getElementById("reserveModal").style.display = "none";
    }
    </script>
</body>
</html>