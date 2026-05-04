<?php require 'config.php'; 
$errors = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($username) || empty($password)) {
        $errors = 'All fields required';
    } elseif (strlen($password) < 6) {
        $errors = 'Password must be 6+ chars';
    } else {
        // Check existing user
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $errors = 'Username taken';
        } else {
            // Hash password & insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Registered! <a href="login.php">Login now</a>';
            } else {
                $errors = 'Registration failed';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Cake Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);}</style>
</head>
<body class="min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4 text-pink">🍰 Cake Shop Register</h2>
                        <?php if($errors): ?><div class="alert alert-danger"><?=$errors?></div><?php endif; ?>
                        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password (6+ chars)" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
                            <p class="text-center">Have account? <a href="login.php">Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>