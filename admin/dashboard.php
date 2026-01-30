<!-- 
 Dashboard hit admin
-->
 <?php 
 require_once "../php/admin_auth.php";
 require_once "../php/config.php";
 
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

    <a href="/admin/logout.php">Logout</a>
    
</body>
</html>