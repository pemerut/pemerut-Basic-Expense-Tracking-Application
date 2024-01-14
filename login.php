<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
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
    <h2>User Login</h2>

    <?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        require "autorisation.php";

        $username = $_POST["username"];
        $password = $_POST["password"];

        $data = $connection->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $data->bind_param("s", $username);
        $data->execute();
        $result = $data->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {

                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["username"] = $username;

                header("Location: main.php");
                exit;
            } else {
                echo "<script>alert('Invalid password. Try again.');</script>";
            }
        } else {
            echo "<script>alert('Username does not exist.');</script>";
        }

        $data->close();
        $connection->close();
    }
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="signup.php">Signup here</a>.</p>

</body>
</html>