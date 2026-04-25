<?php
session_start();

// If already logged in as super_admin, go to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'super_admin') {
    header("Location: index.php");
    exit();
}

// If logged in as someone else, redirect them out
if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'super_admin') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Only allow super_admin accounts to log in here
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'super_admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']       = $row['id'];
            $_SESSION['username']      = $row['username'];
            $_SESSION['role']          = $row['role'];
            $_SESSION['branch_id']     = null; // Super admins don't have a branch
            $_SESSION['business_type'] = 'other'; // Generic fallback

            $conn->query("INSERT INTO activity_logs (user_id, action, details) VALUES ({$row['id']}, 'Admin Login', 'Super Admin Authenticated via secure portal.')");

            header("Location: index.php");
            exit();
        } else {
            $error = "Access Denied: Invalid Credentials.";
        }
    } else {
        $error = "Access Denied: Unauthorized Account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMS — Super Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .admin-icon {
            width: 72px; height: 72px;
            background: rgba(56,189,248,0.1);
            color: #38bdf8;
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px;
            margin: 0 auto 24px;
        }
        .form-control {
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            padding: 14px;
            border-radius: 10px;
        }
        .form-control:focus {
            background: #0f172a;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56,189,248,0.2);
            color: white;
        }
        .btn-admin {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-admin:hover {
            background: #0284c7;
        }
    </style>
</head>
<body>

<div class="admin-card">
    <div class="text-center mb-4">
        <div class="admin-icon"><i class="fas fa-shield-halved"></i></div>
        <h3 class="fw-bold mb-1">System Command</h3>
        <p class="text-muted small">Restricted Super Admin Access</p>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 p-3 mb-4 text-center small fw-bold">
            <i class="fas fa-lock me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Admin ID" required autocomplete="off" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        <div class="mb-4">
            <input type="password" name="password" class="form-control" placeholder="Passcode" required>
        </div>
        <button type="submit" class="btn-admin">
            <i class="fas fa-fingerprint me-2"></i> Verify Identity
        </button>
    </form>
    
    <div class="text-center mt-4">
        <span class="badge bg-slate-800 text-muted border border-secondary px-3 py-2 fw-normal" onclick="document.querySelector('input[name=\'username\']').value='sahilsandhu'; document.querySelector('input[name=\'password\']').value='12345';" style="cursor: pointer;">
            Test Mode: sahilsandhu / 12345
        </span>
    </div>
</div>

</body>
</html>
