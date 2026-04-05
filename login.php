<?php require 'config.php'; 
$errors = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = mysqli_prepare($conn, "SELECT id, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // TEMP FIX: Accept plain text OR hashed passwords
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            $errors = 'Wrong password';
        }
    } else {
        $errors = 'User not found';
    }
    mysqli_stmt_close($stmt); // Clean close
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Cake Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);}</style>
</head>
<body class="min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">🍰 Cake Shop Login</h2>
                        <?php if($errors): ?><div class="alert alert-danger"><?=$errors?></div><?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                            <p class="text-center">No account? <a href="register.php">Register</a></p>
                            
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>