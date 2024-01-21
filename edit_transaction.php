<?php
session_start();
require "autorisation.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$transaction_id = $_GET['id'];
$amount = $date = '';

if ($data = $connection->prepare("SELECT amount, date, type, currency FROM transactions WHERE transaction_id = ?")) {
    $data->bind_param("i", $transaction_id);
    $data->execute();
    $data->bind_result($amount, $date, $type, $currency);
    $data->fetch();
    $data->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    $currency = $_POST['currency'];

    if ($update_data = $connection->prepare("UPDATE transactions SET amount = ?, date = ?, type = ?, currency = ? WHERE transaction_id = ?")) {
        $update_data->bind_param("dsssi", $amount, $date, $type, $currency, $transaction_id);
        if ($update_data->execute()) {
            header("Location: user_data.php");
        } else {
            echo "Error updating record: " . $connection->error;
        }
        $update_data->close();
    }
    $connection->close();
}
?>

<!-------------------------------------------------------->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="main_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="form-container">
        <h2>Edit Transaction</h2>
        <form method="post">
            <label for="amount">Amount:</label>
            <input type="number" name="amount" value="<?php echo htmlspecialchars($amount); ?>" required><br>

            <label for="date">Date:</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required><br>

            <label for="type">Type:</label>
            <select name="type" required>
                <option value="income" <?php echo $type == 'income' ? 'selected' : ''; ?>>Income</option>
                <option value="expense" <?php echo $type == 'expense' ? 'selected' : ''; ?>>Expense</option>
            </select><br>

            <label for="currency">Currency:</label>
            <select name="currency" required>
                <option value="EUR" <?php echo $currency == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                <option value="USD" <?php echo $currency == 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                <option value="BGN" <?php echo $currency == 'BGN' ? 'selected' : ''; ?>>Bulgarian Lev (BGN)</option>
            </select><br>
                    
            <button type="submit">Update Transaction</button>
        </form>
    </div>
</body>
</html>
