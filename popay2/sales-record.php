<?php
require("con.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
}

$username = $_SESSION['username'];
$query = mysqli_query($con, "SELECT*FROM admin WHERE Username='$username'");

if (mysqli_num_rows($query) == 0) {
    header("location:login.php");
}

$userDetails = mysqli_fetch_assoc($query);
if ($userDetails['Role'] != "Sales") {
    header("location:dashboard.php");
    exit();
}

$query = mysqli_query($con, "SELECT*FROM configuration WHERE ID='1'");
$set = mysqli_fetch_assoc($query);

$date = date("Y-m-d");

$query = mysqli_query($con, "SELECT*FROM orders WHERE Username='$username' and Date_='$date'");
$sales = 0;
$liters = 0;

while ($sum = mysqli_fetch_assoc($query)) {
    // Determine PriceKg based on the Category (bulk or retail)
    if ($sum['Category'] == 'bulk') {
        $priceKg = $set['bulkPrice'];  // Assuming bulk price is in configuration
    } else {
        $priceKg = $set['retailPrice'];  // Assuming retail price is in configuration
    }

    // Calculate the total sales and total liters
    $cal = $priceKg * $sum['Weight2'];
    $sales += $cal;
    $liters += $sum['Weight2'];
}

$query = mysqli_query($con, "SELECT DISTINCT Customer FROM orders WHERE Username='$username' and Date_='$date'");
$cust = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>Gas Station POS Dashboard</title>
  <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <link href="assets/demo/demo.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }
  </style>
</head>

<body>
  <div class="wrapper">
  <?php sidebar1($set['companyName'],$userDetails['Role'],$set)?>
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-toggle">
              <button type="button" class="navbar-toggler">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </button>
            </div>
            <a class="navbar-brand" href="#"><?php echo $set['companyName'] ?></a>
          </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
          </button>
          <!-- User Info Section -->
          <div class="collapse navbar-collapse justify-content-end" id="navigation">
            <ul class="navbar-nav">
                <li class="nav-item">
                <a class="nav-link" href="javascript:;">
    <i class="nc-icon nc-single-02"></i> <!-- Changed to nc-icon for admin -->
    <span class="d-none d-lg-inline">
         <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?>
    </span>
</a>
                </li>
            </ul>
        </div>
        </div>
      </nav>
      <!-- End Navbar -->
      <!-- Main Content -->
      <div class="content">
      <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-money-coins text-success"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Total Sales</p>
                      <p class="card-title">₦<?php echo $sales ?><p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-calendar-o"></i>
                  Today's Sales
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-tap-01 text-warning"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Gas Sold</p>
                      <p class="card-title"><?php echo $liters ?> KG<p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-clock-o"></i>
                  Total Weight
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body ">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-single-02 text-danger"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Customers</p>
                      <p class="card-title"><?php echo $cust ?><p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer ">
                <hr>
                <div class="stats">
                  <i class="fa fa-refresh"></i>
                  Customers
                </div>
              </div>
            </div>
          </div>
          
        </div>
        <div class="col-md-12">
            <div class="row card">
                <div class="card-header ">
                    <h5 class="card-title">Sales Performance</h5>
                    <p class="card-category">Today's Sales</p>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="example">
  <thead class="text-primary">
    <tr>
      <th>Date</th>
      <th>Customer Name</th>
      <th>Gas Type</th>
      <th>Empty Cylinder Kg</th>
      <th>Quantity Filled (kg)</th>
      <th>Total Weight (kg)</th>
      <th>Price per (₦)Kg</th>
      <th>Total Amount (₦)</th>
      <th>Payment method</th>
    </tr>
  </thead>
  <tbody id="salesRecords">
    <?php
    // Query to fetch orders for the current day
    $query = mysqli_query($con, "SELECT*FROM orders WHERE Username='$username' and Date_='$date'");
    
    // Loop through each order record
    while ($row = mysqli_fetch_assoc($query)) {
      
      // Dynamically determine PriceKg based on Category
      if ($row['Category'] == 'bulk') {
        $priceKg = $set['bulkPrice'];  // Assuming the bulk price is in the configuration
      } else {
        $priceKg = $set['retailPrice'];  // Assuming the retail price is in the configuration
      }

      // Calculate the total amount for each order
      $totalAmount = $priceKg * $row['Weight2'];
    ?>
      <tr>
        <td><?php echo $row['Date_'] ?></td>
        <td><?php echo $row['Customer'] ?></td>
        <td><?php echo $row['Category'] ?></td>
        <td><?php echo $row['Weight1'] ?></td>
        <td><?php echo $row['Weight2'] ?></td>
        <td><?php echo $row['TotalWeight'] ?></td>
        <td><?php echo $priceKg ?></td> <!-- Display dynamically calculated PriceKg -->
        <td><?php echo $totalAmount ?></td> <!-- Display total amount calculated dynamically -->
        <td><?php echo $row['Payment'] ?></td>
      </tr>
    <?php
    }
    ?>
  </tbody>
</table>

                      </div>
                    </div>
                  </div>
                </div>

        </div>
  
        <footer class="footer footer-black footer-white text-center">
          <div class="container-fluid">
            <div class="credits">
              <span class="copyright">
                © <script>document.write(new Date().getFullYear())</script> <i class="fa fa-heart heart"></i> <?php echo $set['companyName']; ?>
              </span>
            </div>
          </div>
        </footer>
      </div>
    </div>
  
    <!-- Core JS Files -->
    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script>
  // Initialize DataTable
  $(document).ready(function () {
    $('#example').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      lengthChange: true,
      language: {
        search: "Filter records:", // Customizing search input label
      },
    });
  });


      function viewInfo(customerName, gasType, emptyKg, filledKg, total) {
        alert(`Customer: ${customerName}\nGas Type: ${gasType}\nEmpty Kg: ${emptyKg}\nQuantity Filled: ${filledKg}\nTotal Amount: ${total}`);
      }
  
      function printRecord(customerName, gasType, emptyKg, filledKg, total) {
        alert(`Printing record for ${customerName}\nGas Type: ${gasType}\nEmpty Kg: ${emptyKg}\nQuantity Filled: ${filledKg}\nTotal Amount: ${total}`);
      }
  
      function deleteRecord(button) {
        if (confirm("Are you sure you want to delete this record?")) {
          const row = button.closest('tr');
          row.remove();
          alert("Record deleted successfully.");
        }
      }
    </script>
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
</body>

</html>
