<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="main_style.css">
</head>
<body>
<?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        require "autorisation.php";

        $username = $_POST["username"];
        $password = $_POST["password"];

        $data = $connection->prepare("SELECT user_id, username, password, is_admin FROM users WHERE username = ?");
        $data->bind_param("s", $username);
        $data->execute();
        $result = $data->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {

                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["username"] = $username;
                $_SESSION["is_admin"] = $row["is_admin"];

                header("Location: index.php");
                exit;
            } else {
                echo "<script>$(function(){toastr.error('Invalid password. Try again.')});</script>";
            }
        } else {
            echo "<script>$(function(){toastr.error('Username does not exist.')});</script>";
        }

        $data->close();
        $connection->close();
    }
    ?>
    <div class="form-container">
        <h2>User Login</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Username:</label>
            <input type="text" name="username" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Signup here</a>.</p>
    </div>
</body>
</html>
