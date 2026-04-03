<?php require 'config.php'; ?>
<!DOCTYPE html>
<html><head><title>Test DB</title></head><body>
<h2>Database Test</h2>
<p>Connected successfully!</p>
<?php
// Test query
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$row = mysqli_fetch_assoc($result);
echo "<p>Users table has {$row['count']} rows.</p>";
?>
</body></html>