<?php
$conn = mysqli_connect("localhost", "root", "", "test_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $_POST['name'];
    $message = $_POST['message'];
    $start   = $_POST['start'];
    $end     = $_POST['end'];

    // Time logic
    $start_ts = strtotime($start);
    $end_ts   = strtotime($end);

    if ($end_ts < $start_ts) {
        $end_ts += 86400;
    }

    $diff = $end_ts - $start_ts;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $duration = "$hours hours $minutes minutes";

    // Extra: Day, Week number, Date
    $date_today = date('Y-m-d');          // 2025-06-01
    $weekno     = date('W');              // ISO week number
    $day        = date('l');              // Monday, Tuesday, etc.

    // Sanitize
    $name = mysqli_real_escape_string($conn, $name);
    $message = mysqli_real_escape_string($conn, $message);

    // Insert
    $sql = "INSERT INTO messages (name, content, start, end, duration, date_today, weekno, day)
            VALUES ('$name', '$message', '$start', '$end', '$duration', '$date_today', '$weekno', '$day')";

    if (mysqli_query($conn, $sql)) {
        echo "✅ Saved! Duration: $duration";
    } else {
        echo "❌ Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
