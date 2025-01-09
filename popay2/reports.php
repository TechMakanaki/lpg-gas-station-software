<?php
require("con.php");

// Redirect if user is not logged in
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

// Fetch configuration settings
$query = mysqli_query($con, "SELECT * FROM configuration WHERE ID='1'");
$set = mysqli_fetch_assoc($query);

// Initialize filter variables
$filterStartDate = $_POST['startDate'] ?? '';
$filterEndDate = $_POST['endDate'] ?? '';
$filterSortOption = $_POST['sortOption'] ?? '';

// Build SQL query for orders
$sql = "SELECT * FROM orders WHERE 1";

if (!empty($filterStartDate)) {
    $sql .= " AND Date_ >= '$filterStartDate'";
}
if (!empty($filterEndDate)) {
    $sql .= " AND Date_ <= '$filterEndDate'";
}
if (!empty($filterSortOption)) {
    $sql .= $filterSortOption == 'bulk' ? " ORDER BY Price ASC" : " ORDER BY (Price * Weight2) ASC";
}

$query = mysqli_query($con, $sql);

// Fetch daily, weekly, and monthly sales data
$dailySalesData = mysqli_fetch_all(mysqli_query($con, "SELECT DATE(Date_) AS day, SUM(PriceKg * Weight2) AS total_sales FROM orders GROUP BY DATE(Date_) ORDER BY day DESC LIMIT 7"), MYSQLI_ASSOC);
$weeklySalesData = mysqli_fetch_all(mysqli_query($con, "SELECT WEEK(Date_) AS week, SUM(PriceKg * Weight2) AS total_sales FROM orders GROUP BY WEEK(Date_) ORDER BY week DESC LIMIT 4"), MYSQLI_ASSOC);
$monthlySalesData = mysqli_fetch_all(mysqli_query($con, "SELECT MONTH(Date_) AS month, YEAR(Date_) AS year, SUM(PriceKg * Weight2) AS total_sales FROM orders GROUP BY YEAR(Date_), MONTH(Date_) ORDER BY year DESC, month DESC LIMIT 12"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="<?php echo $set['logoURL']?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Gas Station POS - Sales Report</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <link href="assets/demo/demo.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php sidebar1($set['companyName'], $userDetails['Role'],$set) ?>
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
                        <a class="navbar-brand" href="#">Report</a>
                    </div>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:;">
                                    <i class="nc-icon nc-single-02"></i>
                                    <span class="d-none d-lg-inline">Admin <?php echo htmlspecialchars($username); ?></span>
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
                            <h5 class="card-title">Sales Reports</h5>
                            <p class="card-category">Last 24 Hours</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="startDate">Start Date:</label>
                                            <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo htmlspecialchars($filterStartDate); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="endDate">End Date:</label>
                                            <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo htmlspecialchars($filterEndDate); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sortOption">Sort By:</label>
                                            <select class="form-control" id="sortOption" name="sortOption">
                                                <option value="bulk" <?php echo ($filterSortOption == 'bulk') ? 'selected' : ''; ?>>Bulk Price</option>
                                                <option value="retail" <?php echo ($filterSortOption == 'retail') ? 'selected' : ''; ?>>Retail Price</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                                <button type="button" class="btn btn-success" id="downloadCsv">Download CSV</button>
                            </form>
                            <div class="table-responsive mt-4">
                            <table class="table table-bordered" id="reportTable">
    <thead>
        <tr>
            <th>Date</th>
            <th>Customer Name</th>
            <th>Gas Type</th>
            <th>Empty Cylinder (kg)</th>
            <th>Quantity Filled (kg)</th>
            <th>Total Weight (kg)</th>
            <th>Price per (₦)/kg</th>
            <th>Total Amount (₦)</th>
            <th>Payment Method</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($query)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['Date_']); ?></td>
                <td><?php echo htmlspecialchars($row['Customer']); ?></td>
                <td><?php echo htmlspecialchars($row['Category']); ?></td>
                <td><?php echo htmlspecialchars($row['Weight1']); ?></td>
                <td><?php echo htmlspecialchars($row['Weight2']); ?></td>
                <td><?php echo htmlspecialchars($row['TotalWeight']); ?></td>
                <td><?php echo htmlspecialchars($row['PriceKg']); ?></td>
                <td><?php echo htmlspecialchars($row['Price']); ?></td> 
                <td><?php echo htmlspecialchars($row['Payment']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

                            </div>
                            <hr />
                            <div class="card-stats">
                                <i class="fa fa-check" id="totalSales"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5>Sales Analysis</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-lg mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Daily Sales Analysis</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailySalesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class ="card shadow-lg mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Weekly Sales Analysis</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="weeklySalesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-lg mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Monthly Sales Analysis</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlySalesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer footer-black footer-white text-center">
                    <div class="container-fluid">
                        <div class="credits">
                            <span class="copyright">
                                © <script>document.write(new Date().getFullYear())</script> <i class="fa fa-heart heart"></i> <?php echo htmlspecialchars($set['companyName']); ?>
                            </span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Core JS Files -->
        <script src="assets/js/core/jquery.min.js"></script>
        <script src="assets/js/core/bootstrap.bundle.min.js"></script>
        <script src="assets/js/plugins/chartjs.min.js"></script>
        <script src="assets/js/plugins/bootstrap-notify.js"></script>
        <script src="assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>

        <script>
            document.getElementById("downloadCsv").addEventListener("click", function () {
                const table = document.getElementById("reportTable");
                const rows = table.querySelectorAll("tr");
                let csvContent = "";

                rows.forEach(row => {
                    const cells = row.querySelectorAll("th, td");
                    const rowData = Array.from(cells).map(cell => `"${cell.innerText}"`);
                    csvContent += rowData.join(",") + "\n";
                });

                const blob = new Blob([csvContent], { type: "text/csv" });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.setAttribute("hidden", "");
                a.setAttribute("href", url);
                a.setAttribute("download", "sales_report.csv");
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });

            function calculateTotals() {
                const table = document.getElementById("reportTable");
                const rows = table.querySelectorAll("tbody tr");
                
                let totalQuantityFilled = 0;
                let totalEmptyCylinder = 0;
                let totalAmount = 0;

                rows.forEach(row => {
                    const cells = row.querySelectorAll("td");
                    if (cells.length > 0) {
                        totalQuantityFilled += parseFloat(cells[3].innerText) || 0;
                        totalEmptyCylinder += parseFloat(cells[4].innerText) || 0;
                        totalAmount += parseFloat(cells[7].innerText) || 0;
                    }
                });

                document.getElementById("totalSales").innerText = `Total Sales: ₦${totalAmount.toLocaleString()}`;
                const totalsRow = `
                    <tr style="font-weight: bold;">
                        <td colspan="3">Totals</td>
                        <td>${totalQuantityFilled.toLocaleString()}</td>
                        <td>${totalEmptyCylinder.toLocaleString()}</td>
                        <td></td>
                        <td></td>
                        <td>${totalAmount.toLocaleString()}</td>
                        <td></td>
                    </tr>
                `;

                const tbody = table.querySelector("tbody");
                tbody.insertAdjacentHTML("beforeend", totalsRow);
            }

            document.addEventListener("DOMContentLoaded", function () {
                calculateTotals();

                const dailyLabels = <?php echo json_encode(array_column($dailySalesData, 'day')); ?>;
                const dailySales = <?php echo json_encode(array_column($dailySalesData, 'total_sales')); ?>;

                const weeklyLabels = <?php echo json_encode(array_column($weeklySalesData, 'week')); ?>;
                const weeklySales = <?php echo json_encode(array_column($weeklySalesData, 'total_sales')); ?>;

                const monthlyLabels = <?php echo json_encode(array_map(function($row) { return $row['month'] . '-' . $row['year']; }, $monthlySalesData)); ?>;
                const monthlySales = <?php echo json_encode(array_column($monthlySalesData, 'total_sales')); ?>;

                const ctx1 = document.getElementById('dailySalesChart').getContext('2d');
                const dailySalesChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: dailyLabels,
                        datasets: [{
                            label: 'Daily Sales (₦)',
                            data: dailySales,
                            fill: false,
                            borderColor: 'rgba(75, 192, 192, 1)',
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

                const ctx2 = document.getElementById('weeklySalesChart').getContext('2d');
                const weeklySalesChart = new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: weeklyLabels,
                        datasets: [{
                            label: 'Weekly Sales (₦)',
                            data: weeklySales,
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

                const ctx3 = document.getElementById('monthlySalesChart').getContext('2d');
                const monthlySalesChart = new Chart(ctx3, {
                    type: 'bar',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Monthly Sales (₦)',
                            data: monthlySales,
                            backgroundColor: 'rgba(255, 159, 64, 0.2)',
                            borderColor: 'rgba(255, 159, 64, 1)',
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
            });
        </script>
    </div>
</body>
</html>