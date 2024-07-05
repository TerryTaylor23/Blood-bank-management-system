<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4"></h1>

    <!-- Form for uploading CSV file -->
    <!--<div class="card">
        <div class="card-header">Add Regions</div>
        <div class="card-body">
         <form action="upload_regions.php" method="post" enctype="multipart/form-data">
            <input type="file" name="Tz-data">
            <input type="submit" value="Upload CSV">
         </form>


        </div>
    </div>-->

    <!-- Form for registering hospitals -->
    <div class="card mt-4">
        <div class="card-header">Register Hospital</div>
        <div class="card-body">
            <form action="register_hospital.php" method="post">
                <label for="hospitalName">Hospital Name:</label>
                <input type="text" name="hospitalName" required>
            <br><br>
                <label for="hospitalName">WARD:</label>
                <input type="text" name="WARDName" required>
                <br><br>
                <button type="submit" class="btn btn-primary">Register Hospital</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
