<?php
// Connect to DB
$conn = mysqli_connect("localhost", "root", "", "test_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get selected user from dropdown
$selected_user = $_GET['user'] ?? '';

// Get distinct users for dropdown
$user_result = mysqli_query($conn, "SELECT DISTINCT name FROM messages");

// Build WHERE clause
$where = "";
if (!empty($selected_user)) {
    $safe_user = mysqli_real_escape_string($conn, $selected_user);
    $where = "WHERE name = '$safe_user'";
}

// Daily summary query
$sql_day = "SELECT date_today, SUM(TIME_TO_SEC(TIMEDIFF(end, start))) AS total_seconds 
            FROM messages 
            $where 
            GROUP BY date_today 
            ORDER BY date_today DESC";
$result_day = mysqli_query($conn, $sql_day);

// Weekly summary query
$sql_week = "SELECT weekno, SUM(TIME_TO_SEC(TIMEDIFF(end, start))) AS total_seconds 
             FROM messages 
             $where 
             GROUP BY weekno 
             ORDER BY weekno DESC";

// Helper: Format duration in hours and minutes
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return "$hours h $minutes m";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Summary Report</title>
    <style>
        table { border-collapse: collapse; width: 50%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Summary Report</h2>

    <!-- Dropdown filter -->
    <form method="GET">
        <label>Select User:</label>
        <select name="user" onchange="this.form.submit()">
            <option value="">All</option>
            <?php while ($user = mysqli_fetch_assoc($user_result)): ?>
                <option value="<?= htmlspecialchars($user['name']) ?>" <?= ($selected_user == $user['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- Daily Summary -->
    <h3>Daily Summary</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Total Duration</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result_day)): ?>
            <tr>
                <td><?= htmlspecialchars($row['date_today']) ?></td>
                <td><?= formatDuration($row['total_seconds']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Weekly Summary -->
    <?php $result_week = mysqli_query($conn, $sql_week); ?>
    <h3>Weekly Summary</h3>
    <table>
        <tr>
            <th>Week #</th>
            <th>Total Duration</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result_week)): ?>
            <tr>
                <td>Week <?= htmlspecialchars($row['weekno']) ?></td>
                <td><?= formatDuration($row['total_seconds']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
mysqli_close($conn);
?>
