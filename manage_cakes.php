<?php
require 'config.php';


// Owner check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header('Location: login.php');
    exit();
}


// Handle ADD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_cake'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $details = mysqli_real_escape_string($conn, $_POST['details']);
   
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Create images folder if missing
        if (!file_exists('images')) mkdir('images', 0777, true);
       
        $target_dir = "images/";
        $image_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($image_extension, ['jpg','jpeg','png','gif'])) {
            $image_name = 'cake_' . time() . '.' . $image_extension;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
        }
    }
   
    $sql = "INSERT INTO cakes (category_id, name, image, price, details) VALUES ($category_id, '$name', '$image_name', $price, '$details')";
    mysqli_query($conn, $sql);
}

// Handle EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cake'])) {
    $edit_id = (int)$_POST['edit_id'];
    $edit_name = mysqli_real_escape_string($conn, $_POST['edit_name']);
    $edit_price = (float)$_POST['edit_price'];
    $edit_category = (int)$_POST['edit_category'];
    
    mysqli_query($conn, "UPDATE cakes SET name='$edit_name', price=$edit_price, category_id=$edit_category WHERE id=$edit_id");
    
    header('Location: manage_cakes.php?success=edit');
    exit();
}

// Cancel edit
if (isset($_GET['cancel_edit'])) {
    header('Location: manage_cakes.php');
    exit();
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $cake_id = (int)$_GET['delete'];
    $cake_result = mysqli_query($conn, "SELECT image FROM cakes WHERE id=$cake_id");
    if ($cake = mysqli_fetch_assoc($cake_result)) {
        if ($cake['image'] && file_exists("images/" . $cake['image'])) {
            unlink("images/" . $cake['image']);
        }
    }
    mysqli_query($conn, "DELETE FROM cakes WHERE id=$cake_id");
    mysqli_free_result($cake_result);
}


// Get cakes + categories
$cakes_result = mysqli_query($conn, "SELECT c.*, cat.name as category FROM cakes c LEFT JOIN categories cat ON c.category_id=cat.id ORDER BY c.id DESC");
$cakes = [];
while ($row = mysqli_fetch_assoc($cakes_result)) {
    $cakes[] = $row;
}
mysqli_free_result($cakes_result);


$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
}
mysqli_free_result($categories_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Cakes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>body{background:#f8f9fa;}</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fs-3" href="dashboard.php">
                <i class="fas fa-crown text-warning me-2"></i>Admin Panel
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-primary me-2">👥 Shop Front</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>
   
    <div class="container-fluid my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4"><i class="fas fa-birthday-cake text-danger me-3"></i>Manage Cakes</h1>
               
                <!-- Add Form -->
                <?php if (isset($_GET['success'])): ?>
<div class="alert alert-success">
    <?php if($_GET['success']=='edit'): ?>
        Cake updated successfully! ✅
    <?php else: ?>
        Cake added successfully! 🎉
    <?php endif; ?>
</div>
<?php endif; ?>
               
                <div class="card shadow-lg mb-5">
                    <div class="card-header bg-success text-white">
                        <h3><i class="fas fa-plus me-2"></i>Add New Cake</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Cake Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Price (LKR)</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Category</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Choose...</option>
                                        <?php foreach($categories as $cat): ?>
                                        <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="add_cake" class="btn btn-success w-100">
                                        <i class="fas fa-plus"></i> Add Cake
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Details</label>
                                    <textarea name="details" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Cakes Table -->
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-list me-2"></i>Cakes List (<?=count($cakes)?>)</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($cakes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-birthday-cake fa-3x text-muted mb-3"></i>
                            <h4>No cakes yet. Add your first cake above!</h4>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="80">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Details</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(array_reverse($cakes) as $cake): ?>
                                    <tr>
                                        <td>
                                            <?php if($cake['image'] && file_exists("images/{$cake['image']}")): ?>
                                            <img src="images/<?=htmlspecialchars($cake['image'])?>" width="60" height="60" class="rounded shadow-sm">
                                            <?php else: ?>
                                            <div class="bg-light rounded p-3 text-center text-muted" style="width:60px;height:60px;">
                                                <small>📷</small>
                                            </div>
                                            <?php endif; ?>
                                            </td>
                                        <td><strong><?=$cake['name']?></strong></td>
                                        <td><span class="badge bg-info"><?=$cake['category'] ?: 'Uncategorized'?></span></td>
                                        <td><span class="fw-bold text-success">LKR <?=number_format($cake['price'], 0)?></span></td>
                                        <td><?=htmlspecialchars(substr($cake['details'], 0, 50))?>...</td>
    <td>
    <!-- Edit Form (Inline) -->
    <a href="?edit=<?=$cake['id']?>" class="btn btn-sm btn-warning edit-btn">
        <i class="fas fa-edit"></i> Edit
    </a>

    <?php if (isset($_GET['edit']) && $_GET['edit'] == $cake['id']): ?>
    <form method="POST" class="d-inline" enctype="multipart/form-data">
        <input type="hidden" name="edit_id" value="<?=$cake['id']?>">
        <input type="text" name="edit_name" value="<?=htmlspecialchars($cake['name'])?>" class="form-control form-control-sm d-inline-block w-auto" style="width:120px;">
        <input type="number" name="edit_price" value="<?=$cake['price']?>" step="0.01" class="form-control form-control-sm d-inline-block w-auto" style="width:80px;">
        <select name="edit_category" class="form-select form-select-sm d-inline-block w-auto" style="width:100px;">
            <?php foreach($categories as $cat): ?>
            <option value="<?=$cat['id']?>" <?=($cake['category_id']==$cat['id']) ? 'selected' : ''?>><?=$cat['name']?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="update_cake" class="btn btn-sm btn-success">💾</button>
        <a href="?cancel_edit=1" class="btn btn-sm btn-secondary">❌</a>
    </form>
    <?php endif; ?>
    <!-- Delete -->
    <a href="?delete=<?=$cake['id']?>" class="btn btn-sm btn-danger" 
       onclick="return confirm('Delete <?=htmlspecialchars($cake['name'])?>?')">
        <i class="fas fa-trash"></i>
    </a>
</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>