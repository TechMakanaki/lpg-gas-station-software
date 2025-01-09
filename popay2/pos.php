<?php
require("con.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$username = $_SESSION['username'];

// Secure query using prepared statements
$stmt = $con->prepare("SELECT * FROM admin WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("location:login.php");
    exit();
}

$userDetails = $result->fetch_assoc();

if ($userDetails['Role'] !== "Sales") {
    header("location:dashboard.php");
    exit();
}

// Fetch configuration settings
$configQuery = $con->prepare("SELECT * FROM configuration WHERE ID = ?");
$configQuery->bind_param("i", $configId);
$configId = 1;
$configQuery->execute();
$configResult = $configQuery->get_result();
$set = $configResult->fetch_assoc();

// Close the connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Gas Station POS Dashboard</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
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

    <style>
        @media print {
            body {
                margin: 0;
                font-size: 12px; /* Adjust font size for readability */
            }

            #print {
                width: 80mm; /* Thermal paper width */
                padding: 5mm;
                margin: 0 auto;
                font-family: 'Courier New', Courier, monospace; /* Use a mono-spaced font for better readability */
            }

            #print h5, #print p {
                margin: 5px 0;
            }

            .receipt-header, .receipt-footer {
                text-align: center;
            }

            .receipt-details, .receipt-footer {
                border-top: 1px dashed #000;
                margin-top: 10px;
                padding-top: 5px;
            }

            .total {
                font-weight: bold;
                text-align: right;
            }

            /* Hide non-receipt elements during print */
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
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
                        <a class="navbar-brand" href="javascript:;"><?php echo $set['companyName'] ?></a>
                    </div>
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

            <div class="content">
                <main class="col-md-12 d-flex flex-column">
                    <header class="bg-primary text-white p-3">
                        <h2 class="fs-4 mb-0"><?php echo $set['companyName'] ?></h2>
                        <small>Logged in as: Cashier (<?php echo $userDetails['Username'] ?>)</small>
                    </header>

                    <div class="row flex-grow-1">
                        <section id="print" class="col-md-6 border-end p-3">
                            <div class="mb-3">
                                <h5>Transaction Details</h5>
                                <p>Date: <span id="transaction-date"></span></p>
                                <p>Customer Name:<span id="customer"></span></p>
                                <div class="border p-3" style="height: 300px; overflow-y: auto ;" id="transaction-details">
                                    <p id="no-transaction" class="text-muted">No items added yet.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p>Subtotal: <span class="float-end" id="subtotal">₦0.00</span></p>
                                <p>Tax: <span class="float-end" id="tax">₦0.00</span></p>
                                <p>Total: <strong class="float-end" id="total">₦0.00</strong></p>
                            </div>
                            <div>
                                <h5>Payment Method</h5>
                                <div class="d-flex gap-4 align-items-center">
                                    <label><input type="radio" name="paymentMethod" value="Cash" checked> Cash</label>
                                    <label><input type="radio" name="paymentMethod" value="Debit Card"> Debit Card</label>
                                    <label><input type="radio" name="paymentMethod" value="Bank Transfer"> Bank Transfer</label>
                                </div>
                            </div>                          
                            <div class="d-flex gap-2">
                                <button class="btn btn-success w-100" id="pay-btn">PAY</button>
                            </div>
                        </section>
                        
                        <section class="col-md-6 p-3">
                            <div class="mb-3">
                                <input type="text" id="price-input" class="form-control text-end fs-4" value="<?php echo $set['retailPrice'] ?>" readonly>
                            </div>
                            <div class="mt-4">
                                <button class="btn btn-dark w-100 mb-2" id="retail-btn">Retail Price</button>
                                <button class="btn btn-dark w-100 mb-2" id="bulk-btn">Bulk Price</button>
                            </div>
                            <div class="mt-4">
                                <label for="empty-cylinder">Empty Cylinder Weight (kg):</label>
                                <input type="number" id="empty-cylinder" class="form-control mb-2" placeholder="Enter weight" required>
                                <label for="fill-weight">Weight to Fill (kg):</label>
                                <input type="number" id="fill-weight" class="form-control mb-2" placeholder="Enter weight" required>
                                <label for="fill-name">Customer Name:</label>
                                <input type="text" id="fill-name" class="form-control mb-2" placeholder="Enter name" required>
                                <button class="btn btn-outline-success w-100 mt-3" id="add-details">Add Details</button>
								
								<button class="btn btn-primary w-100 mt-2" id="refresh-btn">Refresh</button>

                            </div>
                        </section>
                    </div>
                </main>
            </div>

            <footer class="footer footer-black footer-white">
                <div class="container-fluid">
                    <div class="row">
                        <nav class="footer-nav">
                            <ul>
                                <li><a href="#" target="_blank"><?php echo $set['companyName']; ?></a></li>
                            </ul>
                        </nav>
                        <div class="credits ml-auto">
                            <span class="copyright">
                                © <script>
                                    document.write(new Date().getFullYear())
                                </script> <i class="fa fa-heart heart"></i> <?php echo $set['companyName']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
    <script src="assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>


    <script>
    document.getElementById('refresh-btn').addEventListener('click', () => {
        location.reload(); // Reloads the current page
    });

    $(document).ready(function() {
        const transactionDetails = document.getElementById('transaction-details');
        const noTransaction = document.getElementById('no-transaction');
        const subtotal = document.getElementById('subtotal');
        const total = document.getElementById('total');
        const retailBtn = document.getElementById('retail-btn');
        const bulkBtn = document.getElementById('bulk-btn');
        const addDetailsBtn = document.getElementById('add-details');
        const priceInput = document.getElementById('price-input');
        const emptyCylinder = document.getElementById('empty-cylinder');
        const fillWeight = document.getElementById('fill-weight');
        const transactionDate = document.getElementById('transaction-date');
        const customer = document.getElementById('customer');
        const saveBtn = document.getElementById('save-btn');

        var fillname = document.getElementById('fill-name');
        var total2 = 0;
        var order = [];
        transactionDate.innerText = new Date().toLocaleString();
        let currentPrice = <?php echo $set['retailPrice'] ?>;
        priceInput.value = `₦${currentPrice}`;

        retailBtn.addEventListener('click', () => {
            currentPrice = <?php echo $set['retailPrice'] ?>;
            priceInput.value = `₦${currentPrice}`;
        });

        bulkBtn.addEventListener('click', () => {
            currentPrice = <?php echo $set['bulkPrice'] ?>;
            priceInput.value = `₦${currentPrice}`;
        });

        addDetailsBtn.addEventListener('click', () => {
            const empty = emptyCylinder.value;
            const fill = fillWeight.value;
            customer.innerText = fillname.value;

            if (empty && fill && !isNaN(empty) && !isNaN(fill) && fill > 0) {
                noTransaction.style.display = 'none';
                const totalWeight = parseFloat(empty) + parseFloat(fill); // Calculate TotalWeight
                const weightToFill = parseFloat(fill);
                const cost = weightToFill * currentPrice;
                const item = document.createElement('p');
                item.innerHTML = `
                    <strong>
                        Gas Type: ${currentPrice == <?php echo json_encode($set['retailPrice']); ?> ? "Retail" : "Bulk"}<br>
                        Empty Cylinder: ${empty}kg<br>
                        Quantity Filled: ${fill}kg<br>
                        Total Weight: ${totalWeight}kg<br>
                        Price per Kg: NGN ${currentPrice}<br>
                        Total Cost: NGN ${cost}
                    </strong>
                `;

                transactionDetails.appendChild(item);
                total2 += cost;
                subtotal.textContent = `₦${total2}`;
                total.textContent = `₦${total2}`;
                order.push(`${empty},${fill},${totalWeight},${cost},${currentPrice}`); // Include TotalWeight in order array
            } else {
                alert('Please fill in all details!');
            }
            console.log(order);
        });

        document.getElementById('pay-btn').addEventListener('click', () => {
            const noTransactionMessage = '<p id="no-transaction" class="text-muted">No items added yet.</p>';

            if (transactionDetails.innerHTML.trim() === noTransactionMessage) {
    alert('Empty item, select kg');
} else {
    const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    const printContent = `
        <div id="print">
            <div class="receipt-header">
                <img src="${<?php echo json_encode($set['logoURL']); ?>}" alt="Company Logo" style="max-width: 150px; height: auto;">
                <h2>${<?php echo json_encode($set['companyName']); ?>}</h2>
                <h5>${<?php echo json_encode($set['Address']); ?>}</h5>
                <h5>${<?php echo json_encode($set['Phone']); ?>}</h5>
                <p>Date: ${new Date().toLocaleString()}</p>
            </div>
            <br><br>
            <div class="receipt-body">
                <p>Customer name: ${document.getElementById('fill-name').value}</p> <br>
                ${transactionDetails.innerHTML}
                <br>
                <div class="receipt-details">
                    <p>Tax: 0.00</p>
                    <p class="total">Total: NGN ${document.getElementById('total').innerText}</p>
                </div>
            </div>
            <br><br>
            <div class="receipt-footer">
                <p>Paid with: ${selectedMethod}</p><br>
                <p>Thank you for your patronage!
                <br>Please come back again</p>
            </div>
        </div>
    `;
}


                for (let i = 0; i < order.length; i++) {
                    const data = new FormData();
                    data.append('order', order[i]);
                    data.append('customer', customer.innerText);
                    data.append('total2', total2);
                    data.append('selectedMethod', selectedMethod);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'order.php', true);

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            console.log('Success:', xhr.responseText);
                        } else {
                            console.error('Error:', xhr.statusText);
                        }
                    };

                    xhr.onerror = function () {
                        console.error('Error: AJAX request failed.');
                    };

                    xhr.send(data);
                }

                const printWindow = window.open('', '_blank', 'width=800,height=600');
                printWindow.document.open();
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Receipt</title>
                            <style>
                                @media print {
                                    body {
                                        margin: 0;
                                        font-size: 12px;
                                    }
                                    #print {
                                        width: 80mm;
                                        padding: 5mm;
                                        margin: auto;
                                        font-family: 'Courier New', Courier, monospace;
                                    }
                                    .receipt-header, .receipt-footer {
                                        text-align: center;
                                    }
                                    .receipt-details, .receipt-footer {
                                        border-top: 1px dashed #000;
                                        margin-top: 10px;
                                        padding-top: 5px;
                                    }
                                    .total {
                                        font-weight: bold;
                                        text-align: right;
                                    }
                                }
                            </style>
                        </head>
                        <body onload="window.print(); window.close();">
                            ${printContent}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                transactionDetails.innerHTML = noTransactionMessage;
            }
        });
    });
</script>

</body>

</html>