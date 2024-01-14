<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        /* Saw it in tiktok */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            text-align: center;
        }
        form {
            display: inline-block;
            margin-top: 20px;
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 4px;
        }
        button:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <h2>User Registration</h2>

    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);

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
            echo "<script>alert('Username or Email already in use. Please try again with different credentials.');</script>";
        } else {
            if ($password === $confirm_password) {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);


                $data = $connection->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $data->bind_param("sss", $username, $email, $hashed_password);
                $data->execute();

                if ($data->affected_rows > 0) {
                    echo "<script>alert('Registration successful!');</script>";
                } else {
                    echo "<script>alert('Error: Could not register.');</script>";
                }

                $data->close();
            } else {
                echo "<script>alert('Passwords do not match');</script>";
            }
        }
        $connection->close();
    }
    ?>
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

    <script>
        window.onload = function() {
            <?php if ($show_alert): ?>
                alert('Registration successful!');
            <?php endif; ?>
        };
    </script>
</body>
</html>