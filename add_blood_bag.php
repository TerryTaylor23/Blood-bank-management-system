<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Fetch user details from the database, including first name, last name, and role
$user_id = $_SESSION['user_id'];
$query = $con->prepare("
    SELECT u.username, u.user_type, hs.firstname, hs.lastname, hs.Staff_Role
    FROM user u
    LEFT JOIN hospital_staff hs ON u.user_id = hs.User_ID
    WHERE u.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $user_type = $user['user_type'];
    $firstname = $user['firstname'];
    $lastname = $user['lastname'];
    $role = $user['Staff_Role'];
} else {
    // If user is not found, redirect to login page
    header('Location: login.php');
    exit();
}
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">

<title>Health-Connect</title>
<meta content="" name="description">
<meta content="" name="keywords">

<!-- Favicons -->
<link href="assets/img/micon.png" rel="icon">
<link href="assets/img/micon.png" rel="apple-touch-icon">

<!-- Google Fonts -->
<link href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
<link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
<link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
<link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
<link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

<!-- Template Main CSS File -->
<link href="assets/css/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="hospital_dashboard.php" class="logo d-flex align-items-center">
            <img src="assets/img/OIG2.jfif" alt="">
            <span class="d-none d-lg-block">Health-Connect</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h6>
                        <span><?php echo htmlspecialchars($role); ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="profile.html">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link " href="hospital_dashboard.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="add_donor.php">
                <i class="bi bi-person-add"></i>
                <span>Add Donors</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="request.php">
                <i class="bx bxs-donate-blood"></i>
                <span>Blood Request</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="blood_bag.php">
                <i class="bi bi-bag"></i>
                <span>View Blood Bag</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="view_donors.php">
                <i class="bi bi-people"></i>
                <span>View Donors</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Main Content -->
<main id="main" class="main">
    <div class="container mt-4">
        <h2>Add Blood Bag</h2>
        <form action="add_blood_bag.php" method="POST">
            <div class="form-group">
                <label for="bloodType">Blood Type</label>
                <select class="form-control" id="bloodType" name="Blood_Type" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                </select>
            </div>
            <div class="form-group">
                <label for="rhFactor">Rh Factor</label>
                <select class="form-control" id="rhFactor" name="Rh_Factor" required>
                    <option value="+">+</option>
                    <option value="-">-</option>
                </select>
            </div>
            <div class="form-group">
                <label for="expiryDate">Expiry Date</label>
                <input type="date" class="form-control" id="expiryDate" name="Expiry_Date" required>
            </div>
            <div class="form-group">
                <label for="donorID">Donor</label>
                <select class="form-control" id="donorID" name="Donor_ID" required>
                    <?php
                    include('server.php');
                    $donorQuery = "SELECT Donor_ID, firstname, lastname FROM donor";
                    $donorResult = $conn->query($donorQuery);
                    while ($donorRow = $donorResult->fetch_assoc()) {
                        $donorID = htmlspecialchars($donorRow['Donor_ID']);
                        $donorName = htmlspecialchars($donorRow['firstname'] . ' ' . $donorRow['lastname']);
                        echo "<option value='$donorID'>$donorName</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="hospitalID">Hospital</label>
                <select class="form-control" id="hospitalID" name="Hospital_ID" required>
                    <?php
                    $hospitalQuery = "SELECT Hospital_ID, Hospital_Name FROM hospital";
                    $hospitalResult = $conn->query($hospitalQuery);
                    while ($hospitalRow = $hospitalResult->fetch_assoc()) {
                        $hospitalID = htmlspecialchars($hospitalRow['Hospital_ID']);
                        $hospitalName = htmlspecialchars($hospitalRow['Hospital_Name']);
                        echo "<option value='$hospitalID'>$hospitalName</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Blood Bag</button>
        </form>
    </div>
</main>

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
    <div class="copyright">
        &copy; 2023 <strong><span>Health-Connect</span></strong>. All Rights Reserved
    </div>
</footer>

<!-- Vendor JS Files -->
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/chart.js/chart.umd.js"></script>
<script src="assets/vendor/echarts/echarts.min.js"></script>
<script src="assets/vendor/quill/quill.min.js"></script>
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="assets/vendor/tinymce/tinymce.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="assets/js/main.js"></script>

<!-- Custom JavaScript to fetch last donation date and set expiry date -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const donorIDSelect = document.getElementById('donorID');
    const expiryDateInput = document.getElementById('expiryDate');

    donorIDSelect.addEventListener('change', function() {
        const donorID = this.value;
        if (donorID) {
            fetch(`get_donor_last_donation.php?donor_id=${donorID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.last_donation_date) {
                        const lastDonationDate = new Date(data.last_donation_date);
                        lastDonationDate.setDate(lastDonationDate.getDate() + 42);
                        expiryDateInput.value = lastDonationDate.toISOString().split('T')[0];
                    }
                });
        }
    });
});
</script>

</body>
</html>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodType = $_POST['Blood_Type'];
    $rhFactor = $_POST['Rh_Factor'];
    $expiryDate = $_POST['Expiry_Date'];
    $donorID = $_POST['Donor_ID'];
    $hospitalID = $_POST['Hospital_ID'];

    // Sanitize input
    $bloodType = $con->real_escape_string($bloodType);
    $rhFactor = $con->real_escape_string($rhFactor);
    $expiryDate = $con->real_escape_string($expiryDate);
    $donorID = $con->real_escape_string($donorID);
    $hospitalID = $con->real_escape_string($hospitalID);

    // Insert into database
    $stmt = $con->prepare("
        INSERT INTO blood_bag (Blood_Type, Rh_Factor, Expiry_Date, Donor_ID, Hospital_ID)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssii', $bloodType, $rhFactor, $expiryDate, $donorID, $hospitalID);
    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Blood bag added successfully'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to add blood bag'
            });
        </script>";
    }
    $stmt->close();
}
$con->close();
?>
