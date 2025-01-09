<?php
require("con.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$username = mysqli_real_escape_string($con, $_SESSION['username']);
$query = mysqli_query($con, "SELECT * FROM admin WHERE Username='$username'");

if (!$query) {
    die("Database query failed: " . mysqli_error($con));
}

if (mysqli_num_rows($query) == 0) {
    header("location:login.php");
    exit();
}

$userDetails = mysqli_fetch_assoc($query);
if ($userDetails['Role'] != "Admin") {
    header("location:dashboard.php");
    exit();
}

$query = mysqli_query($con, "SELECT * FROM configuration WHERE ID='1'");
if (!$query) {
    die("Database query failed: " . mysqli_error($con));
}
$set = mysqli_fetch_assoc($query);

$date = date("Y-m-d");

// Total Sales, Gas Sold, and Customers
$query = mysqli_query($con, "SELECT * FROM orders WHERE Date_='$date'");
if (!$query) {
    die("Database query failed: " . mysqli_error($con));
}

$sales = 0;
$liters = 0;

while ($sum = mysqli_fetch_assoc($query)) {
    $cal = $sum['Price'];
    $sales += $cal;
    $liters += $sum['Weight2'];
}

$query = mysqli_query($con, "SELECT DISTINCT Customer FROM orders WHERE Date_='$date'");
if (!$query) {
    die("Database query failed: " . mysqli_error($con));
}
$cust = mysqli_num_rows($query);

if (isset($_POST['delete'])) {
    $deleteId = intval($_POST['id']); // Ensure the ID is an integer
    // Delete the record from the database
    if (mysqli_query($con, "DELETE FROM orders WHERE ID='$deleteId'")) {
        // If deletion is successful, return success response
        echo json_encode(['status' => 'success', 'message' => 'Transaction deleted successfully!']);
        exit(); // Ensure the script stops executing after sending the response
    } else {
        // If deletion fails, return error response
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete transaction!']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Gas Station POS Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <?php sidebar1($set['companyName'], $userDetails['Role'],$set); ?>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <a class="navbar-brand" href="#">Transactions</a>
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
            <!-- End Navbar -->
            <div class="content">
                <!-- Transactions Table -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">All Transactions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="transactionsTable">
                                <thead>
    <tr>
        <th>Date</th>
        <th>Customer Name</th>
        <th>Gas Type</th>
        <th>Empty Cylinder (Kg)</th>
        <th>Amount Filled (Kg)</th>
        <th>Total Weight (Kg)</th>
        <th>Price (₦/Kg)</th>
        <th>Total (₦)</th>
        <th>Payment</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
<?php
    $query = mysqli_query($con, "SELECT * FROM orders");
    if (!$query) {
        die("Database query failed: " . mysqli_error($con));
    }
    while ($row = mysqli_fetch_assoc($query)) {
        $totalWeight = $row['Weight1'] + $row['Weight2']; // Calculate Total Weight
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Date_']); ?></td>
            <td><?php echo htmlspecialchars($row['Customer']); ?></td>
            <td><?php echo htmlspecialchars($row['Category']); ?></td>
            <td><?php echo htmlspecialchars($row['Weight1']); ?></td>
            <td><?php echo htmlspecialchars($row['Weight2']); ?></td>
            <td><?php echo htmlspecialchars($totalWeight); ?></td> <!-- Display Total Weight -->
            <td><?php echo htmlspecialchars($row['PriceKg']); ?></td>
            <td><?php echo htmlspecialchars($row['Price'] ); ?></td>
            <td><?php echo htmlspecialchars($row['Payment']); ?></td>
            <td>
                <button class="btn btn-info btn-sm" onclick="viewInfo('<?php echo htmlspecialchars($row['Customer']); ?>', '<?php echo htmlspecialchars($row['Category']); ?>', '<?php echo htmlspecialchars($row['Weight1']); ?>', '<?php echo htmlspecialchars($row['Weight2']); ?>', '<?php echo htmlspecialchars($row['Price'] * $row['Weight2']); ?>', '<?php echo htmlspecialchars($totalWeight); ?>')">Info</button>
                <button class="btn btn-danger btn-sm" onclick="deleteRecord(<?php echo intval($row['ID']); ?>)">Delete</button>
            </td>
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
                    <span>&copy; <script>document.write(new Date().getFullYear())</script><?php echo $set['companyName']; ?></span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Customer Name:</strong> <span id="modalCustomer" style="float: right;"></span></p>
                    <p><strong>Gas Type:</strong> <span id="modalGasType" style="float: right;"></span></p>
                    <p><strong>Empty Cylinder (Kg):</strong> <span id="modalEmptyKg" style="float: right;"></span></p>
                    <p><strong>Amount Filled (Kg):</strong> <span id="modalFilledKg" style="float: right;"></span></p>
                    <p><strong>Total Weight (Kg):</strong> <span id="modalTotalWeight" style="float: right;"></span></p>
                    <p><strong>Total (₦):</strong> <span id="modalTotal" style="float: right;"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
            $(document).ready(function () {
                $('#transactionsTable').DataTable();

                window.viewInfo = function (customer, gasType, emptyKg, filledKg, total) {
                    $('#modalCustomer').text(customer);
                    $('#modalGasType').text(gasType);
                    $('#modalEmptyKg').text(emptyKg);
                    $('#modalFilledKg').text(filledKg);
                    $('#modalTotalWeight').text(totalWeight);
                    $('#modalTotal').text(total);
                    $('#infoModal').modal('show');
                };

                window.deleteRecord = function (id) {
                    if (confirm('Are you sure you want to delete this transaction?')) {
                        $.post('', { delete: true, id: id }, function (response) {
                            const res = JSON.parse(response);
                            alert(res.message);
                            if (res.status === 'success') {
                                location.reload(); // Reload the page to reflect changes
                            }
                        });
                    }
                };
            });
        </script>
</body>
</html>
