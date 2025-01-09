<?php
session_start();
// Database connection
$con = mysqli_connect("localhost", "root", "", "popay");
$date = date("Y-m-d");

function sidebar1($company, $role,$set) {

    // Get the current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="sidebar" data-color="white" data-active-color="danger">
        <div class="logo">
            <a href="#" class="simple-text logo-mini">
                <div class="logo-image-small">
                    <img src="<?php echo $set['logoURL']?>">
                </div>
            </a>
            <a href="#" class="simple-text logo-normal">
                <?php echo $company; ?>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <ul class="nav">
                <?php
                if ($role == "Admin") {
                    ?>
                    <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                        <a href="dashboard.php">
                            <i class="nc-icon nc-bank"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales-transaction.php') ? 'active' : ''; ?>">
                        <a href="sales-transaction.php">
                            <i class="nc-icon nc-cart-simple"></i>
                            <p>Sales Transactions</p>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                        <a href="reports.php">
                            <i class="nc-icon nc-chart-bar-32"></i>
                            <p>Sales Report</p>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'add-profile.php') ? 'active' : ''; ?>">
                        <a href="add-profile.php">
                            <i class="nc-icon nc-single-02"></i>
                            <p>Add Staffs</p>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                        <a href="settings.php">
                            <i class="nc-icon nc-settings-gear-65"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                    <?php
                } else {
                    ?>
                    <li class="<?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>">
                        <a href="pos.php">
                            <i class="nc-icon nc-cart-simple"></i>
                            <p>Sell/Record Gas</p>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales-record.php') ? 'active' : ''; ?>">
                        <a href="sales-record.php">
                            <i class="nc-icon nc-paper"></i>
                            <p>Sales Record</p>
                        </a>
                    </li>
                    <?php
                }
                ?>
                <li class="<?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>">
                    <a href="logout.php">
                        <i class="nc-icon nc-button-power"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <?php
}

function reps_price($username) {
    global $con, $date;
    $query = mysqli_query($con, "SELECT * FROM orders WHERE Username='$username' AND Date_='$date'");
    $sum = 0;
    while ($row = mysqli_fetch_assoc($query)) {
        $cal = ($row['Weight2'] * $row['PriceKg']);
        $sum = ($sum + $cal);
    }
    return $sum;
}

function reps_kg($username) {
    global $con, $date;
    $query = mysqli_query($con, "SELECT * FROM orders WHERE Username='$username' AND Date_='$date'");
    $sum = 0;
    while ($row = mysqli_fetch_assoc($query)) {
        $sum = $sum + $row['Weight2'];
    }
    return $sum;
}
?>