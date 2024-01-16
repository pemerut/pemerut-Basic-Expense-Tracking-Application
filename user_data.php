<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User's Tags</title>
    <link rel="stylesheet" href="main_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    include 'navbar.php';

    $data = $connection->prepare("SELECT tag_id, tag_name, type, currency FROM tags WHERE user_id = ?");
    $data->bind_param("i", $_SESSION["user_id"]);
    $data->execute();
    $tags_result = $data->get_result();

    echo "<div class='transactions-container'>";
    if ($tags_result->num_rows > 0) {
        echo "<table class='transactions-table'><tr><th>Name</th><th>Type</th><th>Currency</th><th>Action</th></tr>";
        while ($row = $tags_result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row["tag_name"]) . "</td>
                    <td>" . htmlspecialchars($row["type"]) . "</td>
                    <td>" . htmlspecialchars($row["currency"]) . "</td>
                    <td><a href='delete_tag.php?id=" . $row["tag_id"] . "' onclick='return confirm(\"Are you sure you want to delete this tag?\");'>Delete</a></td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tags found.</p>";
    }
    echo "</div>";

    $report_result = null;
    if (isset($_POST['generate_report'])) {
        $report_date = $_POST['report_date'];
        $report_tag = $_POST['report_tag'];
    
        $sql = "SELECT amount, tag_name FROM transactions t JOIN tags tg ON t.tag_id = tg.tag_id WHERE t.user_id = ?";
        $params = [$_SESSION["user_id"]];
    
        if (!empty($report_date)) {
            $sql .= " AND DATE(t.date) = ?";
            $params[] = $report_date;
        }
    
        if (!empty($report_tag) && $report_tag !== 'All Tags') {
            $sql .= " AND t.tag_id = ?";
            $params[] = $report_tag;
        }
    
        $report_data = $connection->prepare($sql);
        $report_data->bind_param(str_repeat("s", count($params)), ...$params);
        $report_data->execute();
        $report_result = $report_data->get_result();
    }
?>
<h2 class="centered-title">User's Tags</h2>
<div class="form-container">
    <h3>Generate Report</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="report_date">Date:</label>
        <input type="date" name="report_date"><br>

        <label for="report_tag">Tag:</label>
        <select name="report_tag">
            <option value="">All Tags</option>
            <?php
            $tags_result->data_seek(0);
            while ($row = $tags_result->fetch_assoc()) {
                echo "<option value=\"" . $row["tag_id"] . "\">" . $row["tag_name"] . "</option>";
            }
            ?>
        </select><br>

        <button type="submit" name="generate_report">Generate Report</button>
    </form>
</div>

<?php if (isset($report_result) && $report_result->num_rows > 0) : ?>
    <div class="chart-container" style="position: relative; height:40vh; width:80vw">
        <canvas id="reportChart"></canvas>
    </div>
    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        const chartData = {
            labels: [],
            datasets: [{
                label: 'Report Data',
                data: [],
                backgroundColor: []
            }]
        };

        <?php while ($row = $report_result->fetch_assoc()) : ?>
            chartData.labels.push("<?php echo $row['tag_name']; ?>");
            chartData.datasets[0].data.push(<?php echo $row['amount']; ?>);
            chartData.datasets[0].backgroundColor.push('randomColorFunction()');
        <?php endwhile; ?>

        new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
<?php endif; ?>

<div class="form-container" style="text-align: center;">
    <button onclick="window.location.href='index.php'">Back to the Main Page</button>
</div>

<?php
$data->close();
$connection->close();
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
