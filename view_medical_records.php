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

// Fetch user details from the database
$query = $con->prepare("SELECT username, user_type FROM user WHERE user_id = ?");
if ($query === false) {
    die("MySQL prepare statement error: " . $con->error);
}
$query->bind_param("i", $user_id);
if (!$query->execute()) {
    die("MySQL execute statement error: " . $query->error);
}
$result = $query->get_result();
if ($result === false) {
    die("MySQL get result error: " . $query->error);
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $user_type = $user['user_type'];
} else {
    // If user is not found, redirect to login page
    header('Location: login.php');
    exit();
}

$query->close();

// Ensure the user is a donor
if ($user_type !== 'donor') {
    die("Access denied: You do not have permission to view this page.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Health-Connect</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
  <link href="assets/img/micon.png" rel="icon">
  <link href="assets/img/micon.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="assets/css/style.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
      <a href="donor_dashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/OIG2.jfif" alt="">
        <span class="d-none d-lg-block">Health-Connect</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li>
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($username); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($username); ?></h6>
              <span><?php echo htmlspecialchars($user_type); ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="donor_profile.php">
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
          </ul>
        </li>
      </ul>
    </nav>
  </header>

  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link " href="donor_dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="view_medical_records.php">
          <i class="bi bi-book"></i>
          <span>Medical Records</span>
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link collapsed" href="eligibility.php">
          <i class="bi bi-ui-checks"></i>
          <span>Eligibility Status</span>
        </a>
      </li>
    </ul>
  </aside>

  <main id="main" class="main">
    <?php
    // Fetch the donor's medical records
    $donorId = $user_id;

    if ($donorId !== null) {
        $sql = "SELECT * FROM medical_records WHERE Donor_ID = ?";
        $stmt = $con->prepare($sql);
        if ($stmt === false) {
            die("MySQL prepare statement error: " . $con->error);
        }
        $stmt->bind_param("i", $donorId);
        if (!$stmt->execute()) {
            die("MySQL execute statement error: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if ($result === false) {
            die("MySQL get result error: " . $stmt->error);
        }
        $medicalRecords = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (!empty($medicalRecords)) {
            echo "<h2>Medical Records</h2>";
            echo "<table class='table table-bordered'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Record ID</th>";
            echo "<th>Medical History</th>";
            echo "<th>Height</th>";
            echo "<th>Weight</th>";
            echo "<th>Age</th>";
            echo "<th>Last Update</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            foreach ($medicalRecords as $record) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($record['Record_ID']) . "</td>";
                echo "<td>" . nl2br(htmlspecialchars($record['Medical_History'])) . "</td>";
                echo "<td>" . htmlspecialchars($record['Height']) . "</td>";
                echo "<td>" . htmlspecialchars($record['Weight']) . "</td>";
                echo "<td>" . htmlspecialchars($record['Age']) . "</td>";
                echo "<td>" . htmlspecialchars($record['Last_Update']) . "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>No medical records found. <a href='add_medical_record.php'>Add your medical records now</a>.</p>";
        }
    } else {
        echo "<p>Please provide a valid donor ID.</p>";
    }

    $con->close();
    ?>
  </main>



  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Health-Connect</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      Designed by <strong>Taylor</strong>
    </div>
  </footer>

  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
