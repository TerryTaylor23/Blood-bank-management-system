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
$query->bind_param("i", $user_id);  
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $user_type = $user['user_type'];
} else {
    // If user is not found, redirect to login page
    header('Location: login.php');
    exit();
}

// Fetch the next donation date if the user is a donor
$next_donation_date = '';
if ($user_type === 'donor') {
    $donor_query = $con->prepare("SELECT last_donation_date FROM donor WHERE Donor_ID = ?");
    $donor_query->bind_param("i", $user_id);
    $donor_query->execute();
    $donor_result = $donor_query->get_result();
    if ($donor_result->num_rows == 1) {
        $donor_data = $donor_result->fetch_assoc();
        $last_donation_date = $donor_data["last_donation_date"];
        $next_donation_date = date('Y-m-d', strtotime($last_donation_date . ' + 56 days'));
    }
    $donor_query->close();
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="assets/css/style.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="donor_dashboard.php" class="logo d-flex align-items-center">
      <img src="assets/img/OIG2.jfif" alt="">
        <span class="d-none d-lg-block">Health-Connect</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <!-- <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div>End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->
        
        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <!-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> -->
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($username); ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
            <h6><?php echo htmlspecialchars($username); ?></h6>
            <span><?php echo htmlspecialchars($user_type); ?></span>
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
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="donor_dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="view_medical_records.php">
          <i class="bi bi-book"></i>
          <span>Medical Records</span>
        </a>
      </li><!-- End medical Page Nav -->


      <li class="nav-item">
        <a class="nav-link collapsed" href="eligibility.php">
        <i class="bi bi-ui-checks"></i>
          <span>Eligibility Status</span>
        </a>
      </li><!-- End Contact Page Nav -->

     

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">
  <?php
// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $hasHealthConditions = isset($_POST['haskifafa']);
    $hasTattoosOrPiercings = isset($_POST['hasTattoosOrPiercings']);
    $hadCovid = isset($_POST['hadCovid']);
    $hascancer = isset($_POST['hascancer']);
    $hasdiabetes = isset($_POST['hasdiabetes']);
    $covidRecoveryDays = $_POST['covidRecoveryDays'];
    $receivedCovidVaccine = isset($_POST['receivedCovidVaccine']);
    $vaccineDays = $_POST['vaccineDays'];

    // Eligibility checks based on the provided information
    $eligibilityMessage = "";
    $isEligible = true;

    // Age check
    if ($age < 16 || $age > 65) {
        $eligibilityMessage .= "You must be between 16 and 65 years old to donate. ";
        $isEligible = false;
    }

    // Weight check
    if ($weight < 50) {
        $eligibilityMessage .= "You must weigh at least 50 kg to donate. ";
        $isEligible = false;
    }

    // Health condition check
    if ($hasHealthConditions) {
        $eligibilityMessage .= "You Cannot donate blood if you have epilepsy. ";
        $isEligible = false;
    }

    // Tattoo/piercing check
    if ($hasTattoosOrPiercings) {
        $eligibilityMessage .= "You cannot donate blood if you have received tattoos or body piercings in the past 12 months. ";
        $isEligible = false;
    }

    // COVID-19 recovery check
    if ($hadCovid) {
        if ($covidRecoveryDays < 28) {
            $eligibilityMessage .= "You can only donate blood after 28 days from recovering from COVID-19. ";
            $isEligible = false;
        }
    }

    // COVID-19 vaccination check
    if ($receivedCovidVaccine) {
        if ($vaccineDays < 14) {
            $eligibilityMessage .= "You can donate blood after 2 weeks from receiving the COVID-19 vaccine. ";
            $isEligible = false;
        }
    }

    // Final eligibility message
    if ($isEligible) {
        $eligibilityMessage = "Congratulations! You are eligible to donate blood.";
    }
}
?>

<div class="container">
    <h1 class="text-center mt-3">Blood Donation Eligibility Checker</h1>
    <form method="post" class="form-horizontal mt-4">
        <div class="form-group">
            <label for="age" class="col-sm-2 control-label">Age:</label>
            <div class="col-sm-10">
                <input type="number" name="age" id="age" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label for="weight" class="col-sm-2 control-label">Weight (kg):</label>
            <div class="col-sm-10">
                <input type="number" name="weight" id="weight" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="haskifafa">
                        Epilepsy(Kifafa)
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="hasTattoosOrPiercings" id="hasTattoosOrPiercings">
                        I have received tattoos or body piercings in the past 12 months
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="hascancer">
                        Cancer
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="hasdiabetes">
                        Diabetes(Kisukari)
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="hadCovid" id="hadCovid">
                        I have had COVID-19
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="covidRecoveryDays" class="col-sm-2 control-label">Days since COVID-19 recovery:</label>
            <div class="col-sm-10">
                <input type="number" name="covidRecoveryDays" id="covidRecoveryDays" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="receivedCovidVaccine" id="receivedCovidVaccine">
                        I have received the COVID-19 vaccine
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="vaccineDays" class="col-sm-2 control-label">Days since COVID-19 vaccination:</label>
            <div class="col-sm-10">
                <input type="number" name="vaccineDays" id="vaccineDays" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <input type="submit" value="Check Eligibility" class="btn btn-primary">
            </div>
        </div>
        <?php if (isset($eligibilityMessage)) { ?>
            <div class="alert alert-<?php echo (strpos($eligibilityMessage, 'Congratulations') !== false) ? 'success' : 'danger'; ?>">
                <?php echo $eligibilityMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
    </form>
</div>
</main><!-- End #main -->

  <!-- ======= Footer ======= -->
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
