<?php
ob_start(); // <--- THIS FIXES THE REDIRECT ISSUE
session_start();
ini_set('display_errors', 1); // Show errors for debugging
error_reporting(E_ALL);

include 'db.php';
$error = "";

if (isset($_GET['registered'])) {
    $error = "Registration successful! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$user'");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            // Set Session Variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            // Check if 'role' column exists, otherwise default to resident
            $role = isset($row['role']) ? $row['role'] : 'resident';
            $_SESSION['role'] = $role;

            // REDIRECT LOGIC
            if ($role == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: resident_home.php");
                exit();
            }
        } else {
            $error = "Invalid Password";
        }
    } else {
        $error = "User not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Bagumbayan Census</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="auth-bg">
        <div class="auth-overlay"></div>
        <div class="card auth-card shadow-lg" style="width: 400px;">
            <div class="auth-header">
                <i class="fas fa-landmark fa-3x mb-2"></i>
                <h4 class="mb-0 fw-bold">Bagumbayan Census</h4>
                <small>Secure Access Portal</small>
            </div>
            <div class="card-body p-4">
                <?php if ($error) echo "<div class='alert alert-danger py-2'>$error</div>"; ?>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" name="username" class="form-control" id="floatingUser" placeholder="Username" required>
                        <label for="floatingUser"><i class="fas fa-user me-1"></i> Username</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" name="password" class="form-control" id="floatingPass" placeholder="Password" required>
                        <label for="floatingPass"><i class="fas fa-lock me-1"></i> Password</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                        LOGIN <i class="fas fa-sign-in-alt ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted small">Don't have an account?</p>
                    <a href="register.php" class="text-decoration-none fw-bold">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php ob_end_flush(); ?>