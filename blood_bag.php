<?php
session_start();

// Database connection
$con = new mysqli("localhost", "root", "", "blood_bank");

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database, including first name, last name, and role
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
$con->close();
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
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="assets/css/style.css" rel="stylesheet">
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
        </div><!-- End Logo -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <!--<img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">-->
                        <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></span>
                    </a><!-- End Profile Image Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h6>
                            <span><?php echo htmlspecialchars($role); ?></span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="profile.html">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->
            </ul>
        </nav><!-- End Icons Navigation -->
    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link " href="hospital_dashboard.php">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li><!-- End Dashboard Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="add_donor.php">
                    <i class="bi bi-person-add"></i>
                    <span>Add Donors</span>
                </a>
            </li><!-- End Medical Records Page Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="request.php">
                    <i class="bx bxs-donate-blood"></i>
                    <span>Blood Request</span>
                </a>
            </li><!-- End Blood Transfusion Request Page Nav -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="blood_bag.php">
                    <i class="bi bi-bag"></i>
                    <span>View Blood Bag</span>
                </a>
            </li><!-- End Blood Transfusion Request Page Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="view_donors.php">
                    <i class="bi bi-people"></i>
                    <span>View Donors</span>
                </a>
            </li><!-- End Donors Page Nav -->
        </ul>
    </aside><!-- End Sidebar -->

    <main id="main" class="main">
    <?php
    include('server.php');

    // Query to get blood bag stock levels grouped by blood type, Rh factor, status, and hospital
    $query = "
        SELECT Blood_Type, Rh_Factor, Status, COUNT(*) as Quantity, hospital.Hospital_Name 
        FROM blood_bag 
        JOIN hospital ON blood_bag.Hospital_ID = hospital.Hospital_ID 
        GROUP BY Blood_Type, Rh_Factor, Status, hospital.Hospital_Name
    ";
    $result = $conn->query($query);

    echo "<div class='container mt-4'>";
    echo "<h2>Blood stock</h2>";
    echo "<table class='table table-bordered table-striped mt-3'>";
    echo "<thead class='thead-dark'><tr><th>Blood Type</th><th>Rh Factor</th><th>Quantity</th><th>Hospital</th><th>Status</th></thead>";
    echo "<tbody>";

    while ($row = $result->fetch_assoc()) {
        $bloodType = htmlspecialchars($row['Blood_Type']);
        $rhFactor = htmlspecialchars($row['Rh_Factor']);
        $quantity = htmlspecialchars($row['Quantity']);
        $hospitalName = htmlspecialchars($row['Hospital_Name']);
        $status = htmlspecialchars($row['Status']);

        // Determine the row color based on the status
        $statusColor = '';
        if ($status == 'Available') {
            $statusColor = 'bg-success text-white';
        } elseif ($status == 'Reserved') {
            $statusColor = 'bg-warning text-dark';
        } elseif ($status == 'Discarded') {
            $statusColor = 'bg-danger text-white';
        }
        
        echo "<tr class='$statusColor'>";
        echo "<td>$bloodType</td>";
        echo "<td>$rhFactor</td>";
        echo "<td>$quantity</td>";
        echo "<td>$hospitalName</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    echo "<div class='container mt-4'>";
    echo "<a href='add_blood_bag.php' class='btn btn-primary'>Add Blood Bag</a></br></br>";
    echo "<a href='view_requests.php' class='btn btn-primary'>View Request</a>";
    echo "</div>";
    $conn->close();
    ?>

    </main><!-- End #main -->

    <!-- Footer -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>Health-Connect</span></strong>. All Rights Reserved
        </div>
        <div class="credits">
            Designed by <strong>Taylor</strong>
        </div>
    </footer><!-- End Footer -->

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

</body>
</html>
