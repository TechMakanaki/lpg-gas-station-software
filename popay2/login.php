<?php 
require("con.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($password != "" && $username != "") {
        // Use prepared statements to prevent SQL injection
        $stmt = $con->prepare("SELECT * FROM admin WHERE Username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify the hashed password
            if (password_verify($password, $user['Password'])) {
                $_SESSION['username'] = $username;
                header("location:dashboard.php");
                exit();
            } else {
                // Set an error message for invalid password
                $_SESSION['error'] = "Invalid username or password.";
            }
        } else {
            // Set an error message for invalid username
            $_SESSION['error'] = "Invalid username or password.";
        }
    } else {
        // Set an error message for empty fields
        $_SESSION['error'] = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Popay Foster</title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <div class="card-body">
                <h2 class="text-center mb-4">Login</h2>
                
                <!-- Display error message if it exists -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']); // Clear the message after displaying
                        ?>
                    </div>
                <?php endif; ?>

                <form id="login-form" method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" class="form-control" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <small class="text-muted">© 2024 Popay Foster Cashier POS</small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>