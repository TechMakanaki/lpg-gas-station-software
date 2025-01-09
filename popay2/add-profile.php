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

$query = mysqli_query($con, "SELECT * FROM configuration WHERE ID='1'");
$set = mysqli_fetch_assoc($query);

$showToast = false; // Variable to control the toast display
$toastMessage = ""; // Variable to hold the toast message

if (isset($_POST['sub'])) {
    $fullname = $_POST['fullname'];
    $username2 = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username already exists
    $query = mysqli_query($con, "SELECT * FROM admin WHERE Username='$username2'");
    if (mysqli_num_rows($query) == 0) {
        mysqli_query($con, "INSERT INTO admin (Fullname, Username, Role, Password) VALUES ('$fullname', '$username2', '$role', '$hashedPassword')");
        $showToast = true;
        $toastMessage = "Account created successfully!";
    } else {
        $showToast = true;
        $toastMessage = "Username already exists!";
    }
}

// Fetch all users
$users = mysqli_query($con, "SELECT * FROM admin");

if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    // Delete the record from the database
    if (mysqli_query($con, "DELETE FROM admin WHERE ID='$deleteId'")) {
        // If deletion is successful, return success response
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully!']);
        // Redirect after deletion
        header("location: add-profile.php");
        exit(); // Ensure the script stops executing after redirect
    } else {
        // If deletion fails, return error response
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete user!']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Gas Station POS - Profile Management</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
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
                        <a class="navbar-brand" href="#">Profile and Privileges</a>
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
                            <h5 class="card-title">User  Management</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <form id="userForm" method="post">
                                        <div class="form-group">
                                            <label for="fullname">Fullname:</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="username">Username:</label>
                                            <input type="text" class=" form-control" id="username" name="username" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="role">Role:</label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="">Select Role</option>
                                                <option value="Admin">Admin</option>
                                                <option value="Sales">Sales</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password:</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="sub">Add User</button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <h4>Help & Documentation</h4>
                                    <p>If you need assistance, please refer to the <a href="help.html">Help Documentation</a> or contact support.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <h5 class="card-title">All Users</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fullname</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($users)) { ?>
                                    <tr>
                                        <td><?php echo $user['Fullname']; ?></td>
                                        <td><?php echo $user['Username']; ?></td>
                                        <td><?php echo $user['Role']; ?></td>
                                        <td>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#editUser Modal" data-id="<?php echo $user['ID']; ?>" data-fullname="<?php echo $user['Fullname']; ?>" data-username="<?php echo $user['Username']; ?>" data-role="<?php echo $user['Role']; ?>"><i class="fa fa-edit"></i> Edit</a>
                                            <a href="?delete=<?php echo $user['ID']; ?>" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fa fa-trash"></i> Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
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
                    <div class="credits">
                        <span class="copyright">
                            © <script>document.write(new Date().getFullYear())</script> <i class="fa fa-heart heart"></i> <?php echo $set['companyName']; ?>
                        </span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUser Modal" tabindex="-1" aria-labelledby="editUser ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUser ModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUser Form" method="post" action="edit-user.php">
                        <input type="hidden" name="id" id="editUser Id">
                        <div class="form-group">
                            <label for="editFullname">Fullname:</label>
                            <input type="text" class="form-control" id="editFullname" name="fullname" required>
                        </div>
                        <div class="form-group">
                            <label for="editUsername">Username:</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="editRole">Role:</label>
                            <select class="form-control" id="editRole" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Admin">Admin</option>
                                <option value="Sales">Sales</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editPassword">Password:</label>
                            <input type="password" class="form-control" id="editPassword" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="editUser ">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script>
        // Populate the edit modal with user data
        $('#editUser  Modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id'); // Extract info from data-* attributes
            var fullname = button.data('fullname');
            var username = button.data('username');
            var role = button.data('role');

            // Update the modal's content
            var modal = $(this);
            modal.find('#editUser  Id').val(id);
            modal.find('#editFullname').val(fullname);
            modal.find('#editUsername').val(username);
            modal.find('#editRole').val(role);
        });
    </script>
</body>

</html>