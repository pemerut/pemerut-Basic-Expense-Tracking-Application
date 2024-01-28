<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="main_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
    <?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("Location: login.php");
        exit;
    }

    require "autorisation.php";
    include "navbar.php";

    function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_tag"])) {
        $tag_name = clean_input($_POST["tag_name"]);
        $user_id = $_SESSION["user_id"];
    
        $check = $connection->prepare("INSERT INTO tags (user_id, tag_name) VALUES (?, ?)");
        $check->bind_param("is", $user_id, $tag_name);
        $check->execute();
    
        if ($check->affected_rows > 0) {
            echo "<script>$(function(){toastr.success('Tag added successfully!')});</script>";
        } else {
            echo "<script>$(function(){toastr.error('Error: Could not add the tag.')});</script>";
        }
    
        $check->close();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_expense"])) {
        $date = $_POST["date"];
        $amount = $_POST["amount"];
        $tag_id = $_POST["tag"] ? $_POST["tag"] : NULL;
        $user_id = $_SESSION["user_id"];
        $currency = $_POST["currency"];
        $type = $_POST["type"];
    
        $check = $connection->prepare("INSERT INTO transactions (user_id, tag_id, type, currency, date, amount) VALUES (?, ?, ?, ?, ?, ?)");
        $check->bind_param("iisssd", $user_id, $tag_id, $type, $currency, $date, $amount);
        $check->execute();
    
        if ($check->affected_rows > 0) {
            echo "<script>$(function(){toastr.success('Transaction added successfully!')});</script>";
        } else {
            echo "<script>$(function(){toastr.error('Error: Could not add the transaction.')});</script>";
        }
    
        $check->close();
    }

    $tags_check = $connection->prepare("SELECT tag_id, tag_name FROM tags WHERE user_id = ?");
    $tags_check->bind_param("i", $_SESSION["user_id"]);
    $tags_check->execute();
    $tags_result = $tags_check->get_result();
    ?>
    <h2 class="centered-title">Welcome, User!</h2>
    
    <div class="form-container">
        <h3  class="centered-title">Add New Tag</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="tag_name">Tag Name:</label>
            <input type="text" name="tag_name" required><br>

            <button type="submit" name="add_tag">Add Tag</button>
        </form>
    </div>

    <div class="form-container">
    <h3 class="centered-title">Add Expense</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="date">Date:</label>
            <input type="date" name="date" required><br>
            <label for="amount">Amount:</label>
            <input type="number" name="amount" step="0.01" required><br>

            <label for="type">Type:</label>
            <select name="type">
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select><br>

            <label for="currency">Currency:</label>
            <select name="currency">
                <option value="EUR">Euro (EUR)</option>
                <option value="USD">US Dollar (USD)</option>
                <option value="BGN">Bulgarian Lev (BGN)</option>
            </select><br>

            <label for="tag">Tag (optional):</label>
            <select name="tag">
            <option value="">None</option>
            <?php
            while ($row = $tags_result->fetch_assoc()) {
                echo "<option value=\"" . $row["tag_id"] . "\">" . $row["tag_name"] . "</option>";
            }
            ?>
        </select><br>

            <button type="submit" name="add_expense">Add Expense</button>
        </form>
    </div>
    <h3 class='centered-title'>Your Transactions</h3>
    <?php
$transactions_check = $connection->prepare("SELECT t.transaction_id, t.date, t.amount, t.currency, t.type, tg.tag_name FROM transactions t LEFT JOIN tags tg ON t.tag_id = tg.tag_id WHERE t.user_id = ?");
$transactions_check->bind_param("i", $_SESSION["user_id"]);
$transactions_check->execute();
$transactions_result = $transactions_check->get_result();

if ($transactions_result->num_rows > 0) {
    echo "<table class='transactions-table'><tr><th>Tag</th><th>Amount</th><th>Currency</th><th>Type</th><th>Date</th><th>Action</th></tr>";
    while ($row = $transactions_result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["tag_name"] ?? 'None') . "</td>
                <td>" . htmlspecialchars($row["amount"]) . "</td>
                <td>" . htmlspecialchars($row["currency"]) . "</td>
                <td>" . htmlspecialchars($row["type"]) . "</td>
                <td>" . htmlspecialchars($row["date"]) . "</td>
                <td>
                    <a href='delete_transaction.php?id=" . $row["transaction_id"] . "' onclick='return confirm(\"Are you sure you want to delete this transaction?\");'>Delete</a>
                    <a href='edit_transaction.php?id=" . $row["transaction_id"] . "'>Edit</a>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No transactions recorded.</p>";
}

$transactions_check->close();
$connection->close();
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
