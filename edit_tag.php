<?php
session_start();
require "autorisation.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tag_id = $_GET['id'];
$tag_name = '';

if ($data = $connection->prepare("SELECT tag_name FROM tags WHERE tag_id = ?")) {
    $data->bind_param("i", $tag_id);
    $data->execute();
    $data->bind_result($tag_name);
    $data->fetch();
    $data->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tag_name = $_POST['tag_name'];

    if ($update_data = $connection->prepare("UPDATE tags SET tag_name = ? WHERE tag_id = ?")) {
        $update_data->bind_param("si", $tag_name, $tag_id);
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
    <title>Edit Tag</title>
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="main_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="form-container">
        <h2>Edit Tag</h2>
        <form method="post">
            <label for="tag_name">Tag Name:</label>
            <input type="text" name="tag_name" value="<?php echo htmlspecialchars($tag_name); ?>" required><br>
            <button type="submit">Update Tag</button>
        </form>
    </div>
</body>
</html>
