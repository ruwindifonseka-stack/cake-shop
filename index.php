<?php require 'config.php'; 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle cart add FIRST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    foreach ($_POST['selected_cakes'] ?? [] as $cake_id) {
        $quantity = max(1, (int)($_POST['quantity'][$cake_id] ?? 1));
        $user_id = $_SESSION['user_id'];
        
        $check_sql = "SELECT id FROM carts WHERE user_id=$user_id AND cake_id=$cake_id";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            mysqli_query($conn, "UPDATE carts SET quantity = quantity + $quantity WHERE user_id=$user_id AND cake_id=$cake_id");
        } else {
            mysqli_query($conn, "INSERT INTO carts (user_id, cake_id, quantity) VALUES ($user_id, $cake_id, $quantity)");
        }
        mysqli_free_result($check_result);
    }
    echo "<script>alert('Added to cart! 🎉');</script>";
}

// Search & filter
$where = '1=1';
if (!empty($_GET['search'])) {
    $where .= " AND c.name LIKE '%" . mysqli_real_escape_string($conn, $_GET['search']) . "%'";
}
if (!empty($_GET['category'])) {
    $where .= " AND c.category_id = " . (int)$_GET['category'];
}

$query = "SELECT c.*, cat.name as cat_name FROM cakes c JOIN categories cat ON c.category_id = cat.id WHERE $where ORDER BY c.name";
$cakes_result = mysqli_query($conn, $query);
$cakes = [];
while ($row = mysqli_fetch_assoc($cakes_result)) {
    $cakes[] = $row;
}
mysqli_free_result($cakes_result);

// Categories dropdown
$cat_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories = [];
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row;
}
mysqli_free_result($cat_result);

// Cart count
$cart_result = mysqli_query($conn, "SELECT SUM(quantity) as total FROM carts WHERE user_id=" . $_SESSION['user_id']);
$cart_count = mysqli_fetch_assoc($cart_result)['total'] ?? 0;
mysqli_free_result($cart_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cake Catalog - Cake Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
        .cake-card { transition: transform 0.3s; }
        .cake-card:hover { transform: scale(1.05); }
        .price-tag { background: #ff6b6b; color: white; padding: 8px 20px; border-radius: 25px; font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fs-3 fw-bold" href="#">🍰 Cake Shop</a>
            <form class="d-flex me-auto" method="GET">
                <input class="form-control me-2" type="search" name="search" placeholder="🔍 Search cakes..." value="<?php echo htmlspecialchars($_GET['search']??''); ?>">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?=$cat['id']?>" <?php echo ($_GET['category']??'') == $cat['id'] ? 'selected' : ''; ?>><?=$cat['name']?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-light" type="submit">Filter</button>
            </form>
            <div class="navbar-text me-3">
                Cart: <span class="badge bg-danger fs-5"><?=$cart_count?></span>
            </div>
            <?php if(isset($_SESSION['role']) && $_SESSION['role']=='owner'): ?>
            <a href="dashboard.php" class="btn btn-light btn-sm me-2">👑 Owner Dashboard</a>
            <?php endif; ?>
            <a href="cart.php" class="btn btn-warning btn-sm me-2">🛒 View Cart</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>
    
    <div class="container my-5">
        <div class="row mb-5">
            <div class="col text-center">
                <h1 class="display-4 fw-bold mb-3">🍰 Our Delicious Cakes</h1>
                <p class="lead text-muted">Browse, select multiple, set quantities & add to cart!</p>
            </div>
        </div>
        
        <form method="POST">
            <?php if(empty($cakes)): ?>
            <div class="alert alert-warning text-center">
                <h4>😔 No cakes found</h4>
                <p>Try different search or <a href="?" class="alert-link">clear filters</a></p>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach($cakes as $cake): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card cake-card shadow-lg border-0 h-100">
                        <div class="card-img-top position-relative overflow-hidden" style="height: 220px;">
                            <?php if($cake['image']): ?>
                            <img src="images/<?=$cake['image']?>" class="w-100 h-100 object-fit-cover" alt="<?=$cake['name']?>">
                            <?php else: ?>
                            <div class="w-100 h-100 bg-gradient d-flex align-items-center justify-content-center text-white">
                                <div class="text-center">
                                    <i class="fas fa-birthday-cake fs-1 mb-2"></i>
                                    <div>Cake Image</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold"><?=$cake['name']?></h5>
                                <span class="badge bg-success"><?=$cake['cat_name']?></span>
                            </div>
                            <p class="card-text flex-grow-1 text-muted small"><?=$cake['details']?></p>
                            <div class="price-tag mb-3 d-flex justify-content-center align-items-center fs-5">
                                LKR <?=number_format($cake['price'], 0)?>
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Qty</span>
                                <input type="number" class="form-control" name="quantity[<?=$cake['id']?>]" value="1" min="1" max="50">
                                <div class="input-group-text">
                                    <input type="checkbox" class="form-check-input border-0" name="selected_cakes[]" value="<?=$cake['id']?>" id="cake_<?=$cake['id']?>">
                                </div>
                            </div>
                            <label for="cake_<?=$cake['id']?>" class="form-check-label small text-muted ms-3">
                                ✓ Select this cake
                            </label>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5 pt-4 border-top">
                <button type="submit" name="add_to_cart" value="1" class="btn btn-success btn-lg px-5 py-3 fs-4 shadow-lg">
                    🛒 Add <?=count($cakes)?> Selected Cakes to Cart
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>