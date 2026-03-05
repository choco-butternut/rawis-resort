<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin.css">

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>


<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <a href="dashboard.php">
            <?php
                $showImage = true;
                $showText  = true;
                require_once __DIR__ . '/../php/logo.php'; 
            ?>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            </li>
            <li class="<?= ($current_page == 'reservation.php') ? 'active' : ''; ?>">
                <a href="reservation.php"><i class="fas fa-calendar-check"></i> Reservations</a>
            </li>
            <li class="<?= ($current_page == 'rooms.php') ? 'active' : ''; ?>">
                <a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a>
            </li>
            <!-- <li class="<?= ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a>
            </li> -->
            <!-- <li class="<?= ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
            </li> -->
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>