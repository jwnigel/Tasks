<?php
// Database connection
$db = require_once("includes/connection.php");

// Function to shorten file path to just the filename
function shortenFilePath($path) {
    if (empty($path)) {
        return '';
    }
    // Find the position after "Current Assembly Procedures\"
    $startPos = strpos($path, "Current Assembly Procedures\\");
    if ($startPos === false) {
        return basename($path);
    }
    $startPos += strlen("Current Assembly Procedures\\");
    return substr($path, $startPos);
}

try {
    // Read the CSV file
    $csvFile = '/var/www/html/all_tasks/SpareTimeStudy.csv';
    $handle = fopen($csvFile, 'r');
    if ($handle === false) {
        throw new Exception("Could not open CSV file");
    }

    // Skip the header row
    fgetcsv($handle);

    // Prepare the insert statement
    $insertStmt = $db->prepare("
        INSERT INTO WorkTask (Title, RoutedTime, FileName, MasterOrder)
        VALUES (:title, :routedTime, :fileName, :masterOrder)
    ");

    // Begin transaction
    $db->beginTransaction();

    $masterOrder = 1;
    $insertCount = 0;

    // Process each row
    while (($row = fgetcsv($handle)) !== false) {
        $task = trim($row[0]);
        $time = trim($row[1]);
        $filePath = trim($row[2]);

        // Skip empty rows or rows without a task name
        if (empty($task) || $task === 'Task') {
            continue;
        }

        // Skip the empty header-like row
        if ($task === '' && $time === '' && $filePath === '') {
            continue;
        }

        // Process time (convert "mm:ss" to seconds if needed)
        $routedTime = null;
        if (!empty($time)) {
            if (strpos($time, ':') !== false) {
                list($minutes, $seconds) = explode(':', $time);
                $routedTime = ($minutes * 60) + $seconds;
            } elseif (is_numeric($time)) {
                $routedTime = (int)$time;
            }
        }

        // Shorten the file path
        $fileName = shortenFilePath($filePath);

        // Insert into database
        $insertStmt->execute([
            ':title' => $task,
            ':routedTime' => $routedTime,
            ':fileName' => $fileName,
            ':masterOrder' => $masterOrder
        ]);

        $masterOrder++;
        $insertCount++;
    }

    // Commit transaction
    $db->commit();
    fclose($handle);

    echo "Successfully inserted $insertCount tasks into WorkTask table. MasterOrder goes up to " . ($masterOrder - 1);

} catch (Exception $e) {
    // Rollback in case of error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    if (isset($handle) && $handle) {
        fclose($handle);
    }
    echo "Error: " . $e->getMessage();
}
?>