<header class="header">
    <link rel="stylesheet" href="assets/css/header-footer.css">
    <div class="header-inner">

        <div class="header-logo">
            <a href="index.php">
                <?php
                    $showImage = true;
                    $showText  = true;
                    require_once __DIR__ . '/logo.php';
                ?>
            </a>
        </div>

        <?php
    // determine the current script name for active link highlighting
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
        <nav class="nav-menu">
            <ul>
                <li><a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="rooms.php" class="<?= $currentPage === 'rooms.php' ? 'active' : '' ?>">Rooms</a></li>
                <li><a href="amenities.php" class="<?= $currentPage === 'amenities.php' ? 'active' : '' ?>">Amenities</a></li>
            </ul>
        </nav>

    </div>
</header>
