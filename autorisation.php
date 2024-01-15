<?php
session_start();
require "autorisation.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$tag_id = $_GET['id'];
$user_id = $_SESSION["user_id"];

$data = $connection->prepare("DELETE FROM tags WHERE tag_id = ? AND user_id = ?");
$data->bind_param("ii", $tag_id, $user_id);
$data->execute();

header("Location: user_data.php");
exit;
?>
