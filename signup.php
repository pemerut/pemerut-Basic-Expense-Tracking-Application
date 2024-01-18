<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="main_style.css">
</head>
<body>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        require "autorisation.php";

        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        $data = $connection->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $data->bind_param("ss", $username, $email);
        $data->execute();
        $result = $data->get_result();

        if ($result->num_rows > 0) {
            echo "<script>$(function(){toastr.error('Please try again with different credentials.', 'Username or Email already in use.')});</script>";
        } else {
            if ($password === $confirm_password) {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);


                $data = $connection->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $data->bind_param("sss", $username, $email, $hashed_password);
                $data->execute();

                if ($data->affected_rows > 0) {
                    echo "<script>$(function(){toastr.success('Registration successful!')});</script>";
                } else {
                    echo "<script>$(function(){toastr.error('Error: Could not register.')});</script>";
                }

                $data->close();
            } else {
                echo "<script>$(function(){toastr.error('Passwords do not match')});</script>";
            }
        }
        $connection->close();
    }
    ?>
    <div class="form-container">
        <h2>User Registration</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Name:</label>
            <input type="text" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <label for="confirm_password">Repeat Password:</label>
            <input type="password" name="confirm_password" required><br>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

</body>
</html>
