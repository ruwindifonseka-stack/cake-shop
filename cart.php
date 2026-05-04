<?php require 'config.php'; 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    mysqli_query($conn, "DELETE FROM carts WHERE id=" . (int)$_GET['remove'] . " AND user_id=" . $_SESSION['user_id']);
}

// Get cart items
$user_id = $_SESSION['user_id'];
$cart_result = mysqli_query($conn, "SELECT c.id, ck.name, ck.price, ck.image, c.quantity, (ck.price * c.quantity) as subtotal 
                                    FROM carts c 
                                    JOIN cakes ck ON c.cake_id = ck.id 
                                    WHERE c.user_id = $user_id");
$cart_items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $row;
    $total += $row['subtotal'];
}
mysqli_free_result($cart_result);

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    if (!empty($cart_items)) {
        // Create order
        $order_insert = mysqli_query($conn, "INSERT INTO orders (user_id, total) VALUES ($user_id, $total)");
        $order_id = mysqli_insert_id($conn);
        
        // Move cart to order_items
        foreach ($cart_items as $item) {
            mysqli_query($conn, "INSERT INTO order_items (order_id, cake_id, quantity, price) VALUES ($order_id, {$item['cake_id']}, {$item['quantity']}, {$item['price']})");
        }
        
        // Clear cart
        mysqli_query($conn, "DELETE FROM carts WHERE user_id = $user_id");
        
        echo "<script>alert('Order #$order_id placed! Total: LKR " . number_format($total,2) . "'); window.location='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shopping Cart - Cake Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);}</style>
</head>
<body class="min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">🍰 Cake Shop</a>
            <a href="index.php" class="btn btn-outline-light ms-auto me-2">Continue Shopping</a>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>
    
    <div class="container my-5">
        <h1 class="text-center mb-5">🛒 Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
        <div class="alert alert-info text-center">
            <h4>Your cart is empty 😔</h4>
            <a href="index.php" class="btn btn-primary btn-lg">Browse Cakes</a>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <?php foreach($cart_items as $item): ?>
                        <div class="d-flex align-items-center border-bottom py-3">
                            <img src="images/<?=$item['image'] ?: 'default.jpg'?>" class="rounded me-3" style="width:80px;height:80px;object-fit:cover;" alt="<?=$item['name']?>">
                            <div class="flex-grow-1">
                                <h5><?=$item['name']?></h5>
                                <p class="mb-1">LKR <?=number_format($item['price'],2)?> x <?=$item['quantity']?></p>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5 text-success mb-2">LKR <?=number_format($item['subtotal'],2)?></div>
                                <a href="?remove=<?=$item['id']?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove?')">🗑️ Remove</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-lg sticky-top" style="top:20px;">
                    <div class="card-header bg-primary text-white">
                        <h4>Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Items (<?=count($cart_items)?>):</span>
                            <span><?=count($cart_items)?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold mb-4">
                            <span>Total:</span>
                            <span class="text-success">LKR <?=number_format($total,2)?></span>
                        </div>
                        <form method="POST">
                            <button type="submit" name="checkout" class="btn btn-success w-100 btn-lg mb-3">
                                ✅ Place Order Now
                            </button>
                            <small class="text-muted d-block text-center">Orders processed within 24hrs</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>