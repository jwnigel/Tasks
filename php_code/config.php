<?php 
$db = require_once("includes/connection.php"); 

$stmt = $db->query("SELECT * FROM WorkConfiguration");
$zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <div class="config-container">
        <h1>Work Configuration</h1>
        <table>
            <thead>
                <tr>
                    <th>WorkConfigID</th>
                    <th>WorkID</th>
                    <th>ZoneCount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $zone): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($zone['WorkConfigID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zone['WorkID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zone['ZoneCount'] ?? ''); ?></td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>