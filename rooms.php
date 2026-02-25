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
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="customer-page">
    <div class="rooms-page">
        <?php require_once __DIR__ . '/php/header.php'; ?>
        
        <div id="reserveModal">
            <div class="modal-content">
                <button type="button" class="modal-close" onclick="closeModal()"><i class="fa-solid fa-times"></i></button>
                <form method="POST" action="/php/reserve.php">
                    <input type="hidden" name="room_id" id="room_id">

                    <h3>Reserve Room</h3>

                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="phone_number" placeholder="Phone Number" required>
                    <input type="text" name="address" placeholder="Address">

                    <div>
                        <label>Check-in</label>
                        <input type="date" name="check_in_date" required>
                    </div>

                    <div>
                        <label>Check-out</label>
                        <input type="date" name="check_out_date" required>
                    </div>

                    <input type="number" name="num_guests" placeholder="Number of Guests" required>

                    <h4>Select Amenities</h4>

                    <div>
                        <?php while($amenity = $amenities->fetch_assoc()): ?>
                            <div>
                                <input type="checkbox"
                                    id="amenity_<?= $amenity['amenity_id']; ?>"
                                    name="amenities[<?= $amenity['amenity_id']; ?>]"
                                    value="<?= $amenity['price']; ?>">
                                <label for="amenity_<?= $amenity['amenity_id']; ?>">
                                    <?= htmlspecialchars($amenity['amenity_name']); ?>
                                    (₱<?= number_format($amenity['price'],2); ?>)
                                </label>
                                <div class="amenity-quantity">
                                    <label for="qty_<?= $amenity['amenity_id']; ?>">Qty:</label>
                                    <input type="number"
                                        id="qty_<?= $amenity['amenity_id']; ?>"
                                        name="quantity[<?= $amenity['amenity_id']; ?>]"
                                        min="1"
                                        value="1">
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <textarea name="extra_requests" placeholder="Special Requests (Optional)"></textarea>

                    <button type="submit">Confirm Reservation</button>
                    <button type="button" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        </div>

    <div class="page-header">
        <h1>Room Options</h1>
    </div>

    <div class="room-top-container">
        <div class="room-filter">
            <button>Filter room options</button>
        </div>
        <div class="room-finder">
            <button>Find me a room</button>
        </div>
    </div>

    <div class="room-cards">
        <?php while($room = $rooms->fetch_assoc()): ?>
            
            <?php 
                $name_parts = explode(' ', $room["room_type"], 2); 
                $first_word = $name_parts[0];
                $second_word = isset($name_parts[1]) ? $name_parts[1] : '';
            ?>

            <div class="room-card">
                

                <img src="<?= $room["image_path"] ?>" alt="Room Image" />
                
                <div class="room-info">
                    <div class="room-price-tag">
                        <span class="currency">PHP</span>
                        <span class="price"><?= number_format($room["price_per_night"], 0); ?></span>
                        <span class="per-night">/night</span>
                    </div>
                    
                    <h3 class="room-title">
                        <span class="main-name"><?= htmlspecialchars($first_word); ?></span>
                        <span class="sub-name"><?= htmlspecialchars($second_word); ?></span>
                    </h3>
                    
                    <div class="room-details">
                        <p><i class="fa-solid fa-bed"></i> Room: <?= htmlspecialchars($room["room_number"]); ?></p>
                        <p><i class="fa-solid fa-users"></i> <?= $room["max_capacity"]; ?> Guests</p>
                    </div>

                    <div class="room-card-actions">
                        <a href="#" class="details-link">See more details</a>
                        <button onclick="openModal(<?= $room['room_id']; ?>)">Book</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php require_once __DIR__ . '/php/footer.php'; ?>

    <script>
    function openModal(roomId){
        const modal = document.getElementById("reserveModal");
        modal.classList.add("show");
        document.getElementById("room_id").value = roomId;
    }

    function closeModal(){
        const modal = document.getElementById("reserveModal");
        modal.classList.remove("show");
    }

    // Close modal when clicking outside of it
    document.getElementById("reserveModal").addEventListener("click", function(event){
        if(event.target === this){
            closeModal();
        }
    });
    </script>
</body>
</html>
</html>