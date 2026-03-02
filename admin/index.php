

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

        if($password===$user["password"]){
            session_regenerate_id(true);

            $_SESSION["admin_role"] = $user["role"];
            $_SESSION["admin_username"] = $user["username"];
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_id"] = $user["id"];
            $_SESSION["last_activity"] = time();

            $stmt->close();
            $conn->close();

            session_write_close();
            
            header("Location: dashboard.php");
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
    <title>Admin Login | Rawís Resort Hotel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login">
    <div class="login-container">
        <div class="logo-header">
            <?php include '../php/logo.php'; ?>
            <p class="subtitle">Please enter details below.</p>
        </div>

        <form action="" method="post">
            <?php if(!empty($error)) :?>
                <div class="error-box">
                    <?php echo $error; ?>
                </div>
            <?php endif;?>

            <div class="input-group">
                <i class="fas fa-user icon"></i>
                <div class="divider"></div>
                <input name="email" type="email" placeholder="Username" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <div class="divider"></div>
                <input name="password" type="password" id="loginPass" placeholder="Password" required>
                <i class="fas fa-eye eye-toggle" onclick="togglePass()"></i>
            </div>

            <button type="submit" class="login-button">Login</button>
            
            <a href="#" class="forgot-link">Forgot password?</a>
        </form>
    </div>

    <script>
        function togglePass() {
            const passField = document.getElementById("loginPass");
            passField.type = passField.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>