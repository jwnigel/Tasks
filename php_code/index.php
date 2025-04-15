<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Assembly Task Project</title>Fatal error: Uncaught Error: Call to undefined function parseTaskFile() in /var/www/html/index.php:23 Stack trace: #0 {main} thrown in /var/www/html/index.php on line 23
    <!-- css link goes here -->
</head>
<body>
    <h1>Tasks</h1>
    <?php 
        include 'task_file_parser.php';

        $task_folder = './all_tasks/' ; 
        $task_files = scandir($task_folder) ; 

        // for parsed data
        $all_data = [];

        foreach ($task_files as $task_file) {
            if (is_dir($task_folder . $task_file) || $task_file[0] === '.') {
                continue;
            }

            $file_contents = file_get_contents($task_folder . $task_file);

            $task_data = parseTaskFile($file_contents);

            if ($task_data === null) {
                continue;
            }

            $all_data[] = $task_data;

        }
        ?>

        <h2>Table View</h2>
        <table id="dataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Group Name</th>
                    <th>File Name</th>
                    <th>Title</th>
                    <th>Date Created</th>
                    <th>Date Updated</th>
                    <th>Task #</th>
                    <th>Time (sec)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_data as $task_data): ?>
                <tr>
                    <td><?php echo $task_data['id']; ?></td>
                    <td><?php echo $task_data['GroupName']; ?></td>
                    <td><?php echo $task_data['FileName']; ?></td>
                    <td><?php echo $task_data['Title']; ?></td>
                    <td><?php echo $task_data['DateCreated']; ?></td>
                    <td><?php echo $task_data['DateUpdated']; ?></td>
                    <td><?php echo $task_data['TaskNum']; ?></td>
                    <td><?php echo $task_data['SecondsToComplete']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

</body>
</html>