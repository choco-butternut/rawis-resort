<!-- 
 Dashboard hit admin
-->
 <?php 
require_once __DIR__ . "/../php/config.php";
require_once __DIR__ . "/../php/admin_auth.php";
 
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <div>
        <?php echo $admin_username ?>
    </div>

   <a href= dashboard.php>Dashboard</a>
    <a href="rooms.php">Rooms</a>
    <a href="reservation.php">Reservations</a>
    <a href="amenities.php">Amenities</a>
    <a href="logout.php">Logout</a>
    <br>
    
    
</body>
</html>