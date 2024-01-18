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

    $data = $connection->prepare("SELECT tag_id, tag_name FROM tags WHERE user_id = ?");
    $data->bind_param("i", $_SESSION["user_id"]);
    $data->execute();
    $tags_result = $data->get_result();

    echo "<div class='transactions-container'>";
    if ($tags_result->num_rows > 0) {
        echo "<h2 class='centered-title'>User's Tags</h2>";
        echo "<table class='transactions-table'><tr><th>Name</th><th>Action</th></tr>";
        while ($row = $tags_result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row["tag_name"]) . "</td>
                    <td>
                        <a href='delete_tag.php?id=" . $row["tag_id"] . "' onclick='return confirm(\"Are you sure you want to delete this tag?\");'>Delete</a>
                        <a href='edit_tag.php?id=" . $row["tag_id"] . "'>Edit</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tags found.</p>";
    }
    echo "</div>";

    $report_result = null;
    $totalExpense = 0;
    $selected_currency = '';
    if (isset($_POST['generate_report']) && isset($_POST['currency'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $selected_currency = $_POST['currency'];
    
        $sql = "SELECT t.amount, t.type, COALESCE(tg.tag_name, 'No Tag') AS tag_name FROM transactions t LEFT JOIN tags tg ON t.tag_id = tg.tag_id WHERE t.user_id = ? AND t.currency = ? AND DATE(t.date) BETWEEN ? AND ? AND t.type = 'expense'";
        $params = [$_SESSION["user_id"], $selected_currency, $start_date, $end_date];
    
        $report_data = $connection->prepare($sql);
        $report_data->bind_param("ssss", ...$params);
        $report_data->execute();
        $report_result = $report_data->get_result();
    
        while ($row = $report_result->fetch_assoc()) {
            $totalExpense += $row['amount'];
        }
        $report_result->data_seek(0);
    }
    
    
?>
    <div class="form-container">
        <h3 class='centered-title'>Generate Report</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" required><br>

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" required><br>

        <label for="currency">Currency:</label>
        <select name="currency" required>
            <option value="EUR">Euro (EUR)</option>
            <option value="USD">US Dollar (USD)</option>
            <option value="BGN">Bulgarian Lev (BGN)</option>
        </select><br>

            <button type="submit" name="generate_report">Generate Report</button>
        </form>
    </div>
    <?php if (isset($report_result) && $report_result->num_rows > 0 && !empty($selected_currency)) : ?>
    <div class="form-container">
        <div class="total-report">
            <h3 class='centered-title'>Generated Report</h3>
            <p>Total Expenditure in <?php echo htmlspecialchars($selected_currency); ?>: <?php echo $totalExpense; ?></p>
        </div>
    </div>
    <div class="d-flex justify-content-center align-items-center" style="height: 60vh;">
        <div class="chart-container">
            <canvas id="reportChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        const chartData = {
            labels: [],
            datasets: [{
                label: 'Expense Data',
                data: [],
                backgroundColor: [],
                hoverBackgroundColor: []
            }]
        };

        const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#4D5360'];
        let colorIndex = 0;

        <?php while ($row = $report_result->fetch_assoc()) : ?>
            if ("<?php echo $row['type']; ?>" === "expense") {
                chartData.labels.push("<?php echo $row['tag_name']; ?>");
                chartData.datasets[0].data.push(<?php echo $row['amount']; ?>);
                chartData.datasets[0].backgroundColor.push(colors[colorIndex % colors.length]);
                chartData.datasets[0].hoverBackgroundColor.push(colors[colorIndex % colors.length]);
                colorIndex++;
            }
        <?php endwhile; ?>

        const myPieChart = new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
<?php endif; 

$data->close();
$connection->close();
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
