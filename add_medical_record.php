<?php
session_start();
include("server.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$query = $conn->prepare("
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donorId = $_POST['donor_id'];
    $medicalHistory = $_POST['medical_history'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $age = $_POST['age'];
    $lastUpdate = date('Y-m-d');

    $query = $conn->prepare("INSERT INTO medical_records (Donor_ID, Medical_History, Height, Weight, Age, Last_Update) VALUES (?, ?, ?, ?, ?, ?)");
    $query->bind_param("issdis", $donorId, $medicalHistory, $height, $weight, $age, $lastUpdate);

    if ($query->execute()) {
        $_SESSION['success_message'] = "Medical record added successfully.";
    } else {
        $_SESSION['error_message'] = "Error adding medical record.";
    }

    $query->close();
    header('Location: add_medical_record.php?donor_id=' . $donorId);
    exit();
}

// Fetch donor ID from GET request
$donorId = isset($_GET['donor_id']) ? intval($_GET['donor_id']) : 0;

$conn->close();
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
          <span>Blood  Request</span>
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
  <div class="container mt-3">
    <h2 class="mb-4">Add Medical Record</h2>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <form action="add_medical_record.php" method="post">
        <input type="hidden" name="donor_id" value="<?php echo htmlspecialchars($donorId); ?>">
        <div class="form-group">
            <label for="medical_history">Medical History</label>
            <textarea class="form-control" id="medical_history" name="medical_history" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label for="height">Height (cm)</label>
            <input type="number" class="form-control" id="height" name="height" required>
        </div>
        <div class="form-group">
            <label for="weight">Weight (kg)</label>
            <input type="number" class="form-control" id="weight" name="weight" required>
        </div>
        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" class="form-control" id="age" name="age" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Medical Record</button>
        <button type="button" class="btn btn-secondary" onclick="window.print()">Print</button>
    </form>
</div>
</main>
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
