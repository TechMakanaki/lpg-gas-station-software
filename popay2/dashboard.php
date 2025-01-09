<?php
require("con.php");
if (!isset($_SESSION['username'])) {
  header("location:login.php");
}
$username = $_SESSION['username'];
$query = mysqli_query($con,"SELECT*FROM admin WHERE Username='$username'");
    if(mysqli_num_rows($query)==0){
      header("location:login.php");
      exit();
    }
$userDetails =  mysqli_fetch_assoc($query);
if ($userDetails['Role']!="Admin") {
    header("location:sales-record.php");
    exit();
}

$query = mysqli_query($con,"SELECT*FROM configuration WHERE ID='1'");
$set = mysqli_fetch_assoc($query);

$date = date("Y-m-d");

$query = mysqli_query($con,"SELECT*FROM orders WHERE Date_='$date'");
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

$query = mysqli_query($con,"SELECT DISTINCT Customer FROM orders WHERE Date_='$date'");
$cust = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="<?php echo $set['logoURL']?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    Gas Station POS Dashboard
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <link href="assets/demo/demo.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="">
  <div class="wrapper">
    <?php sidebar1($set['companyName'],$userDetails['Role'],$set)?>
    <div class="main-panel">
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
                <a class="navbar-brand" href="javascript:;"><?php echo $set['companyName']; ?></a>
            </div>
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
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
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
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-calendar-o"></i> Today's Sales
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
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
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-clock-o"></i> Total Weight
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
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
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-refresh"></i> Customers
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sales Rep Performance Table -->
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title">Sales Rep Performance</h5>
                <p class="card-category">Today</p>
              </div>
              <div class="card-body">
                <div class="table-responsive mt-4">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Sales rep Name</th>
                        <th>Total Filled (kg)</th>
                        <th>Total Sales Amount</th>
                      </tr>
                    </thead>
                    <tbody id="salesRecords">
                      <?php
                      $query = mysqli_query($con,"SELECT * FROM admin WHERE Role='Sales'");
                      while($row = mysqli_fetch_assoc($query)){
                      ?>
                        <tr>
                          <td><?php echo $date ?></td>
                          <td><?php echo $row['Fullname'] ?></td>
                          <td><?php echo reps_kg($row['Username']) ?></td>
                          <td>₦<?php echo reps_price($row['Username']) ?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer">
                <hr>
                <div class="stats">
                  <i class="fa fa-history"></i> Updated 5 minutes ago
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sales Rep Performance Chart -->
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title">Sales Rep Performance (Chart)</h5>
              </div>
              <div class="card-body">
                <canvas id="salesRepChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Daily Sales Chart -->
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title">Daily Sales (Chart)</h5>
              </div>
              <div class="card-body">
                <canvas id="dailySalesChart"></canvas>
              </div>
            </div>
          </div>
        </div>

      </div>

      <footer class="footer footer-black  footer-white ">
        <div class="container-fluid">
          <div class="row">
            <nav class="footer-nav">
              <ul>
                <li><a href="#" target="_blank"><?php echo $set['companyName']; ?></a></li>
              </ul>
            </nav>
          </div>
        </div>
      </footer>
    </div>
  </div>

  
  <script src="assets/js/plugins/chartjs.min.js"></script>
  <script>
    const ctx1 = document.getElementById('salesRepChart').getContext('2d');
    const salesRepChart = new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: [<?php
            $query = mysqli_query($con, "SELECT Fullname FROM admin WHERE Role='Sales'");
            while ($row = mysqli_fetch_assoc($query)) {
                echo "'" . $row['Fullname'] . "', ";
            }
            ?>],
        datasets: [{
          label: 'Total Sales (₦)',
          data: [<?php
            $query = mysqli_query($con, "SELECT Username FROM admin WHERE Role='Sales'");
            while ($row = mysqli_fetch_assoc($query)) {
                echo reps_price($row['Username']) . ", ";
            }
            ?>],
          backgroundColor: 'rgba(75, 192, 192, 0.6)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    const ctx2 = document.getElementById('dailySalesChart').getContext('2d');
const dailySalesChart = new Chart(ctx2, {
  type: 'line',
  data: {
    labels: [<?php
        // Query to fetch distinct dates and sort them in ascending order
        $query = mysqli_query($con, "SELECT DISTINCT Date_ FROM orders ORDER BY Date_ ASC");
        $labels = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $labels[] = "'" . $row['Date_'] . "'";
        }
        echo implode(",", $labels); // Outputs dates like '2024-12-25','2024-12-26', ...
    ?>],
    datasets: [{
      label: 'Sales Amount (₦)',
      data: [<?php
        // Query to fetch total sales grouped by Date_
        $query = mysqli_query($con, "SELECT Date_, SUM(PriceKg * Weight2) AS total_sales FROM orders GROUP BY Date_ ORDER BY Date_ ASC");
        $salesData = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $salesData[] = $row['total_sales'];
        }
        echo implode(",", $salesData); // Outputs totals like 1500, 3000, ...
      ?>],
      fill: false,
      borderColor: 'rgba(153, 102, 255, 1)',
      tension: 0.1
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

  </script>
</body>

</html>
