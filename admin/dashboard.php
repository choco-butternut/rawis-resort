<?php 
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";

$rooms_count = $conn->query("SELECT COUNT(*) AS total FROM rooms")
                    ->fetch_assoc()["total"];

$res_count = $conn->query("SELECT COUNT(*) AS total FROM reservations")
                  ->fetch_assoc()["total"];

$pending_count = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE reservation_status='Pending'")
                      ->fetch_assoc()["total"];

$confirmed_count = $conn->query("SELECT COUNT(*) AS total FROM reservations WHERE reservation_status='Confirmed'")
                        ->fetch_assoc()["total"];

$amenities_count = $conn->query("SELECT COUNT(*) AS total FROM amenities")
                        ->fetch_assoc()["total"];

$users_count = $conn->query("SELECT COUNT(*) AS total FROM users")
                    ->fetch_assoc()["total"];

                    

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
                <span class="stat-value">3</span> </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                <span class="stat-label">Departures Today</span>
                <span class="stat-value">4</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <span class="stat-label">Pending Reservations</span>
                <span class="stat-value"><?php echo $pending_count; ?></span>
            </div>

            <div class="status-summary-card">
                <h3>Room Status</h3>
                <hr>
                <ul>
                    <li>Available Rooms: <strong><?php echo $rooms_count; ?></strong></li>
                    <li>Occupied Rooms: <strong><?php echo $confirmed_count; ?></strong></li>
                    <li>Cleaning: <strong>1</strong></li>
                    <li>Maintenance: <strong>0</strong></li>
                </ul>
            </div>
        </div>


        <div class="dashboard-lower-section">
            <div class="recent-res">
                <h1 class="reservations-head">Reservations</h1>
                <div class="search-add">
                    <div class="search-bar">
                        <input type="text" placeholder="Search">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn-add"><i class="fas fa-plus"></i> Add</button>
                </div>
                <div class="table-header-row">
                    <div class="status-filters">
                        <a href="#" class="active">All</a>
                        <a href="#">Arrivals</a>
                        <a href="#">Departures</a>
                    </div>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Room</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_res->fetch_assoc()): ?>
                        <tr>
                            <td class="id-column">#<?php echo $row["reservation_id"]; ?></td>
                            <td class="bold-text"><?php echo htmlspecialchars($row["username"]); ?></td>
                            <td><?php echo htmlspecialchars($row["room_type"]); ?></td>
                            <td><?php echo date("M d, Y", strtotime($row["check_in_date"])); ?></td>
                            <td><span class="status-text <?php echo strtolower($row["reservation_status"]); ?>"><?php echo $row["reservation_status"]; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="sales-overview-card">
                <div class="sales-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Sales Overview</h3>
                    <p>Overview of current sales performance.</p>
                </div>
                <div class="sales-metrics">
                    <div class="metric">
                        <span>Today</span>
                        <strong>₱15,200</strong>
                    </div>
                    <div class="metric">
                        <span>This Week</span>
                        <strong>₱82,300</strong>
                    </div>
                    <div class="metric">
                        <span>This Month</span>
                        <strong>₱210,450</strong>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>