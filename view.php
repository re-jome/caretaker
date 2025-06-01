<?php
$conn = mysqli_connect("localhost", "root", "", "test_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM messages ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr>
            <th>ID</th>
            <th>Location</th>
            <th>Parts</th> 
            <th>Start Time</th>
            <th>End Time</th>
            <th>Duration</th>
             <th>Day</th>
             <th>Date</th>
             <th>Week</th>
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        $id      = $row['id'];
        $name    = $row['name'];
        $message = $row['content'];
        $start   = $row['start'];
        $end     = $row['end'];
       $duration = $row['duration']; // already calculated during insert
        $day     = $row['day'];
        $date_today  = $row['date_today'];
        $weekno = $row['weekno'];


       

        echo "<tr>
                <td>$id</td>
                <td>$name</td>
                <td>$message</td>
                <td>$start</td>
                <td>$end</td>
                <td>$duration</td>
                <td>$day</td>
                <td>$date_today</td>
                <td>$weekno</td>


              </tr>";
    }

    echo "</table>";
} else {
    echo "No records found.";
}

mysqli_close($conn);
?>
