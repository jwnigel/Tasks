
<?php include 'task_file_parser.php';


$array=[
    'id'=>'new',
    'Title' => 'my title',
    'FileName'=>'file.txt'
];  

$link = mysqli_connect('db', 'nigel', 'passw0rd', 'sample_d');  // (host (name in docker), username, password, database name)

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
        <?
        $task_folder = './all_tasks/' ; 
    

        if (isset($_POST['submit']) && $_POST['submit']=="SaveTask") {

            if ($_POST['id']=='new'){
                $insert_sql = "INSERT INTO Tasks (Title, SecondsToComplete, Description, GroupName, FileName) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($link, $insert_sql);
                mysqli_stmt_bind_param(
                    $insert_stmt,
                    'sisss',
                    $_POST['title'],
                    $_POST['secondsToComplete'],
                    $_POST['description'],
                    $_POST['groupName'],
                    $_POST['fileName']
                );
                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
        
                print '<div class="alert alert-success" role="alert">✅ New task "' . $_POST['title'] . '" added.</div>';
            } else {
                $update_query = "UPDATE Tasks SET Title = ?, SecondsToComplete = ?, Description = ?, GroupName = ?, FileName = ? WHERE id = ?";
                $update_statement = mysqli_prepare($link, $update_query);
                mysqli_stmt_bind_param($update_statement, 'sisssi', $_POST['title'], $_POST['secondsToComplete'], $_POST['description'], $_POST['groupName'], $_POST['fileName'], $_POST['id']);
                mysqli_stmt_execute($update_statement);
                mysqli_stmt_close($update_statement);
                print '<div class="alert alert-success" role="alert">Record #'.$_POST['title'].' updated</div>';
            }

        }

        if (isset($_GET['id'])) {
           
            $id_check_query =  "SELECT * FROM Tasks WHERE id = ?";
            $id_check_statement = mysqli_prepare($link, $id_check_query);
            mysqli_stmt_bind_param($id_check_statement, "i", $_GET['id']);
            mysqli_stmt_execute($id_check_statement);
            $result = mysqli_stmt_get_result($id_check_statement);
            if (mysqli_num_rows($result) == 0) {
                if ($_GET['id'] != 'new') {
                    echo "<h2>❌ No result found for id = " . $_GET['id'] . "</h2>";
                }
            }
            else {
                $task = mysqli_fetch_assoc($result);
                //$obj = mysqli_fetch_object($result);                

            }
        }
        
        if (isset($_GET['id']) && $_GET['id']=='new') {
            $task=[
                'id'=>'new',
                // 'Title' => $_GET['fileName'],
                // 'FileName'=>$_GET['fileName'],

            ]; 
            }
            
        $deleteModeOn = isset($_GET['delete']) && $_GET['delete'] == 't';


        if (isset($_POST['submit']) && $_POST['submit'] == "DeleteTask") {
            $delete_sql = "DELETE FROM Tasks WHERE id = ?";
            $delete_stmt = mysqli_prepare($link, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, 'i', $_POST['id']);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);

        }

        if (isset($task)) {
            $exists = file_exists( $task_folder.$task["FileName"] );  

            echo 'File check result: ' . ($exists ? 'True' : 'False') . "   ";
            echo 'FileName: ' . ($task['FileName']) ;
            ?>

            <div class="edit-form">
                <?php if ($deleteModeOn): ?>
                    <!-- Delete Task Form -->
                    <h2>❌ Delete Task</h2>
                    <div class="alert alert-danger">
                        <strong>Warning!</strong> Are you sure you want to delete this task?
                    </div>
                    
                    <form method="post" action="/">
                        <input type="hidden" name="id" value="<?php echo $task['id']; ?>"/>
                        
                        <div class="task-row">
                            <label><strong>ID:</strong> <?php echo $task['id']; ?></label>
                        </div>
                        
                        <div class="task-row">
                            <label><strong>Title:</strong> <?php echo $task['Title']; ?></label>
                        </div>
                        
                        <div class="task-row">
                            <label><strong>File Name:</strong> <?php echo $task['FileName']; ?></label>
                        </div>
                        
                        <?php if (isset($task['GroupName']) && !empty($task['GroupName'])): ?>
                        <div class="task-row">
                            <label><strong>Group:</strong> <?php echo $task['GroupName']; ?></label>
                        </div>
                        <?php endif; ?>

                        <div class="task-submit-btn">
                            <button type="submit" name="submit" value="DeleteTask" class="btn btn-danger">Confirm Delete</button>
                            <?php if (!$exists): ?>
                            <?php endif; ?>
                            <a href="?id=<?= $task_data['id'] ?>">Edit Task</a>
                        </div>
                        <p class="text-danger"><small>Note: The associated file is missing from the filesystem</small></p>

                    </form>

                <?php else: ?>

                <h2>📝<?=$task['id']=="new"?"Add":"Edit"?> Task</h2>
                <form method="post" action="index.php?id=<?php echo $_GET['id']; ?>">
                    <input type="hidden" name="id" value=<?php echo $_GET['id']; ?>/>

                    <div class="task-row">
                        <label for="file">File: </label>
                        <input type="file" name="fileName" value="<?php echo $task['FileName']; ?>"/>
                    </div>

                    <div class="task-row">
                        <label for="title">Title: </label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $task['Title']; ?>" required>
                    </div>

                    <div class="task-row">
                        <label for="secondsToComplete">Seconds to Complete: </label>
                        <input type="number" id="secondsToComplete" name="secondsToComplete" value="<?php echo $task['SecondsToComplete'] ?? '' ?>">
                    </div>

                    <div class="task-row">
                        <label for="description">Description: </label>
                        <textarea id="description" name="description" rows="4" cols="50"><?php echo $task['Description'] ?? '' ?></textarea>
                    </div>

                    <div class="task-row">
                        <label for="groupName">Group Name: </label>
                        <input type="text" id="groupName" name="groupName" value="<?php echo $task['GroupName'] ?? '' ?>"></input>
                    </div>

                    <div class="task-submit-btn">
                        <button type="submit" id="submit" name="submit" value="SaveTask"><?php echo ($_GET['id'] == 'new') ? "Add to database" : "Save changes" ;?></button>
                        <a href="index.php">Go Back</a>
                    </div>

                </form>
                <?php endif; ?>

            </div>

            <?php
        } else {
                    
       
        $task_files = scandir($task_folder) ; 
        $all_data = [];
        $files = [];

        
        foreach ($task_files as $task_file) {
            if (is_dir($task_folder . $task_file) || $task_file[0] === '.') {
                continue;
            }
            $files[]=$task_file;
        }
        $fileNotInDB=$files;
        
        ?>
        <h1>Tasks</h1>
        <?php 
            if ($link) {
                $result = mysqli_query($link, "SELECT * FROM Tasks");
                $count = mysqli_num_rows($result);
                echo "<h3>✅ Successfully connected to database! Current entries: $count </h2>";

            } 
            else {
                echo "<h3>❌Database connection error </h2>";
            }

            foreach ($all_data as $task) {
                $check_query =  "SELECT * FROM Tasks WHERE FileName = ?";
                $check_statement = mysqli_prepare($link, $check_query);
                mysqli_stmt_bind_param($check_statement, "s", $task['FileName']);
                mysqli_stmt_execute($check_statement);
                $result = mysqli_stmt_get_result($check_statement);
                if (mysqli_num_rows($result) == 0) {
                    $insert_query = "INSERT INTO Tasks (Title, SecondsToComplete, Description, GroupName, FileName, TaskNum, Exists) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $insert_statement = mysqli_prepare($link, $insert_query);
                    mysqli_stmt_bind_param($insert_statement, 'sisssii', $task['Title'], $task['SecondsToComplete'], $task['Description'], $task['GroupName'], $task['FileName'], $task['TaskNum'], $task['Exists']);
                    mysqli_stmt_execute($insert_statement);
                    mysqli_stmt_close($insert_statement);
                    echo "Added task: " . $task['Title'] . "<br>";
                }
                mysqli_stmt_close($check_statement);
            }
            ?>

            <h2>Table View</h2>
            <table id="dataTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th scope="col">Group Name</th>
                        <th>File Name</th>
                        <th>Title</th>
                        <th>Date Created</th>
                        <th>Date Updated</th>
                        <th>Task #</th>
                        <th>Time (sec)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $table_result = mysqli_query($link, "SELECT * FROM Tasks ORDER BY id");
                    while ($task_data = mysqli_fetch_assoc($table_result)):
                        //if (in_array($task_data['FileName'],$files2))
                        //=     $files[$task_data['FileName']])
                        ?>
                        <tr class="<?=in_array($task_data['FileName'],$files) ? 'table-success' : 'table-danger'?> clickable-row"
                            style="cursor: pointer;"
                            onclick="window.location='?id=<?=$task_data['id']?>'">
                            <td><?php echo $task_data['id']; ?></td>
                            <td><?php echo $task_data['GroupName']; ?></td>
                            <td><?php echo $task_data['FileName']; ?></td>
                            <td><?php echo $task_data['Title']; ?></td>
                            <td><?php echo $task_data['DateCreated']; ?></td>
                            <td><?php echo $task_data['DateUpdated']; ?></td>
                            <td><?php echo $task_data['TaskNum']; ?></td>
                            <td><?php echo $task_data['SecondsToComplete']; ?></td>
                            <?php if (!in_array($task_data['FileName'],$files)) {
                               
                                ?><td><a href="?id=<?=$task_data['id']?>&delete=t">File Missing - Delete entry</a></td>
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
                   
                       
                    endwhile; ?>
                    
                    <?php 
                    foreach($fileNotInDB as $fileName): ?>
                        <tr class="table-warning clickable-row"
                            style="cursor: pointer;"
                            onclick="window.location='?id=<?=$task_data['id']?>'">
                            <td> </td>
                            <td> </td>
                            <td><?php echo $fileName; ?></td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td><a href="?filename=<?=$fileName?>&id=new">Add to DB</a></td>
                        </tr>
                    <?php endforeach ?>
                    
                </tbody>
            </table>

            <!-- <?php
            if (sizeof($fileNotInDB)>0){
                print "<h2>Files needing added to the DB</h2>";
                print_r($fileNotInDB);
            }
            
            ?> -->
    </div>
    <?php
        }
        ?>

</body>
</html>