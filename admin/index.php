

<?php
session_start();

if(isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true){
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"]==="POST"){
    require_once "../php/config.php";

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();

        if(md5($password)===$user["password"]){
            session_regenerate_id(true);

            $_SESSION["admin_role"] = $user["role"];
            $_SESSION["admin_username"] = $user["username"];
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_id"] = $user["id"];
            $_SESSION["last_activity"] = time();

            $stmt->close();
            $conn->close();

            session_write_close();
            
            header("Location: /admin/dashboard.php");
            exit();
        }else{
            $error = "Invalid username or password";
        }
    }else{
        $error = "Invalid username or password";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/base.css">
</head>
<body>
    <div class="login-container">
        <?php include '../php/logo.php'; ?>

        <form action="" method="post">

            <?php if(!empty($error)) :?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif;?>

            <div class="input-group">
                <img src="../assets/profile.png" alt="Profile Icon" class="input-icon">
                <input name="email" type="email" placeholder="Enter email">
            </div>

            <div class="input-group">
                <img src="../assets/password.png" alt="Password Icon" class="input-icon">
                <input name="password" type="password" placeholder="Enter password">
            </div>

            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>
</html>