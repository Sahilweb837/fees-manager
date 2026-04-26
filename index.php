<?php
session_start();

// If already logged in, redirect properly based on role
if (isset($_SESSION['user_id'])) {
    $r = $_SESSION['role'] ?? '';
    $t = $_SESSION['business_type'] ?? 'other';
    $panel_map = [
        'school'     => 'school/dashboard.php',
        'college'    => 'college/dashboard.php',
        'dispensary' => 'dispensary/dashboard.php',
        'hotel'      => 'hotel/dashboard.php',
        'shop'       => 'shop/dashboard.php',
        'restaurant' => 'restaurant/dashboard.php',
        'inventory'  => 'inventory/dashboard.php',
        'other'      => 'pages/dashboard.php',
    ];
    if ($r == 'super_admin') {
        header("Location: admin/index.php"); exit();
    } else if (isset($panel_map[$t])) {
        header("Location: " . $panel_map[$t]); exit();
    } else {
        header("Location: pages/dashboard.php"); exit();
    }
}

require_once 'includes/db.php';

$error = "";
$is_central_redirect = isset($_GET['redirect']) && $_GET['redirect'] === 'central';
if ($is_central_redirect && empty($error)) {
    $error = "Please login using the Central Admin portal.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $selected_industry = $_POST['industry_type'] ?? '';

    if (empty($selected_industry)) {
        $error = "Please select your industry portal type.";
    } else {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.password, u.role, u.employee_id, u.full_name, u.branch_id, b.business_type, b.branch_name
            FROM users u
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $actual_industry = $row['business_type'] ?? 'other';

                if ($row['role'] === 'super_admin' && $selected_industry !== 'central') {
                    $error = "Super Admins must use the <strong>Central Admin</strong> portal tile.";
                } elseif ($row['role'] !== 'super_admin' && $actual_industry !== $selected_industry) {
                    $error = "Access Denied: Your account belongs to the <strong>" . ucfirst($actual_industry) . "</strong> portal, not <strong>" . ucfirst($selected_industry) . "</strong>.";
                } else {
                    $_SESSION['user_id']       = $row['id'];
                    $_SESSION['username']      = $row['username'];
                    $_SESSION['full_name']     = $row['full_name'] ?? $row['username'];
                    $_SESSION['employee_id']   = $row['employee_id'] ?? '';
                    $_SESSION['role']          = $row['role'];
                    $_SESSION['branch_id']     = $row['branch_id'];
                    $_SESSION['branch_name']   = $row['branch_name'] ?? 'Head Office';
                    $_SESSION['business_type'] = $actual_industry;

                    logActivity($conn, $row['id'], "Login", "Logged in via " . ucfirst($selected_industry) . " portal.");

                    $panel_map = [
                        'school'     => 'school/dashboard.php',
                        'college'    => 'college/dashboard.php',
                        'dispensary' => 'dispensary/dashboard.php',
                        'hotel'      => 'hotel/dashboard.php',
                        'shop'       => 'shop/dashboard.php',
                        'restaurant' => 'restaurant/dashboard.php',
                        'inventory'  => 'inventory/dashboard.php',
                        'other'      => 'pages/dashboard.php',
                    ];

                    if ($row['role'] === 'super_admin') {
                        header("Location: admin/index.php");
                    } elseif (isset($panel_map[$actual_industry])) {
                        header("Location: " . $panel_map[$actual_industry]);
                    } else {
                        header("Location: pages/dashboard.php");
                    }
                    exit();
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No active account found for that username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMS — Multi-Industry Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background orbs */
        body::before {
            content: '';
            position: fixed;
            top: -150px; left: -150px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(255,122,0,0.25) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -150px; right: -150px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 10s ease-in-out infinite reverse;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.4);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #ff7a00, #ff4d00);
            padding: 24px 32px;
            text-align: center;
            position: relative;
        }
        .login-header h2 {
            color: white;
            font-weight: 800;
            font-size: 1.3rem;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .login-body { padding: 32px; }

        .industry-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }
        .industry-tile {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.2s;
        }
        .industry-tile i {
            font-size: 1.2rem;
            margin-bottom: 6px;
            display: block;
        }
        .industry-tile span {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .industry-tile:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .industry-tile.selected {
            background: rgba(255,122,0,0.2);
            border-color: #ff7a00;
            color: #ff7a00;
            box-shadow: 0 4px 12px rgba(255,122,0,0.2);
        }

        .form-label {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            color: white;
            padding: 14px 16px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .form-control:focus {
            background: rgba(255,255,255,0.12);
            border-color: #ff7a00;
            box-shadow: 0 0 0 3px rgba(255,122,0,0.2);
            color: white;
            outline: none;
        }

        .input-group-text {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-right: none;
            color: rgba(255,255,255,0.5);
            border-radius: 12px 0 0 12px;
        }
        .input-group .form-control { border-left: none; border-radius: 0 12px 12px 0; }
        .input-group:focus-within .input-group-text {
            border-color: #ff7a00;
            background: rgba(255,122,0,0.1);
            color: #ff7a00;
        }

        .btn-login {
            background: linear-gradient(135deg, #ff7a00, #ff4d00);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 700;
            width: 100%;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            box-shadow: 0 8px 24px rgba(255,122,0,0.35);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 32px rgba(255,122,0,0.45);
        }

        .alert-danger {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 0.875rem;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .credential-hint {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            padding: 16px 20px;
            margin-top: 24px;
        }
        .credential-hint .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.4);
            font-weight: 600;
            margin-bottom: 10px;
        }
        .cred-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }
        .cred-row + .cred-row { border-top: 1px solid rgba(255,255,255,0.06); }
        .cred-key { color: rgba(255,255,255,0.5); font-size: 0.8rem; }
        .cred-val {
            font-weight: 700;
            font-size: 0.85rem;
            color: #ff7a00;
            font-family: 'Courier New', monospace;
            cursor: pointer;
        }
        .cred-val:hover { color: #ffaa55; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <h2><i class="fas fa-layer-group me-2"></i>Select Portal & Login</h2>
        </div>

        <div class="login-body">
            <?php if($error): ?>
                <div class="alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="industry_type" id="industry_type" value="<?php echo isset($_POST['industry_type']) ? htmlspecialchars($_POST['industry_type']) : ''; ?>">
                
                <label class="form-label text-center d-block mb-3">1. Select Your Sector</label>
                <div class="industry-grid">
                    <div class="industry-tile" onclick="selectIndustry('central', this)" style="border-color:rgba(255,215,0,0.5);"><i class="fas fa-shield-halved" style="color:#ffd700;"></i><span style="color:#ffd700;">Central Admin</span></div>
                    <div class="industry-tile" onclick="selectIndustry('school', this)"><i class="fas fa-school"></i><span>School</span></div>
                    <div class="industry-tile" onclick="selectIndustry('college', this)"><i class="fas fa-university"></i><span>College</span></div>
                    <div class="industry-tile" onclick="selectIndustry('hotel', this)"><i class="fas fa-hotel"></i><span>Hotel</span></div>
                    <div class="industry-tile" onclick="selectIndustry('dispensary', this)"><i class="fas fa-clinic-medical"></i><span>Clinic</span></div>
                    <div class="industry-tile" onclick="selectIndustry('shop', this)"><i class="fas fa-store"></i><span>Shop</span></div>
                    <div class="industry-tile" onclick="selectIndustry('restaurant', this)"><i class="fas fa-utensils"></i><span>Restaurant</span></div>
                    <div class="industry-tile" onclick="selectIndustry('other', this)"><i class="fas fa-briefcase"></i><span>Other</span></div>
                </div>

                <label class="form-label d-block mt-2">2. Enter Credentials</label>
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" required
                               placeholder="Login ID"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" required
                               placeholder="Password">
                        <button type="button" class="btn btn-link text-white opacity-50 px-3"
                                onclick="togglePwd()" style="border:1px solid rgba(255,255,255,0.12); border-left:none; border-radius:0 12px 12px 0; background:rgba(255,255,255,0.08);">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>Authenticate
                </button>
            </form>

            <div class="credential-hint">
                <div class="label"><i class="fas fa-shield-alt me-1"></i> Default Login Credentials</div>
                <div class="cred-row">
                    <span class="cred-key">Super Admin</span>
                    <span class="cred-val" onclick="fillLogin('superadmin','admin123','central')">superadmin / admin123 → Central Admin</span>
                </div>
                <div class="cred-row" style="border-top:1px solid rgba(255,255,255,0.06);margin-top:6px;padding-top:6px;">
                    <span class="cred-key" style="font-size:0.72rem;color:rgba(255,255,255,0.35);">After login: Create a Branch → Create an Admin for that branch → they log in via that industry tile.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectIndustry(type, element) {
    document.getElementById('industry_type').value = type;
    document.querySelectorAll('.industry-tile').forEach(el => el.classList.remove('selected'));
    if (element) {
        element.classList.add('selected');
    } else {
        // Fallback for fillLogin function
        const tiles = document.querySelectorAll('.industry-tile');
        for (let tile of tiles) {
            if (tile.getAttribute('onclick').includes("'" + type + "'")) {
                tile.classList.add('selected');
                break;
            }
        }
    }
}

// Reselect if validation failed
const preSelected = document.getElementById('industry_type').value;
if (preSelected) {
    selectIndustry(preSelected, null);
} else if (<?php echo $is_central_redirect ? 'true' : 'false'; ?>) {
    selectIndustry('central', document.querySelector('.industry-tile[onclick*="central"]'));
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (!document.getElementById('industry_type').value) {
        e.preventDefault();
        alert('Please select your sector (icon tile) before authenticating.');
    }
});

function togglePwd() {
    const inp  = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

function fillLogin(user, pass, type) {
    document.querySelector('input[name="username"]').value = user;
    document.getElementById('password').value = pass;
    document.getElementById('password').type  = 'text';
    document.getElementById('eye-icon').classList.replace('fa-eye','fa-eye-slash');
    selectIndustry(type, null);
}
</script>
</body>
</html>
