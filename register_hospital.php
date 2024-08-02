<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blood_bank";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch regions
$sql_regions = "SELECT * FROM region";
$result_regions = $conn->query($sql_regions);

if ($result_regions === false) {
    die("Error: " . $conn->error);
}

$selected_region_id = isset($_POST['region']) ? $_POST['region'] : '';
$selected_district_id = isset($_POST['district']) ? $_POST['district'] : '';

// Fetch districts based on selected region
$districts = [];
if ($selected_region_id) {
    $sql_districts = "SELECT * FROM district WHERE Region_ID = '$selected_region_id'";
    $result_districts = $conn->query($sql_districts);

    if ($result_districts === false) {
        die("Error: " . $conn->error);
    }

    while ($row = $result_districts->fetch_assoc()) {
        $districts[] = $row;
    }
}

// Fetch wards based on selected district
$wards = [];
if ($selected_district_id) {
    $sql_wards = "SELECT * FROM ward WHERE District_ID = '$selected_district_id'";
    $result_wards = $conn->query($sql_wards);

    if ($result_wards === false) {
        die("Error: " . $conn->error);
    }

    while ($row = $result_wards->fetch_assoc()) {
        $wards[] = $row;
    }
}

// Handle form submission for registering a hospital
$registration_success = false;
if (isset($_POST['register'])) {
    $hospital_name = $_POST['hospital_name'];
    $ward_id = $_POST['ward'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $sql = "INSERT INTO hospital (Hospital_Name, Ward_ID, Contact_Number, Address) VALUES ('$hospital_name', '$ward_id', '$contact_number', '$address')";

    if ($conn->query($sql) === TRUE) {
        $registration_success = true;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

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
      <a href="admin_dashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/OIG2.jfif" alt="">
        <span class="d-none d-lg-block">Health-Connect</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <!-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> -->
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
              <a class="dropdown-item d-flex align-items-center" href="profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
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
        <a class="nav-link " href="admin_dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="add_hospital_staff.php">
        <i class="bi bi-person-add"></i>
          <span>Add Staff</span>
        </a>
      </li><!-- End medical Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="register_hospital.php">
        <i class="bi bi-hospital"></i>
          <span>Hospital</span>
        </a>
      </li><!-- End F.A.Q Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

  <div class="container mt-1">
    <div class="card mt-1">
        <div class="card-header">
            <h2>Register Hospital</h2>
        </div>
        <div class="card-body">
            <form action="register_hospital.php" method="post">
                <!-- First Row -->
                <div class="row mb-2">
                    <div class="col-md-6">


                       <label for="hospital_name">Hospital Name:</label>
                        <input type="text" class="form-control w-auto" id="hospital_name" name="hospital_name" required>
                    </div>
                    <div class="col-md-6">
                    <div class="form-group">
                         <label for="region">Region:</label>
                          <select id="region" class="form-control" name="region" required onchange="this.form.submit()">
                           <option value="">Select Region</option>
                              <?php while($row = $result_regions->fetch_assoc()): ?>
                                     <option value="<?php echo $row['Region_ID']; ?>" <?php if ($row['Region_ID'] == $selected_region_id) echo 'selected'; ?>><?php echo $row['Region_Name']; ?></option>
                                     <?php endwhile; ?>
                          </select>
                    </div>
                  </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                    <div class="form-group">
                       <label for="district">District:</label>
                          <select id="district" class="form-control" name="district" required onchange="this.form.submit()">
                         <option value="">Select Region First</option>
                         <?php foreach ($districts as $district): ?>
                         <option value="<?php echo $district['District_ID']; ?>" <?php if ($district['District_ID'] == $selected_district_id) echo 'selected'; ?>><?php echo $district['District_Name']; ?></option>
                         <?php endforeach; ?>
                     </select>
                    </div>
                    </div>
                    <div class="col-md-6">
                    <div class="form-group">
                      <label for="ward">Ward:</label>
                        <select id="ward" class="form-control" name="ward" required>
                            <option value="">Select District First</option>
                          <?php foreach ($wards as $ward): ?>
                           <option value="<?php echo $ward['Ward_ID']; ?>"><?php echo $ward['Ward_Name']; ?></option>
                          <?php endforeach; ?>
                        </select>
                    </div>
                    </div>
                </div>
                <!-- Third Row -->
                <div class="row mb-2">
                    <div class="col-md-6">
                    <div class="form-group">
                   <label for="contact_number">Contact Number:</label>
                   <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                   </div>
                    </div>
                    <div class="col-md-6">
                    <div class="form-group">
                     <label for="address">Address:</label>
                     <input type="text" class="form-control" id="address" name="address" required>
                     </div>
                    </div>
                </div>
                <!-- Fourth Row -->
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="department">Department:</label>
                            <input type="text" id="department" name="department" class="form-control w-auto" required>
                        </div>
                    </div>
                    
                </div>
        
                
                <button type="submit" name="register" class="btn btn-primary">Register</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>  


    

          </div>
        </div><!-- End Left side columns -->
      </div>
    </section>

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
