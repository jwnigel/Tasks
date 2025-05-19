<?php 
$db = require_once("includes/connection.php"); 

try {
    // Step 1: Fetch all ZoneIds from WorkZone table
    $zoneStmt = $db->query("SELECT ZoneID FROM WorkZone");
    $zoneIds = $zoneStmt->fetchAll(PDO::FETCH_COLUMN);

    // Step 2: Fetch all TaskIds from WorkTask table
    $taskStmt = $db->query("SELECT TaskID FROM WorkTask");
    $taskIds = $taskStmt->fetchAll(PDO::FETCH_COLUMN);

    // Step 3: Fetch all WorkConfigIds from WorkConfiguration table
    $configStmt = $db->query("SELECT WorkConfigID FROM WorkConfiguration");
    $configIds = $configStmt->fetchAll(PDO::FETCH_COLUMN);

    // Step 4: Get existing combinations to avoid duplicates
    $existingStmt = $db->query("SELECT CONCAT(ZoneID, '-', TaskID, '-', WorkConfigID) as combo FROM WorkZoneTask");
    $existingCombos = $existingStmt->fetchAll(PDO::FETCH_COLUMN);

    // Step 5: Prepare insert statement
    $insertStmt = $db->prepare("INSERT INTO WorkZoneTask (ZoneTaskID, ZoneID, TaskID, WorkConfigID) VALUES (?, ?, ?, ?)");
    
    // Get current max ID to continue numbering
    $maxIdStmt = $db->query("SELECT MAX(ZoneTaskID) FROM WorkZoneTask");
    $zoneTaskId = $maxIdStmt->fetchColumn() + 1;
    if ($zoneTaskId < 1000) $zoneTaskId = 1000; // Start at 1000 if table is empty

    // Begin transaction
    $db->beginTransaction();

    $insertCount = 0;
    foreach ($zoneIds as $zoneId) {
        foreach ($taskIds as $taskId) {
            foreach ($configIds as $configId) {
                $combo = "$zoneId-$taskId-$configId";
                if (!in_array($combo, $existingCombos)) {
                    $insertStmt->execute([$zoneTaskId, $zoneId, $taskId, $configId]);
                    $zoneTaskId++;
                    $insertCount++;
                }
            }
        }
    }

    // Commit transaction
    $db->commit();

    echo "Successfully inserted $insertCount new combinations into WorkZoneTask table.";
    
    // Fetch all records for display
    $zonetasks = $db->query("SELECT * FROM WorkZoneTask ORDER BY ZoneTaskID")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Rollback in case of error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <div class="config-container">
        <h1>Work Configuration</h1>
        <table>
            <thead>
                <tr>
                    <th>ZoneTaskID</th>
                    <th>ZoneID</th>
                    <th>TaskID</th>
                    <th>WorkConfigID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zonetasks as $zt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($zt['ZoneTaskID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zt['ZoneID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zt['TaskID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zt['WorkConfigID'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>