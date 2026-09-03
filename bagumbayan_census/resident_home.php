<?php
session_start();
include 'db.php';

// 1. Security: Check if user is logged in and is a resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// 2. Handle Form Submission
if (isset($_POST['submit_census'])) {
    $name = $_POST['full_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $brgy = $_POST['barangay'];
    $civil = $_POST['civil_status'];
    $contact = $_POST['contact_number'];
    $is_pwd = $_POST['is_pwd']; // New Field

    // Insert data linked to this user
    // Note: Added 's' to types string and new variable at the end
    $stmt = $conn->prepare("INSERT INTO residents (user_id, full_name, age, gender, barangay, civil_status, contact_number, is_pwd) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisssss", $user_id, $name, $age, $gender, $brgy, $civil, $contact, $is_pwd);

    if ($stmt->execute()) {
        header("Location: resident_home.php");
        exit();
    } else {
        $msg = "Error submitting data: " . $conn->error;
    }
}

// 3. Check if data exists for this user
$check_data = $conn->query("SELECT * FROM residents WHERE user_id = '$user_id'");
$has_data = $check_data->num_rows > 0;
$my_data = $check_data->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Resident Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="#">
                <i class="fas fa-leaf me-2"></i> Bagumbayan Residents
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted d-none d-md-block">Welcome, <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php" class="btn btn-outline-secondary btn-sm rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <?php if ($has_data): ?>
                    <!-- Success Card (SHOWN IF DATA EXISTS) -->
                    <div class="card shadow-lg border-0 rounded-4 overflow-hidden mt-4">
                        <div class="card-body p-5 text-center bg-white">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                                    <i class="fas fa-check fa-3x text-success"></i>
                                </div>
                            </div>
                            <h2 class="fw-bold text-dark">Submission Complete</h2>
                            <p class="text-muted mb-4">Thank you, <span class="fw-bold text-primary"><?php echo $my_data['full_name']; ?></span>! Your census data has been securely recorded.</p>

                            <div class="card bg-light border-0 rounded-3 p-3 text-start">
                                <div class="row g-3">
                                    <div class="col-6"><small class="text-muted text-uppercase">Barangay</small><br><strong><?php echo $my_data['barangay']; ?></strong></div>
                                    <div class="col-6"><small class="text-muted text-uppercase">Civil Status</small><br><strong><?php echo $my_data['civil_status']; ?></strong></div>
                                    <div class="col-6"><small class="text-muted text-uppercase">Age</small><br><strong><?php echo $my_data['age']; ?></strong></div>
                                    <div class="col-6"><small class="text-muted text-uppercase">PWD Status</small><br><strong><?php echo $my_data['is_pwd']; ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Form Card (SHOWN IF NO DATA) -->
                    <div class="card shadow-lg border-0 rounded-4 mt-2">
                        <div class="card-header bg-primary text-white p-4">
                            <h3 class="mb-0 fw-bold">Resident Information</h3>
                            <p class="mb-0 opacity-75">Please fill out the form accurately.</p>
                        </div>
                        <div class="card-body p-5 bg-white">
                            <?php if ($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>

                            <form method="POST">
                                <h5 class="text-primary mb-3 border-bottom pb-2">Personal Details</h5>

                                <div class="form-floating mb-3">
                                    <input type="text" name="full_name" class="form-control" id="fname" placeholder="Full Name" required>
                                    <label for="fname">Full Name</label>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" name="age" class="form-control" id="age" placeholder="Age" required>
                                            <label for="age">Age</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <select name="gender" class="form-select" id="gender">
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                            <label for="gender">Gender</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <select name="is_pwd" class="form-select" id="pwd">
                                                <option value="No">No</option>
                                                <option value="Yes">Yes</option>
                                            </select>
                                            <label for="pwd">Are you PWD?</label>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="text-primary mt-4 mb-3 border-bottom pb-2">Location & Contact</h5>

                                <div class="form-floating mb-3">
                                    <input type="text" name="barangay" class="form-control" id="brgy" placeholder="Barangay" required>
                                    <label for="brgy">Barangay</label>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select name="civil_status" class="form-select" id="civil">
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
                                            <label for="civil">Civil Status</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" name="contact_number" class="form-control" id="contact" placeholder="Contact">
                                            <label for="contact">Contact Number</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="submit_census" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow">
                                    SUBMIT INFORMATION <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>