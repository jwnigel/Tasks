
<?php 
$db = require_once 'includes/connection.php';

$stmt = $db->query("SELECT * FROM WorkZone");
$zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<body>
    <div class="zone-container">
        <h1>Work Zones</h1>
        <table>
            <thead>
                <tr>
                    <th>ZoneID</th>
                    <th>WorkConfigID</th>
                    <th>WorkID</th>
                    <th>ZoneDisplay</th>
                    <th>Employee</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $zone): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($zone['ZoneID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zone['WorkConfigID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zone['WorkID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zone['ZoneDisplay'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($zone['Employee'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>