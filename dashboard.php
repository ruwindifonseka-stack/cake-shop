<?php require 'config.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header('Location: login.php');
    exit();
}

// Update order status
if (isset($_GET['update_status'])) {
    $order_id = (int)$_GET['update_status'];
    $status = $_GET['status'] ?? 'pending';
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$order_id");
}

// Get recent orders
$orders_result = mysqli_query($conn, "
    SELECT o.*, u.username, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity * oi.price) as order_total
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id 
    ORDER BY o.order_date DESC 
    LIMIT 20
");
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $row;
}
mysqli_free_result($orders_result);

// Stats
$total_orders = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];
$total_revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(total) FROM orders"))[0] ?? 0;
$pending_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Owner Dashboard - Cake Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>body{background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);}</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fs-3" href="#"><i class="fas fa-crown text-warning"></i> Owner Dashboard</a>
            <div>
                <a href="index.php" class="btn btn-primary me-2">Shop Front</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid my-4">
        <!-- Stats Cards -->
        <div class="row mb-5 g-4">
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-lg border-0 h-100 text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shopping-cart fs-1 me-3"></i>
                            <div>
                                <h2 class="mb-0"><?=$total_orders?></h2>
                                <p class="mb-0">Total Orders</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-lg border-0 h-100 text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-coins fs-1 me-3"></i>
                            <div>
                                <h2 class="mb-0">LKR <?=number_format($total_revenue,0)?></h2>
                                <p class="mb-0">Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-lg border-0 h-100 text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock fs-1 me-3"></i>
                            <div>
                                <h2 class="mb-0"><?=$pending_count?></h2>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-lg border-0 h-100 text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users fs-1 me-3"></i>
                            <div>
                                <h2 class="mb-0"><?=mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0]?></h2>
                                <p class="mb-0">Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="manage_cakes.php" class="btn btn-success mb-3">🍰 Manage Cakes</a>
        </div>
        <!-- Orders Table -->
        <div class="card shadow-lg">
            <div class="card-header bg-dark text-white">
                <h3><i class="fas fa-list me-2"></i>Recent Orders</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td><strong>#<?=$order['id']?></strong></td>
                                <td><?=$order['username']?></td>
                                <td><?=$order['item_count']?> items</td>
                                <td><strong>LKR <?=number_format($order['order_total'],0)?></strong></td>
                                <td><?=date('M j, Y H:i', strtotime($order['order_date']))?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $order['status']=='pending' ? 'bg-warning' : 
                                             ($order['status']=='shipped' ? 'bg-info' : 'bg-success'); 
                                    ?>">
                                        <?=ucfirst($order['status'])?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($order['status']=='pending'): ?>
                                    <a href="?update_status=<?=$order['id']?>&status=shipped" class="btn btn-sm btn-info">Ship</a>
                                    <?php endif; ?>
                                    <a href="?update_status=<?=$order['id']?>&status=delivered" class="btn btn-sm btn-success">Mark Delivered</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>