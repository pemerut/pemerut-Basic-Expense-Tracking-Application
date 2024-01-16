<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="main_style.css">
</head>
<body>
    <div class="top-right-buttons">
        <button onclick="window.location.href='login.php'">Sign Out</button>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) : ?>
            <button onclick="window.location.href='admin_page.php'">Go to Admin Page</button>
        <?php endif; ?>
    </div>

    <h2 class="centered-title">Welcome, User!</h2>

    <?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 0);

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("Location: login.php");
        exit;
    }

    require "autorisation.php";

    function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_tag"])) {
        $tag_name = clean_input($_POST["tag_name"]);
        $tag_type = $_POST["tag_type"];
        $currency = isset($_POST["currency"]) ? $_POST["currency"] : 'EUR';
        $user_id = $_SESSION["user_id"];
    
        $check = $connection->prepare("INSERT INTO tags (user_id, tag_name, type, currency) VALUES (?, ?, ?, ?)");
        $check->bind_param("isss", $user_id, $tag_name, $tag_type, $currency);
        $check->execute();
    
        if ($check->affected_rows > 0) {
            echo "<script>alert('Tag added successfully!');</script>";
        } else {
            echo "<script>alert('Error: Could not add the tag.');</script>";
        }
    
        $check->close();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_expense"])) {
        $date = $_POST["date"];
        $amount = $_POST["amount"];
        $tag_id = $_POST["tag"];
        $user_id = $_SESSION["user_id"];
    
        $check = $connection->prepare("INSERT INTO transactions (user_id, tag_id, date, amount) VALUES (?, ?, ?, ?)");
        $check->bind_param("iisd", $user_id, $tag_id, $date, $amount);
        $check->execute();
    
        if ($check->affected_rows > 0) {
            echo "<script>alert('Transaction added successfully!');</script>";
        } else {
            echo "<script>alert('Error: Could not add the transaction.');</script>";
        }
    
        $check->close();
    }

    $tags_check = $connection->prepare("SELECT tag_id, tag_name FROM tags WHERE user_id = ? AND type = 'expense'");
    $tags_check->bind_param("i", $_SESSION["user_id"]);
    $tags_check->execute();
    $tags_result = $tags_check->get_result();
    ?>
    
    <div class="form-container">
        <h3>Add New Tag</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="tag_name">Tag Name:</label>
            <input type="text" name="tag_name" required><br>

            <label for="tag_type">Tag Type:</label>
            <select name="tag_type">
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select><br>
            <label for="currency">Currency:</label>
            <select name="currency">
                <option value="EUR" selected>Euro (EUR)</option>
                <option value="USD">US Dollar (USD)</option>
                <option value="BGN">Bulgarian Lev (BGN)</option>
            </select><br>

            <button type="submit" name="add_tag">Add Tag</button>
        </form>
    </div>

    <h3 class="centered-title">Add Expense</h3>
    <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="date">Date:</label>
            <input type="date" name="date" required><br>
            <label for="amount">Amount:</label>
            <input type="number" name="amount" step="0.01" required><br>

            <label for="tag">Tag:</label>
            <select name="tag">
                <?php
                while($row = $tags_result->fetch_assoc()) {
                    echo "<option value=\"" . $row["tag_id"] . "\">" . $row["tag_name"] . "</option>";
                }
                ?>
            </select><br>

            <button type="submit" name="add_expense">Add Expense</button>
        </form>
    </div>
    <div class="form-container" style="text-align: center;">
        <button onclick="window.location.href='user_data.php'">Go to User Data</button>
    </div>
<?php

echo "<h3 class='centered-title'>Your Transactions</h3>";

$transactions_check = $connection->prepare("SELECT t.transaction_id, t.date, t.amount, tg.tag_name, tg.currency FROM transactions t JOIN tags tg ON t.tag_id = tg.tag_id WHERE t.user_id = ?");
$transactions_check->bind_param("i", $_SESSION["user_id"]);
$transactions_check->execute();
$transactions_result = $transactions_check->get_result();

if ($transactions_result->num_rows > 0) {
    echo "<table class='transactions-table'><tr><th>Tag</th><th>Amount</th><th>Currency</th><th>Date</th><th>Action</th></tr>";
    while($row = $transactions_result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["tag_name"]) . "</td>
                <td>" . htmlspecialchars($row["amount"]) . "</td>
                <td>" . htmlspecialchars($row["currency"]) . "</td> <!-- Displaying currency -->
                <td>" . htmlspecialchars($row["date"]) . "</td>
                <td><a href='delete_transaction.php?id=" . $row["transaction_id"] . "' onclick='return confirm(\"Are you sure you want to delete this transaction?\");'>Delete</a></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No transactions recorded.</p>";
}

$transactions_check->close();
$connection->close();
?>

</body>
</html>
