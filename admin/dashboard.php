<?php 
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

/* ===============================
   DASHBOARD COUNTS
=================================*/

// Total rooms
$rooms_count = $conn->query("SELECT COUNT(*) AS total FROM rooms")
                    ->fetch_assoc()["total"];

// Total reservations
$res_count = $conn->query("SELECT COUNT(*) AS total FROM reservations")
                  ->fetch_assoc()["total"];

// Pending reservations
$pending_count = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE reservation_status='Pending'")
                      ->fetch_assoc()["total"];

// Confirmed reservations
$confirmed_count = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE reservation_status='Confirmed'")
                        ->fetch_assoc()["total"];

// Total amenities
$amenities_count = $conn->query("SELECT COUNT(*) AS total FROM amenities")
                        ->fetch_assoc()["total"];

// Total users
$users_count = $conn->query("SELECT COUNT(*) AS total FROM users")
                    ->fetch_assoc()["total"];


/* ===============================
   RECENT RESERVATIONS
=================================*/

$recent_res = $conn->query("
    SELECT r.reservation_id, u.username, rm.room_type, 
           r.check_in_date, r.check_out_date, r.reservation_status
    FROM reservations r
    JOIN users u ON r.guest_id = u.id
    JOIN rooms rm ON r.room_id = rm.room_id
    ORDER BY r.created_at DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <body>
    <h1>Admin Dashboard</h1>
    <p>Welcome, <strong><?php echo htmlspecialchars($admin_username); ?></strong></p>

    <hr>

    <h2>System Overview</h2>

    <div style="display:flex; gap:20px; flex-wrap:wrap;">

        <div style="border:1px solid #ccc; padding:15px; width:200px;">
            <h3>Total Rooms</h3>
            <p><?php echo $rooms_count; ?></p>
        </div>

        <div style="border:1px solid #ccc; padding:15px; width:200px;">
            <h3>Total Reservations</h3>
            <p><?php echo $res_count; ?></p>
        </div>

        <div style="border:1px solid #ccc; padding:15px; width:200px;">
            <h3>Pending Reservations</h3>
            <p><?php echo $pending_count; ?></p>
        </div>

        <div style="border:1px solid #ccc; padding:15px; width:200px;">
            <h3>Confirmed Reservations</h3>
            <p><?php echo $confirmed_count; ?></p>
        </div>

        <div style="border:1px solid #ccc; padding:15px; width:200px;">
            <h3>Total Amenities</h3>
            <p><?php echo $amenities_count; ?></p>
        </div>

        <div style="border:1px solid #ccc; padding:15px; width:200px;">
            <h3>Total Users</h3>
            <p><?php echo $users_count; ?></p>
        </div>

    </div>

    <hr>

    <h2>Recent Reservations</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Room</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
        </tr>

        <?php while($row = $recent_res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row["reservation_id"]; ?></td>
            <td><?php echo htmlspecialchars($row["username"]); ?></td>
            <td><?php echo htmlspecialchars($row["room_name"]); ?></td>
            <td><?php echo $row["check_in_date"]; ?></td>
            <td><?php echo $row["check_out_date"]; ?></td>
            <td><?php echo $row["reservation_status"]; ?></td>
        </tr>
        <?php endwhile; ?>

    </table>

    <hr>

    <a href="dashboard.php">Dashboard</a>
    <a href="rooms.php">Rooms</a>
    <a href="reservation.php">Reservations</a>
    <a href="amenities.php">Amenities</a>
    <a href="logout.php">Logout</a>

</body>
    
</body>
</html>