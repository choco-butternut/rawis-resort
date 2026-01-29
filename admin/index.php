
<!-- 
 
Login para hit admin page

-->

<?php
session_start();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>
<body>
    <form action="" method="post">

        <input name="email" type="email" placeholder="Enter email">
        <input name="password" type="password" placeholder="Enter password">

        <button type="submit">Login</button>
    </form>
</body>
</html>