<?php
session_start();
require "autorisation.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$transaction_id = $_GET['id'];
$user_id = $_SESSION["user_id"];

$data = $connection->prepare("DELETE FROM transactions WHERE transaction_id = ? AND user_id = ?");
$data->bind_param("ii", $transaction_id, $user_id);
$data->execute();

header("Location: index.php");
exit;
?>
