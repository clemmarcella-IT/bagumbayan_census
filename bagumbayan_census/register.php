<?php
include 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize input
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 2. FORCE ROLE TO RESIDENT (No selection needed)
    $role = 'resident';

    // 3. Check if username exists
    $check = $conn->query("SELECT id FROM users WHERE username='$user'");
    if ($check->num_rows > 0) {
        $message = "Username already taken.";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$user', '$pass', '$role')";
        if ($conn->query($sql) === TRUE) {
            header("Location: index.php?registered=1");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - Bagumbayan Census</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="auth-bg">
        <div class="auth-overlay"></div>
        <div class="card auth-card shadow-lg" style="width: 450px;">
            <div class="auth-header bg-success">
                <i class="fas fa-user-plus fa-3x mb-2"></i>
                <h4 class="mb-0 fw-bold">Resident Registration</h4>
                <small>Create your account to submit census data</small>
            </div>
            <div class="card-body p-4">
                <?php if ($message) echo "<div class='alert alert-danger py-2'>$message</div>"; ?>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" name="username" class="form-control" id="regUser" placeholder="Username" required>
                        <label for="regUser">Create Username</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" name="password" class="form-control" id="regPass" placeholder="Password" required>
                        <label for="regPass">Create Password</label>
                    </div>

                    <!-- REMOVED: The Admin/Resident Selection Dropdown -->

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                        REGISTER ACCOUNT
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>