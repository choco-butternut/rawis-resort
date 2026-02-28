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
           u.first_name, u.last_name, u.phone_number,
           rm.room_number, rm.room_type, rm.price_per_night,
           (rm.price_per_night * DATEDIFF(r.check_out_date, r.check_in_date)) as base_amount
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
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="section-title">
            <h1>Reservations</h1>
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
                <a href="#" class="active">All</a>
                <a href="#">Arrivals</a>
                <a href="#">Departures</a>
                <a href="#">Pending</a>
            </div>

        </div>


        <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest Name</th>
                    <th>Contact</th>
                    <th>Room Details</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $reservations->fetch_assoc()): ?>
                <tr>
                    <td class="id-column">#<?= $row["reservation_id"]; ?></td>
                    <td class="bold-text">
                        <a href="#" class="guest-link" 
                           data-id="<?= $row["reservation_id"]; ?>"
                           data-name="<?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?>"
                           data-phone="<?= htmlspecialchars($row["phone_number"]); ?>"
                           data-room="<?= htmlspecialchars($row["room_number"] . " - " . $row["room_type"]); ?>"
                           data-checkin="<?= date("M d, Y", strtotime($row["check_in_date"])); ?>"
                           data-checkout="<?= date("M d, Y", strtotime($row["check_out_date"])); ?>"
                           data-status="<?= htmlspecialchars($row["reservation_status"]); ?>"
                           onclick="openReservationModal(event)">
                            <?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?>
                        </a>
                    </td>
                    <td>
                        <?= htmlspecialchars($row["phone_number"]); ?><br>
                        <!-- <small><?= htmlspecialchars($row["email"]); ?></small> -->
                    </td>
                    <td>
                        <span class="room-type-tag"><?= htmlspecialchars($row["room_type"]); ?></span>
                        <br><small>Room <?= htmlspecialchars($row["room_number"]); ?></small>
                    </td>
                    <td><?= date("M d, Y", strtotime($row["check_in_date"])); ?></td>
                    <td><?= date("M d, Y", strtotime($row["check_out_date"])); ?></td>
                    <td>
                        <?php 
                            $base = $row["base_amount"];
                            $amenities_total = 0;
                            $res_id = $row["reservation_id"];
                            $amenityQuery = $conn->prepare("
                                SELECT SUM(price * quantity) as total
                                FROM reservation_amenities
                                WHERE reservation_id = ?
                            ");
                            $amenityQuery->bind_param("i", $res_id);
                            $amenityQuery->execute();
                            $amenityResult = $amenityQuery->get_result();
                            if($amenityResult->num_rows > 0) {
                                $a = $amenityResult->fetch_assoc();
                                $amenities_total = $a["total"] ?? 0;
                            }
                            $amenityQuery->close();
                            $total = $base + $amenities_total;
                        ?>
                        ₱<?= number_format($total, 2); ?>
                    </td>
                    <td>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="reservation_id" value="<?= $row["reservation_id"]; ?>">
                            <select name="reservation_status" class="status-select <?= strtolower($row["reservation_status"]); ?>" onchange="this.form.submit()">
                                <option value="Pending" <?= $row["reservation_status"]=='Pending'?'selected':''; ?>>Pending</option>
                                <option value="Confirmed" <?= $row["reservation_status"]=='Confirmed'?'selected':''; ?>>Confirmed</option>
                                <option value="Checked-in" <?= $row["reservation_status"]=='Checked-in'?'selected':''; ?>>Checked-in</option>
                                <option value="Checked-out" <?= $row["reservation_status"]=='Checked-out'?'selected':''; ?>>Checked-out</option>
                                <option value="Cancelled" <?= $row["reservation_status"]=='Cancelled'?'selected':''; ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status">
                        </form>
                    </td>
                    <td class="actions-cell">
                        <div class="action-icons">
                            <a href="#" title="View Details"><i class="fa-solid fa-pen-to-square"></i>
                            <a href="#" title="View Details"><i class="fa-solid fa-message"></i>
                            <a href="reservation.php?delete=<?= $row["reservation_id"]; ?>" 
                               onclick="return confirm('Delete reservation?');" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Reservation Detail Modal -->
     <!-- kaw na bahala here -->
    <div id="reservationModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeReservationModal()">&times;</button>
            <h2>Reservation Details</h2>
            
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Reservation ID</label>
                    <p id="modalId"></p>
                </div>
                <div class="detail-item">
                    <label>Guest Name</label>
                    <p id="modalName"></p>
                </div>
                <div class="detail-item">
                    <label>Contact</label>
                    <p id="modalPhone"></p>
                </div>
                <div class="detail-item">
                    <label>Room Details</label>
                    <p id="modalRoom"></p>
                </div>
                <div class="detail-item">
                    <label>Check-in Date</label>
                    <p id="modalCheckIn"></p>
                </div>
                <div class="detail-item">
                    <label>Check-out Date</label>
                    <p id="modalCheckOut"></p>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <p id="modalStatus"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openReservationModal(event) {
        event.preventDefault();
        const link = event.target;
        
        document.getElementById('modalId').textContent = '#' + link.dataset.id;
        document.getElementById('modalName').textContent = link.dataset.name;
        document.getElementById('modalPhone').textContent = link.dataset.phone;
        document.getElementById('modalRoom').textContent = link.dataset.room;
        document.getElementById('modalCheckIn').textContent = link.dataset.checkin;
        document.getElementById('modalCheckOut').textContent = link.dataset.checkout;
        document.getElementById('modalStatus').textContent = link.dataset.status;
        
        document.getElementById('reservationModal').classList.add('show');
    }

    function closeReservationModal() {
        document.getElementById('reservationModal').classList.remove('show');
    }

    // Close modal when clicking backdrop
    document.getElementById('reservationModal').addEventListener('click', function(evt) {
        if(evt.target === this) {
            closeReservationModal();
        }
    });
    </script>

</body>
</html>
