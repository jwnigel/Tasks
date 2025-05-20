
<?php include 'task_file_parser.php';
include "global.php";

function formatTime($seconds) {
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    
    return sprintf("%02d:%02d", $minutes, $remainingSeconds);
}


// $link = mysqli_connect('db', 'nigel', 'passw0rd', 'sample_d');  // (host (name in docker), username, password, database name)
$task_folder = './all_tasks/' ; 
$task_files = scandir($task_folder) ; 
$files = [];
foreach ($task_files as $task_file) {
    if (is_dir($task_folder . $task_file) || $task_file[0] === '.') {
        continue;
    }
    $files[]=$task_file;
}
$fileNotInDB=$files;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛠 Assembly Tasks 🛠</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</head>
<body>
    <div class="main-container">
        <a href="zone.php">Zones</a>
        <br>
        <a href="config.php">Configurations</a>
        <br>
        <a href="zonetask.php">ZoneTasks</a>
        <br>

        <?php

        if (isset($_POST['submit']) && $_POST['submit']=="SaveTask") {

            if ($_POST['TaskID']=='new'){
                $sql = "INSERT INTO WorkTasks (Title, RoutedTime, Description, Station, FileName) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($link, $sql);
                mysqli_stmt_bind_param($stmt, 'sisss', $_POST['Title'], $_POST['RoutedTime'], $_POST['Description'], $_POST['Station'], $_POST['FileName']);
                $message = '<div class="alert alert-success" role="alert">✅ New task "' . $_POST['Title'] . '" added.</div>';
            } else {
                $sql = "UPDATE WorkTasks SET Title = ?, RoutedTime = ?, Description = ?, Station = ?, FileName = ? WHERE TaskID = ?";
                $stmt = mysqli_prepare($link, $sql);
                mysqli_stmt_bind_param($stmt, 'sisssi', $_POST['Title'], $_POST['RoutedTime'], $_POST['Description'], $_POST['Station'], $_POST['FileName'], $_POST['TaskID']);
                $message = '<div class="alert alert-success" role="alert">Record #'.$_POST['Title'].' updated</div>';
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            print $message;           

            
        }

        $deleteModeOn = isset($_GET['delete']) && $_GET['delete'] == 't';

        if (isset($_POST['submit']) && $_POST['submit'] == "DeleteTask") {
            $delete_sql = "DELETE FROM WorkTasks WHERE TaskID = ?";
            $delete_stmt = mysqli_prepare($link, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, 'i', $_POST['TaskID']);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
        }

        if (isset($_GET['TaskID']) && $_GET['TaskID']!='new') {

            $id_check_query =  "SELECT * FROM WorkTask WHERE TaskID = ?";
            $id_check_statement = mysqli_prepare($link, $id_check_query);
            mysqli_stmt_bind_param($id_check_statement, "i", $_GET['TaskID']);
            mysqli_stmt_execute($id_check_statement);
            $result = mysqli_stmt_get_result($id_check_statement);
            if (mysqli_num_rows($result) == 0) {
                echo "<h2>❌ No result found for TaskID = " . $_GET['TaskID'] . "</h2>";
            }
            else {
                $task = mysqli_fetch_assoc($result);
            }
        }
        
        if (isset($_GET['TaskID']) && $_GET['TaskID']=='new') {
            $task=[
                'TaskID'=>'new',
                'Title' => $_GET['FileName'] ?? 'Add Title',
                'FileName' => $_GET['FileName'] ?? '',
                'Station' => 'What group is this task part of?',
                'Description' => 'Add a description',
                'RoutedTime' => 0
            ]; 
            }

        if (isset($task)) {
            $exists = file_exists( $task_folder.$task["FileName"] );  
            if (!$exists) {
                echo 'File does not exist: ' . $task["FileName"] . ' for task ' . $task['Title'];
            }
            ?>

            <div class="edit-form">
                <?php if ($deleteModeOn): ?>
                    <!-- Delete Task Form -->
                    <h2>❌ Delete Task</h2>
                    <div class="alert alert-danger">
                        <strong>Warning!</strong> Are you sure you want to delete this task?
                    </div>
                    
                    <form method="post" action="/">
                        <input type="hidden" name="TaskID" value="<?php echo $task['TaskID']; ?>"/>
                        
                        <div class="task-row">
                            <label><strong>TaskID:</strong> <?php echo $task['TaskID']; ?></label>
                        </div>
                        
                        <div class="task-row">
                            <label><strong>Title:</strong> <?php echo $task['Title']; ?></label>
                        </div>
                        
                        <div class="task-row">
                            <label><strong>File Name:</strong> <?php echo $task['FileName']; ?></label>
                        </div>
                        
                        <?php if (isset($task['Station']) && !empty($task['Station'])): ?>
                        <div class="task-row">
                            <label><strong>Group:</strong> <?php echo $task['Station']; ?></label>
                        </div>
                        <?php endif; ?>

                        <div class="task-submit-btn">
                            <button type="submit" name="submit" value="DeleteTask" class="btn btn-danger">Confirm Delete</button>
                            <a href="?TaskID=<?=$task['TaskID']?>">Edit Task</a>
                        </div>
                        <p class="text-danger"><small>Note: The associated file is missing from the filesystem</small></p>

                    </form>

                <?php else: ?>

                <h2>📝<?=$task['TaskID']=="new"?"Add":"Edit"?> Task</h2>
                <form method="post" action="?">
                    <input type="hidden" id="TaskID" name="TaskID" value="<?php echo $_GET['TaskID']; ?>"/>

                    <div class="task-row">
                        <label for="filName">File: </label>
                        <input type="text" id="FileName" name="FileName" value="<?php echo $task['FileName']; ?>"/>
                    </div>

                    <div class="task-row">
                        <label for="Title">Title: </label>
                        <input type="text" id="Title" name="Title" class="form-control" value="<?php echo $task['Title']; ?>" required>
                    </div>

                    <div class="task-row">
                        <label for="Description">Description: </label>
                        <textarea id="Description" name="Description" rows="4" cols="50"><?php echo $task['Description'] ?? '' ?></textarea>
                    </div>

                    <div class="task-row">
                        <label for="Station">Station #: </label>
                        <input type="number" id="Station" name="Station" value="<?php echo $task['Station'] ?? '' ?>"></input>
                    </div>
                    
                    <div class="task-row">
                        <label for="Order">Order #: </label>
                        <input type="number" id="Order" name="Order" value="<?php echo $task['Order'] ?? '' ?>"></input>
                    </div>

                    <div class="task-row">
                        <label for="RoutedTime">Routed Time: </label>
                        <input type="number" id="RoutedTime" name="RoutedTime" value="<?php echo $task['RoutedTime'] ?? '' ?>">
                    </div>

                    <div class="task-submit-btn">
                        <button type="submit" id="submit" name="submit" value="SaveTask"><?php echo ($_GET['TaskID'] == 'new') ? "Add to database" : "Save changes" ;?></button>
                        <a href="index.php">Go Back</a>
                    </div>

                </form>
                <?php endif; ?>

            </div>

            <?php
        } else {
        
        ?>
        <h1>Tasks</h1>
        <?php 
            if ($link) {
                $result = mysqli_query($link, "SELECT * FROM WorkTask");
                $count = mysqli_num_rows($result);
                echo "<h3>✅ Successfully connected to database! Current entries: $count </h2>";

            } 
            else {
                echo "<h3>❌Database connection error </h2>";
            }
            ?>

            <h2>Table View</h2>
            <form method="get" style="display: inline;">
                <input type="hidden" name="TaskID" value="new">
                <button type="submit" class="btn btn-primary">Add New Task</button>
            </form>
            
            <table id="dataTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>TaskID</th>
                        <!-- <th scope="col">Station #</th> -->
                        <th>File Name</th>
                        <th>Title</th>
                        <th>Time</th>
                        <th>Date Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    $table_result = mysqli_query($link, "SELECT * FROM WorkTask ORDER BY TaskID");
                    while ($task_data = mysqli_fetch_assoc($table_result)): //green and red rows
                        ?>
                        <tr class="<?=in_array($task_data['FileName'],$files) ? 'table-success' : 'table-success' // change this back to table-danger when file validation added?> clickable-row" 
                            style="cursor: pointer;"
                            onclick="window.location='?TaskID=<?=$task_data['TaskID']?>'">
                            <td><?php echo $task_data['TaskID']; ?></td>
                            <!-- <td onclick="event.stopPropagation(); window.location='station.php?station_id=<?=$task_data['Station']?>'"><?php echo $task_data['Station']; ?></td> -->
                            <td><?php echo $task_data['FileName']; ?></td>
                            <td><?php echo $task_data['Title']; ?></td>
                            <td><?php echo formatTime($task_data['RoutedTime']); ?></td>
                            <td><?php echo $task_data['DateUpdated']; ?></td>                            
                            <?php if (!in_array($task_data['FileName'],$files)) {
                               
                                ?><td><a href="?TaskID=<?=$task_data['TaskID']?>&delete=t">Delete Fileless entry</a></td>
                                <?php
                            } else {
                                echo "<td> </td>";
                            }
                            ?>
                        </tr>
                        <?php
                         $key_to_remove = array_search($task_data['FileName'], $fileNotInDB);
                         if ($key_to_remove !== false) {
                            unset($fileNotInDB[$key_to_remove]);
                         }
                    
                       
                    endwhile;

                    foreach($fileNotInDB as $missingFile): // yellow rows ?> 
                        <tr class="table-warning clickable-row"
                            style="cursor: pointer;"
                            onclick="window.location='?TaskID=<?=$task_data['TaskID']?>'">
                            <td> </td>
                            <td> </td>
                            <td><?php echo $missingFile; ?></td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td><a href="?FileName=<?=(string)$missingFile?>&TaskID=new">Add to DB</a></td>
                        </tr>
                    <?php endforeach ?>
                    
                </tbody>
            </table>
    </div>
    <?php
        }
        ?>

</body>
</html>