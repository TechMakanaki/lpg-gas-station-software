<?php
require("con.php");
$output = array();

if (!isset($_SESSION['username'])) {
  echo "login";
  exit();
}
$username = $_SESSION['username'];
$query = mysqli_query($con, "SELECT * FROM admin WHERE Username='$username'");
if (mysqli_num_rows($query) == 0) {
    echo "login";
    exit();
}

$query = mysqli_query($con, "SELECT * FROM configuration WHERE ID='1'");
$set = mysqli_fetch_assoc($query);

if (isset($_POST['order']) && isset($_POST['customer']) && isset($_POST['total2']) && isset($_POST['selectedMethod'])) {
    $customer = $_POST['customer'];
    $selectedMethod = $_POST['selectedMethod'];
    $date = date("Y-m-d");
    $order = explode(",", $_POST['order']);
    $empty = $order[0];
    $fill = $order[1];
    $totalWeight = $empty + $fill; // Calculate TotalWeight
    $price = (float)$order[3];
    $price_per_kg = $order[4];
    // $bulkPrice = (float)$set['bulkPrice'];
    // $retailPrice = (float)$set['retailPrice'];

// Use a small tolerance for floating-point comparison
if ($price_per_kg == $set['bulkPrice']) { 
    $cat = "bulk";
    $priceKg = $price_per_kg; // Price for bulk
} else {
    $cat = "retail";
    $priceKg = $price_per_kg; // Price for retail
}

    // Insert into the database, including TotalWeight and PriceKg
    $query = mysqli_query($con, "INSERT INTO orders (Username, Customer, Weight1, Weight2, TotalWeight, Category, Price, PriceKg, Payment, Date_)
                                 VALUES ('$username', '$customer', '$empty', '$fill', '$totalWeight', '$cat', '$price', '$priceKg', '$selectedMethod', '$date')");

    echo "success";
    exit();
}
?>
