<?php require 'config.php'; 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cake Shop - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);}</style>
</head>
<body class="min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">🍰 Cake Shop</a>
            <span class="navbar-text ms-auto">Welcome, <?php echo $_SESSION['username']; ?>! 
                <?php if($_SESSION['role']=='owner'): ?><a href="dashboard.php" class="btn btn-light btn-sm ms-2">Owner Dashboard</a><?php endif; ?>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">Logout</a>
            </span>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center mb-5">Welcome to Cake Shop! 🎂</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h3>Customer Features (Coming)</h3>
                        <ul class="list-unstyled">
                            <li>🍰 Browse cakes by category</li>
                            <li>🛒 Add multiple to cart</li>
                            <li>🔍 Search & details</li>
                            <li>💳 Place order</li>
                        </ul>
                        <p class="mt-4">Full catalog in next step!</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h3>Status</h3>
                        <p>Login successful! 👏</p>
                        <?php 
                        $user_id = $_SESSION['user_id'];
                        $cart_count = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(quantity) FROM carts WHERE user_id=$user_id"))[0];
                        echo "<p>Cart items: " . ($cart_count ?? 0) . "</p>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>