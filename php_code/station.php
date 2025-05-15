<?php
include("global.php");
$station_id = isset($_GET['station_id']) ? (int)$_GET['station_id'] : 0;


$station_sql = "SELECT * FROM WorkZone WHERE ZoneID = ?";
$station_stmt = mysqli_prepare($link, $station_sql);
mysqli_stmt_bind_param($station_stmt, "i", $station_id);
mysqli_stmt_execute($station_stmt);
$station_result = mysqli_stmt_get_result($station_stmt);

$task_sql = "SELECT * FROM WorkTask WHERE Station = ?";
$task_stmt = mysqli_prepare($link, $task_sql);
mysqli_stmt_bind_param($task_stmt, "i", $station_id);
mysqli_stmt_execute($task_stmt);
$task_result = mysqli_stmt_get_result($task_stmt);

$station_data = mysqli_fetch_assoc($station_result);
if (mysqli_num_rows($station_result) == 0) {
    $station_display = "<h2>❌ No result found for Station = " . $station_id . "</h2>";
}
 else {
    $emp_id = $station_data['Employee'];
    $emp_sql = "SELECT * FROM WorkEmployee WHERE EmployeeID = ?";
    $emp_stmt = mysqli_prepare($link, $emp_sql);
    mysqli_stmt_bind_param($emp_stmt, "i", $emp_id);
    mysqli_stmt_execute($emp_stmt);
    $emp_result = mysqli_stmt_get_result($emp_stmt);
    if (mysqli_num_rows($emp_result) > 0) {
        $employee_row = mysqli_fetch_assoc($emp_result);
        $employee_name = $employee_row['EmployeeName'];
    }
    else {
        $employee_name = "John Doe";
    }
    $station_display = "<h2> Station " . $station_data['StationID'] . " filled by " . $employee_name . " Employee#" . $station_data['Employee'] . "</h2>";

}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Station Details</title>
</head>
<body>
    <?php echo $station_display ?>
    
    <?php 
        $total_seconds = 0;
        while ($row = mysqli_fetch_assoc($task_result)) {
            echo $row['Title'] . " routed time: " . $row['RoutedTime'];
            echo "<br>";
            $total_seconds += $row['RoutedTime'];
        echo "Total Time: " . $total_seconds;
        }
    ?>

    <p><a href="index.php">Go Back</a></p>
</body>
</html>