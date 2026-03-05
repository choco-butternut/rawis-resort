<?php
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

$today = date('Y-m-d');

$arrivals_today = $conn->query("
    SELECT COUNT(*) AS c FROM reservations
    WHERE check_in_date = '$today'
    AND reservation_status = 'Confirmed'
")->fetch_assoc()["c"];

$departures_today = $conn->query("
    SELECT COUNT(*) AS c FROM reservations
    WHERE check_out_date = '$today'
    AND reservation_status IN ('Confirmed', 'Completed')
")->fetch_assoc()["c"];

$pending_count = $conn->query("
    SELECT COUNT(*) AS c FROM reservations WHERE reservation_status='Pending'
")->fetch_assoc()["c"];

$available_rooms = $conn->query("
    SELECT COUNT(*) AS c FROM rooms WHERE room_status='available'
")->fetch_assoc()["c"];

$occupied_rooms = $conn->query("
    SELECT COUNT(*) AS c FROM rooms WHERE room_status='occupied'
")->fetch_assoc()["c"];

$maintenance_rooms = $conn->query("
    SELECT COUNT(*) AS c FROM rooms WHERE room_status='maintenance'
")->fetch_assoc()["c"];

$sales_today = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE DATE(p.payment_date) = '$today'
      AND p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$sales_week = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE p.payment_date >= DATE_SUB('$today', INTERVAL 7 DAY)
      AND p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$sales_month = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE YEAR(p.payment_date)  = YEAR('$today')
      AND MONTH(p.payment_date) = MONTH('$today')
      AND p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$sales_all = $conn->query("
    SELECT COALESCE(SUM(p.amount_paid), 0) AS t
    FROM payments p
    JOIN reservations r ON r.reservation_id = p.reservation_id
    WHERE p.payment_status = 'Completed'
      AND r.reservation_status != 'Cancelled'
")->fetch_assoc()["t"];

$recent_res = $conn->query("
    SELECT r.reservation_id, u.first_name, u.last_name,
           rm.room_type, r.check_in_date, r.check_out_date, r.reservation_status
    FROM reservations r
    JOIN users u  ON r.guest_id = u.id
    JOIN rooms rm ON r.room_id  = rm.room_id
    ORDER BY r.created_at DESC
    LIMIT 5
");

$awaiting_count = $conn->query("
    SELECT COUNT(*) AS c FROM payments WHERE payment_status='Awaiting Verification'
")->fetch_assoc()["c"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin</title>
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="section-title">
            <h1>DASHBOARD</h1>
            <hr class="header-line">
        </div>

        <div class="dashboard-grid">

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                <span class="stat-label">Arrivals Today</span>
                <span class="stat-value"><?= $arrivals_today; ?></span>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                <span class="stat-label">Departures Today</span>
                <span class="stat-value"><?= $departures_today; ?></span>
            </div>

            <div class="stat-card" style="cursor:pointer" onclick="window.location.href='reservation.php'">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <span class="stat-label">Pending Reservations</span>
                <span class="stat-value"><?= $pending_count; ?></span>
            </div>

            <?php if ($awaiting_count > 0): ?>
            <div class="stat-card" style="cursor:pointer;border:2px solid #93c5fd"
                 onclick="window.location.href='reservation.php'">
                <div class="stat-icon" style="color:#1d4ed8"><i class="fas fa-credit-card"></i></div>
                <span class="stat-label">Awaiting Payment Verification</span>
                <span class="stat-value" style="color:#1d4ed8"><?= $awaiting_count; ?></span>
            </div>
            <?php endif; ?>

            <div class="status-summary-card">
                <h3>Room Status</h3>
                <hr>
                <ul>
                    <li>
                        Available:
                        <strong>
                            <a href="rooms.php" style="color:inherit;text-decoration:none">
                                <?= $available_rooms; ?>
                            </a>
                        </strong>
                    </li>
                    <li>
                        Occupied:
                        <strong><?= $occupied_rooms; ?></strong>
                    </li>
                    <li>
                        Maintenance:
                        <strong><?= $maintenance_rooms; ?></strong>
                    </li>
                </ul>
            </div>

        </div>

        <div class="dashboard-lower-section">

            <div class="recent-res">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <h1 class="reservations-head">Recent Reservations</h1>
                    <a href="reservation.php"
                       style="font-size:13px;color:#1d4ed8;text-decoration:none;font-weight:600">
                        View all →
                    </a>
                </div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_res->fetch_assoc()): ?>
                        <tr style="cursor:pointer" onclick="window.location.href='reservation.php'">
                            <td class="id-column">#<?= $row["reservation_id"]; ?></td>
                            <td class="bold-text"><?= htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?></td>
                            <td><?= htmlspecialchars($row["room_type"]); ?></td>
                            <td><?= date("M d, Y", strtotime($row["check_in_date"])); ?></td>
                            <td>
                                <span class="status-text <?= strtolower(str_replace(' ','-',$row["reservation_status"])); ?>">
                                    <?= $row["reservation_status"]; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="sales-overview-card">
                <div class="sales-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Sales Overview</h3>
                    <p>Completed payments, excluding cancelled reservations.</p>
                </div>
                <div class="sales-metrics">
                    <div class="metric">
                        <span>Today</span>
                        <strong>₱<?= number_format($sales_today, 2); ?></strong>
                    </div>
                    <div class="metric">
                        <span>This Week</span>
                        <strong>₱<?= number_format($sales_week, 2); ?></strong>
                    </div>
                    <div class="metric">
                        <span>This Month</span>
                        <strong>₱<?= number_format($sales_month, 2); ?></strong>
                    </div>
                    <div class="metric" style="border-top:1px solid #e5e7eb;padding-top:10px;margin-top:4px">
                        <span>All Time</span>
                        <strong style="color:#1d4ed8">₱<?= number_format($sales_all, 2); ?></strong>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>