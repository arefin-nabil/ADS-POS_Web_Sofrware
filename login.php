<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean input
    $username = clean($_POST['username']);
    $password = $_POST['password'];

    // Prepare query
    $stmt = $conn->prepare("SELECT id, username, password, fullname, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Fetch result correctly (important)
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password - support both md5 (old) and password_hash (new)
        $passwordValid = false;

        // Check if it's a bcrypt hash (new method)
        if (password_verify($password, $user['password'])) {
            $passwordValid = true;
        }
        // Check if it's md5 (old method - for backwards compatibility)
        elseif (md5($password) === $user['password']) {
            $passwordValid = true;

            // Update to bcrypt for security
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$newHash' WHERE id = {$user['id']}");
        }

        if ($passwordValid) {
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            logActivity($conn, $user['id'], 'User logged in');

            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 576px) {
            .login-card {
                margin: 10px;
            }

            .login-card .card-body {
                padding: 2rem 1.5rem !important;
            }

            .login-card h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shop-window display-1 text-primary"></i>
                            <h2 class="mt-3">POS System</h2>
                            <p class="text-muted">Sign in to continue</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" name="username" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </form>

                        <div class="mt-4 text-center text-muted small">
                            <p>Default Login: admin / admin123</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>