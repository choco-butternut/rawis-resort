<header class="header">
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

        <nav class="nav-menu">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="">Facilities</a></li>
                <li><a href="amenities.php">Amenities</a></li>
            </ul>
        </nav>

    </div>
</header>
