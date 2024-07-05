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
  <script>
    // JavaScript to set the max date for the lastDonationDate input
    window.onload = function() {
      var today = new Date().toISOString().split('T')[0];
      document.getElementById('lastDonationDate').setAttribute('max', today);
    };
    // JavaScript to validate email format and uniqueness
    function validateForm() {
      var email = document.getElementById('email').value;
      var username = document.getElementById('username').value;

      // Validate email format
      var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      if (!emailPattern.test(email)) {
        alert('Please enter a valid email address.');
        return false;
      }

      // Check if email and username are unique via AJAX
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'check_unique.php', false); // Synchronous request
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.send('email=' + encodeURIComponent(email) + '&username=' + encodeURIComponent(username));

      if (xhr.responseText !== 'OK') {
        alert(xhr.responseText);
        return false;
      }

      return true;
    }
  </script>
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
          <span>Add Donor</span>
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
    <div class="container mt-1">
      <div class="card mt-1">
        <div class="card-header">
          <h2>Add Donor</h2>
        </div>
        <div class="card-body">
          <form id="donorForm" action="add_donor.php" method="post">
            <!-- Personal Information Row -->
            <div class="row mb-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="firstname">First Name:</label>
                  <input type="text" id="firstname" name="firstname" class="form-control w-auto" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="lastname">Last Name:</label>
                  <input type="text" id="lastname" name="lastname" class="form-control w-auto" required>
                </div>
              </div>
            </div>
            <!-- Sex and Username Row -->
            <div class="row mb-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sex">Sex:</label>
                  <select id="sex" name="sex" class="form-control w-auto" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="username">Username:</label>
                  <input type="text" id="username" name="username" class="form-control w-auto" required>
                </div>
              </div>
            </div>
            <!-- Password and User Type Row -->
            <div class="row mb-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input type="password" id="password" name="password" class="form-control w-auto" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="usertype">User Type:</label>
                  <select id="usertype" name="usertype" class="form-control w-auto" required>
                    <option value="donor">Donor</option>
                  </select>
                </div>
              </div>
            </div>
            <!-- Blood Type and Rhesus Factor Row -->
            <div class="row mb-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bloodType">Blood Type:</label>
                  <select id="bloodType" name="bloodType" class="form-control w-auto" required>
                    <option value="">Select Blood Type</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="rhesusFactor">Rhesus Factor:</label>
                  <select id="rhesusFactor" name="rhesusFactor" class="form-control w-auto" required>
                    <option value="">Select Rhesus Factor</option>
                    <option value="positive">+</option>
                    <option value="negative">-</option>
                  </select>
                </div>
              </div>
            </div>
            <!-- Email and Last Donation Date Row -->
            <div class="row mb-2">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email">Email:</label>
                  <input type="email" id="email" name="email" class="form-control w-auto" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="lastDonationDate">Last Donation Date:</label>
                  <input type="date" id="lastDonationDate" name="lastDonationDate" class="form-control w-auto" required>
                </div>
              </div>
            </div>
            <!-- Additional Information Row -->
            <div class="row mb-2">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="additionalInfo">Additional Information:</label>
                  <textarea id="additionalInfo" name="additionalInfo" class="form-control w-auto" rows="3" required></textarea>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-auto">Add Donor</button>
          </form>
        </div>
      </div>
    </div>
  

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Health-Connect</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      Designed by <strong>Taylor</strong>
    </div>
  </footer><!-- End Footer -->
  <?php
include('server.php');

function isUnique($conn, $email, $username) {
    // Check if username is unique in the user table
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return "Username is already taken.";
    }
    $stmt->close();

    // Check if email is unique in the donor table
    $stmt = $conn->prepare("SELECT * FROM donor WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return "Email is already registered.";
    }
    $stmt->close();

    return "OK";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);
    $usertype = htmlspecialchars($_POST["usertype"]);
    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname = htmlspecialchars($_POST["lastname"]);
    $sex = htmlspecialchars($_POST["sex"]);
    $bloodType = htmlspecialchars($_POST["bloodType"]);
    $rhFactor = htmlspecialchars($_POST["rhesusFactor"]);
    $email = htmlspecialchars($_POST["email"]);
    $lastDonationDate = htmlspecialchars($_POST["lastDonationDate"]);
    $additionalInfo = htmlspecialchars($_POST["additionalInfo"]);

    // Validate the last donation date to ensure it is not in the future
    if (strtotime($lastDonationDate) > time()) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Last donation date cannot be in the future.',
                icon: 'error'
            }).then(function() {
                window.history.back();
            });
        </script>";
        exit();
    }

    // Hash the password using MD5
    $hashedPassword = md5($password);

    // Check for unique email and username
    $uniqueCheck = isUnique($conn, $email, $username);
    if ($uniqueCheck !== "OK") {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: '$uniqueCheck',
                icon: 'error'
            }).then(function() {
                window.history.back();
            });
        </script>";
        exit();
    }

    // Insert user into the user table
    $stmt = $conn->prepare("INSERT INTO user (username, password, user_type) VALUES (?,?,?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $usertype);
    $stmt->execute();

    // Get the last inserted ID
    $lastUserID = $conn->insert_id;

    // Prepare and execute the insert into donor table
    $stmt = $conn->prepare("INSERT INTO donor (User_ID, Blood_Type, Rh_Factor, Last_Donation_Date, Additional_Information, firstname, lastname, sex, email) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("issssssss", $lastUserID, $bloodType, $rhFactor, $lastDonationDate, $additionalInfo, $firstname, $lastname, $sex, $email);
    $stmt->execute();

    // Display success message
    echo "<script>
        Swal.fire({
            title: 'Success!',
            text: 'New donor added successfully',
            icon: 'success'
        }).then(function() {
            window.location.href = 'hospital_dashboard.php';
        });
    </script>";

    $stmt->close();
    $conn->close();
}
?>


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
