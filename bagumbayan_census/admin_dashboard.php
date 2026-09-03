<?php
session_start();
include 'db.php';

// 1. ACCESS CONTROL
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// 2. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM residents WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// 3. HANDLE UPDATE
if (isset($_POST['update_resident'])) {
    $id = $_POST['id'];
    $name = $_POST['full_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $brgy = $_POST['barangay'];
    $civil = $_POST['civil_status'];
    $contact = $_POST['contact_number'];
    $is_pwd = $_POST['is_pwd'];

    $sql = "UPDATE residents SET full_name='$name', age='$age', gender='$gender', barangay='$brgy', civil_status='$civil', contact_number='$contact', is_pwd='$is_pwd' WHERE id='$id'";
    $conn->query($sql);
    header("Location: admin_dashboard.php");
    exit();
}

// 4. STATISTICS LOGIC (Advanced Counting)
// We calculate everything in one query for speed
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN gender='Male' THEN 1 ELSE 0 END) as male,
    SUM(CASE WHEN gender='Female' THEN 1 ELSE 0 END) as female,
    SUM(CASE WHEN is_pwd='Yes' THEN 1 ELSE 0 END) as pwd,
    SUM(CASE WHEN age BETWEEN 0 AND 12 THEN 1 ELSE 0 END) as children,
    SUM(CASE WHEN age BETWEEN 13 AND 17 THEN 1 ELSE 0 END) as teens,
    SUM(CASE WHEN age BETWEEN 18 AND 35 THEN 1 ELSE 0 END) as young_adults,
    SUM(CASE WHEN age BETWEEN 36 AND 64 THEN 1 ELSE 0 END) as adults,
    SUM(CASE WHEN age >= 65 THEN 1 ELSE 0 END) as seniors
FROM residents";

$stats = $conn->query($stats_sql)->fetch_assoc();

// 5. SEARCH LOGIC
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $residents = $conn->query("SELECT * FROM residents WHERE full_name LIKE '%$search%' OR barangay LIKE '%$search%' ORDER BY id DESC");
} else {
    $residents = $conn->query("SELECT * FROM residents ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-city me-2"></i> MUNICIPALITY OF BAGUMBAYAN
            </a>
            <div class="d-flex align-items-center">
                <div class="text-white me-3 d-none d-md-block">
                    <small>Admin Panel</small><br>
                    <span class="fw-bold"><?php echo strtoupper($_SESSION['username']); ?></span>
                </div>
                <a href="logout.php" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h3 class="mb-3 text-dark fw-bold border-start border-5 border-primary ps-3">Population Overview</h3>

        <!-- Row 1: General Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white h-100 p-3">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75">Total Population</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo $stats['total']; ?></h2>
                        <i class="fas fa-users icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white h-100 p-3">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75">Males</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo $stats['male']; ?></h2>
                        <i class="fas fa-male icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-danger text-white h-100 p-3">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75">Females</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo $stats['female']; ?></h2>
                        <i class="fas fa-female icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white h-100 p-3">
                    <div class="card-body">
                        <h6 class="text-uppercase opacity-75">PWDs</h6>
                        <h2 class="display-6 fw-bold mb-0"><?php echo $stats['pwd']; ?></h2>
                        <i class="fas fa-wheelchair icon-bg"></i>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-3 text-dark fw-bold border-start border-5 border-success ps-3">Age Demographics</h3>

        <!-- Row 2: Age Groups -->
        <div class="row g-3 mb-5">
            <div class="col-md">
                <div class="card border-0 shadow-sm text-center py-3">
                    <h5 class="text-primary fw-bold"><?php echo $stats['children']; ?></h5>
                    <small class="text-muted text-uppercase fw-bold">Children<br>(0-12)</small>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm text-center py-3">
                    <h5 class="text-info fw-bold"><?php echo $stats['teens']; ?></h5>
                    <small class="text-muted text-uppercase fw-bold">Teens<br>(13-17)</small>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm text-center py-3">
                    <h5 class="text-success fw-bold"><?php echo $stats['young_adults']; ?></h5>
                    <small class="text-muted text-uppercase fw-bold">Young Adults<br>(18-35)</small>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm text-center py-3">
                    <h5 class="text-warning fw-bold"><?php echo $stats['adults']; ?></h5>
                    <small class="text-muted text-uppercase fw-bold">Adults<br>(36-64)</small>
                </div>
            </div>
            <div class="col-md">
                <div class="card border-0 shadow-sm text-center py-3">
                    <h5 class="text-danger fw-bold"><?php echo $stats['seniors']; ?></h5>
                    <small class="text-muted text-uppercase fw-bold">Seniors<br>(65+)</small>
                </div>
            </div>
        </div>

        <!-- Database Table -->
        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-secondary">Resident Database</h5>
                <form class="d-flex" method="GET">
                    <input class="form-control form-control-sm me-2 bg-light" type="search" name="search" placeholder="Search..." value="<?php echo $search; ?>">
                    <button class="btn btn-sm btn-primary" type="submit">Search</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table custom-table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>PWD</th>
                                <th>Barangay</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($residents->num_rows > 0): ?>
                                <?php while ($row = $residents->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary"><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['age']; ?></td>
                                        <td><?php echo $row['gender']; ?></td>
                                        <td>
                                            <?php if ($row['is_pwd'] == 'Yes'): ?>
                                                <span class="badge bg-warning text-dark">YES</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary">NO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row['barangay']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary editBtn"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo $row['full_name']; ?>"
                                                data-age="<?php echo $row['age']; ?>"
                                                data-gender="<?php echo $row['gender']; ?>"
                                                data-pwd="<?php echo $row['is_pwd']; ?>"
                                                data-brgy="<?php echo $row['barangay']; ?>"
                                                data-civil="<?php echo $row['civil_status']; ?>"
                                                data-contact="<?php echo $row['contact_number']; ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this record?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Resident</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" id="edit_name" class="form-control" required></div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Age</label><input type="number" name="age" id="edit_age" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>Gender</label>
                                <select name="gender" id="edit_gender" class="form-select">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col">
                                <label>PWD?</label>
                                <select name="is_pwd" id="edit_pwd" class="form-select">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3"><label>Barangay</label><input type="text" name="barangay" id="edit_brgy" class="form-control" required></div>
                        <div class="mb-3"><label>Civil Status</label><input type="text" name="civil_status" id="edit_civil" class="form-control" required></div>
                        <div class="mb-3"><label>Contact Number</label><input type="text" name="contact_number" id="edit_contact" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_resident" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editButtons = document.querySelectorAll('.editBtn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_id').value = btn.getAttribute('data-id');
                document.getElementById('edit_name').value = btn.getAttribute('data-name');
                document.getElementById('edit_age').value = btn.getAttribute('data-age');
                document.getElementById('edit_gender').value = btn.getAttribute('data-gender');
                document.getElementById('edit_pwd').value = btn.getAttribute('data-pwd');
                document.getElementById('edit_brgy').value = btn.getAttribute('data-brgy');
                document.getElementById('edit_civil').value = btn.getAttribute('data-civil');
                document.getElementById('edit_contact').value = btn.getAttribute('data-contact');
            });
        });
    </script>
</body>

</html>