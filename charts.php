<?php
// DB connection
$conn = mysqli_connect("localhost", "root", "", "test_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get selected user
$selected_user = $_GET['user'] ?? '';
$user_result = mysqli_query($conn, "SELECT DISTINCT name FROM messages");

// WHERE filter
$where = "";
if (!empty($selected_user)) {
    $safe_user = mysqli_real_escape_string($conn, $selected_user);
    $where = "WHERE name = '$safe_user'";
}

// Query: total duration per date
$sql = "SELECT date_today, SUM(TIME_TO_SEC(TIMEDIFF(end, start))) AS total_seconds 
        FROM messages $where 
        GROUP BY date_today ORDER BY date_today ASC";
$result = mysqli_query($conn, $sql);

// Prepare data
$labels = [];
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['date_today'];
    $hours = round($row['total_seconds'] / 3600, 2);
    $data[] = $hours;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chart Toggle</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        canvas { max-width: 700px; margin-top: 20px; }
        select, button { padding: 5px 10px; margin: 10px 5px; }
    </style>
</head>
<body>
    <h2>Work Duration Chart</h2>

    <!-- User filter -->
    <form method="GET">
        <label>User:</label>
        <select name="user" onchange="this.form.submit()">
            <option value="">All</option>
            <?php while ($user = mysqli_fetch_assoc($user_result)): ?>
                <option value="<?= htmlspecialchars($user['name']) ?>" <?= ($selected_user == $user['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- Chart toggle -->
    <button onclick="switchChart('bar')">Bar Chart</button>
    <button onclick="switchChart('pie')">Pie Chart</button>

    <!-- Chart container -->
    <canvas id="timeChart"></canvas>

    <script>
    const labels = <?= json_encode($labels) ?>;
    const data = <?= json_encode($data) ?>;

    let chartType = 'bar';
    const ctx = document.getElementById('timeChart').getContext('2d');

    let chart = new Chart(ctx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Hours',
                data: data,
                backgroundColor: labels.map(() => randomColor()),
                borderColor: '#444',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Work Duration Chart'
                },
                legend: {
                    display: () => chartType === 'pie'
                }
            },
            scales: chartType === 'bar' ? {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Hours' }
                },
                x: {
                    title: { display: true, text: 'Date' }
                }
            } : {}
        }
    });

    function switchChart(type) {
        chart.destroy();
        chartType = type;
        chart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Hours',
                    data: data,
                    backgroundColor: labels.map(() => randomColor()),
                    borderColor: '#444',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Work Duration Chart'
                    },
                    legend: {
                        display: () => chartType === 'pie'
                    }
                },
                scales: chartType === 'bar' ? {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Hours' }
                    },
                    x: {
                        title: { display: true, text: 'Date' }
                    }
                } : {}
            }
        });
    }

    // Random pastel color
    function randomColor() {
        const r = Math.floor(Math.random() * 156 + 100);
        const g = Math.floor(Math.random() * 156 + 100);
        const b = Math.floor(Math.random() * 156 + 100);
        return `rgba(${r}, ${g}, ${b}, 0.6)`;
    }
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
