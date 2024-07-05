<?php
include('server.php'); // Include your server configuration file

// Function to insert or retrieve ID of a region
function insertOrGetRegionID($conn, $region_name) {
    // Check if the region already exists
    $stmt = $conn->prepare("SELECT Region_ID FROM region WHERE Region_Name = ?");
    $stmt->bind_param("s", $region_name);
    $stmt->execute();
    $stmt->bind_result($region_id);
    $stmt->fetch();
    $stmt->close();

    // If the region does not exist, insert it
    if (!$region_id) {
        $stmt = $conn->prepare("INSERT INTO region (Region_Name) VALUES (?)");
        $stmt->bind_param("s", $region_name);
        $stmt->execute();
        $region_id = $conn->insert_id;
        $stmt->close();
    }

    return $region_id;
}

// Function to insert or retrieve ID of a district
function insertOrGetDistrictID($conn, $district_name, $region_id) {
    // Check if the district already exists
    $stmt = $conn->prepare("SELECT District_ID FROM district WHERE District_Name = ? AND Region_ID = ?");
    $stmt->bind_param("si", $district_name, $region_id);
    $stmt->execute();
    $stmt->bind_result($district_id);
    $stmt->fetch();
    $stmt->close();

    // If the district does not exist, insert it
    if (!$district_id) {
        $stmt = $conn->prepare("INSERT INTO district (District_Name, Region_ID) VALUES (?, ?)");
        $stmt->bind_param("si", $district_name, $region_id);
        $stmt->execute();
        $district_id = $conn->insert_id;
        $stmt->close();
    }

    return $district_id;
}

// Function to insert or retrieve ID of a ward
function insertOrGetWardID($conn, $ward_name, $district_id) {
    // Check if the ward already exists
    $stmt = $conn->prepare("SELECT Ward_ID FROM ward WHERE Ward_Name = ? AND District_ID = ?");
    $stmt->bind_param("si", $ward_name, $district_id);
    $stmt->execute();
    $stmt->bind_result($ward_id);
    $stmt->fetch();
    $stmt->close();

    // If the ward does not exist, insert it
    if (!$ward_id) {
        $stmt = $conn->prepare("INSERT INTO ward (Ward_Name, District_ID) VALUES (?, ?)");
        $stmt->bind_param("si", $ward_name, $district_id);
        $stmt->execute();
        $ward_id = $conn->insert_id;
        $stmt->close();
    }

    return $ward_id;
}

// Define the target directory for the uploaded file
$target_dir = "upload/";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the name of the uploaded file
    $filename = $_FILES["Tz-data"]["name"];
    
    // Construct the full path to the uploaded file
    $target_file = $target_dir . basename($filename);
    
    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["Tz-data"]["tmp_name"], $target_file)) {
        // Open the uploaded file
        $file = fopen($target_file, 'r');

        // Skip the first row (header)
        fgetcsv($file);

        // Loop through each line in the CSV file
        while (($line = fgetcsv($file)) !== false) {
            // Extract data from the current line
            $region_id = insertOrGetRegionID($conn, trim($line[1])); // Insert or retrieve region ID
            $district_id = insertOrGetDistrictID($conn, trim($line[3]), $region_id); // Insert or retrieve district ID
            $ward_id = insertOrGetWardID($conn, trim($line[5]), $district_id); // Insert or retrieve ward ID
        }
        
        // Close the file
        fclose($file);
        
        echo "Data imported successfully.";
    } else {
        echo "Failed to upload the file.";
    }
} else {
    echo "Invalid request method.";
}
?>
