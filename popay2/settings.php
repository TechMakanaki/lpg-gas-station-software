<?php
require("con.php");
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$username = $_SESSION['username'];
$query = mysqli_query($con, "SELECT * FROM admin WHERE Username='$username'");
if (mysqli_num_rows($query) == 0) {
    header("location:login.php");
    exit();
}

$userDetails = mysqli_fetch_assoc($query);
if ($userDetails['Role'] != "Admin") {
    header("location:sales-record.php");
    exit();
}

$showToast = false; // Variable to control the display of the toast notification
$toastMessage = "";

// Fetch configuration data
$query = mysqli_query($con, "SELECT * FROM configuration WHERE ID='1'");
if (!$query) {
    die("Error fetching configuration: " . mysqli_error($con));
}

$set = mysqli_fetch_assoc($query);

// If no data is found, initialize $set with default values
if (!$set) {
    $set = [
        'companyName' => '',
        'Phone' => '',
        'Address' => '',
        'bulkPrice' => 0,
        'retailPrice' => 0,
        'taxRate' => 0,
        'logoURL' => '' // Default empty logo URL
    ];
}

if (isset($_POST['set'])) {
    $companyName = $_POST['companyName'];
    $Phone = $_POST['Phone'];
    $Address = $_POST['Address'];
    $bulkPrice = $_POST['bulkPrice'];
    $retailPrice = $_POST['retailPrice'];
    $taxRate = $_POST['taxRate'];

    // Handle file upload for the logo
    $logoURL = $set['logoURL']; // Default to existing logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/"; // Directory to save uploaded files
        $logoFileName = basename($_FILES['logo']['name']);
        $logoFilePath = $uploadDir . $logoFileName;

        // Ensure the upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move uploaded file to the server
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoFilePath)) {
            $logoURL = $logoFilePath; // Save the file path to the database
        }
    }

    $query = mysqli_query($con, "UPDATE configuration 
        SET companyName='$companyName', Phone='$Phone', Address='$Address', 
        bulkPrice='$bulkPrice', retailPrice='$retailPrice', taxRate='$taxRate', 
        logoURL='$logoURL' WHERE ID='1'");

    if ($query) {
        $showToast = true; // Set to true if the update was successful
		 $toastMessage = "Configuration saved successfully!";
    } else {
        die("Error updating configuration: " . mysqli_error($con));
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Gas Station POS - Settings</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
            width: 450px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php sidebar1($set['companyName'], $userDetails['Role'],$set); ?>
        <div class="main-panel">
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="#">Settings</a>
                    </div>
                    <!-- User Info Section -->
                <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
                <li class="nav-item">
                <a class="nav-link" href="javascript:;">
    <i class="nc-icon nc-single-02"></i> <!-- Changed to nc-icon for admin -->
    <span class="d-none d-lg-inline">
        Admin <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?>
    </span>
</a>
                </li>
            </ul>
        </div>
                </div>
            </nav>

            <div class="content">
                <div class="col-md-12">
                    <div class="row card">
                        <div class="card-header">
                            <h5 class="card-title">System Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form id="configForm" action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="businessName">Business Name:</label>
                                    <input type="text" class="form-control" id="businessName" name="companyName" value="<?php echo $set['companyName']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Address:</label>
                                    <textarea class="form-control" id="address" rows="3" name="Address" required><?php echo $set['Address']; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Numbers:</label>
                                    <input type="text" class="form-control" id="phone" name="Phone" value="<?php echo $set['Phone']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="bulkPrice">Bulk Price (₦/kg):</label>
                                    <input type="number" class="form-control" id="bulkPrice" name="bulkPrice" value="<?php echo $set['bulkPrice']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="retailPrice">Retail Price (₦/kg):</label>
                                    <input type="number" class="form-control" id="retailPrice" name="retailPrice" value="<?php echo $set['retailPrice']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="taxRate">Tax Rate (%):</label>
                                    <input type="number" class="form-control" id="taxRate" name="taxRate" value="<?php echo $set['taxRate']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="logo">Small Logo for Receipt:</label>
                                    <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*" required>
                                    <?php if (!empty($set['logoURL'])): ?>
                                        <p>Current Logo: <img src="<?php echo $set['logoURL']; ?>" alt="Logo" style="max-width: 100px;"></p>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" name="set" class="btn btn-primary">Save Configuration</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
			<div class="toast-container">
                <div class="toast <?php echo $showToast ? 'show' : 'hide'; ?>" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <strong class="me-auto">Notification</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <?php echo $toastMessage; ?>
                    </div>
                </div>
            </div>
            <footer class="footer footer-black footer-white text-center">
                <div class="container-fluid">
                    <span class="copyright">
                        © <script>document.write(new Date().getFullYear())</script> <?php echo $set['companyName']; ?>
                    </span>
                </div>
            </footer>
        </div>
    </div>

    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
</body>
</html>
