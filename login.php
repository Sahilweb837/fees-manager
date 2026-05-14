<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    $user = getLoggedInUser($conn);
    redirectByRole($user['role_name']);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.status = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['name'] = $user['name'];
            
            logActivity($conn, $user['id'], "Logged In");
            redirectByRole($user['role_name']);
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found or inactive!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | School ERP Pro</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-container">

    <div class="login-box animate-fade-in">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">School ERP Pro</h2>
            <p class="text-muted">Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Enter username" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Enter password" required>
                </div>
            </div>
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#" class="text-decoration-none small">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Login Now</button>
        </form>

        <div class="text-center mt-4">
            <p class="small text-muted">Powered by Netcoder Technology</p>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
