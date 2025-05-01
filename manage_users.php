<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';

if (isset($_SESSION['error_message'])) {
    // Style for centered floating error message
    echo "
    <style>
        #errorMessage {
            position: fixed;
            top: 5%;
            left: 50%;
            transform: translate(-50%, -50%); /* Center the message */
            background-color: #f8d7da;
            color: red;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease;
            text-align: center;
        }
    </style>
    <div id='errorMessage'>
        " . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . "
    </div>";

    // Clear the session message after it's displayed
    unset($_SESSION['error_message']);

    // Add a delay to remove the message after a few seconds
    echo "<script>
        setTimeout(function() {
            document.getElementById('errorMessage').style.opacity = 0;
        }, 3000);  // Message disappears after 3 seconds
    </script>";
}

// Function to check if a username is available
function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return ($row['count'] == 0);
}

// Function to detect XSS attacks
function detectXSS($input)
{
    $pattern = '/(<script.*?>|on[a-z]+=[\'"]?.*?[\'"]?|javascript:|data:|base64,|<.*?(on[a-z]+|style|href|src)=)/i';
    return preg_match($pattern, $input);
}

// Function to detect SQL Injection attempts
function detectSQLInjection($input)
{
    $pattern = '/(\b(SELECT|INSERT|DELETE|DROP|UPDATE|UNION|--|#|\/\*|\*\/|xp_)\b|["\']|;)/i';
    return preg_match($pattern, $input);
}

// Function to log suspicious activity
function logSuspiciousActivity($user_id, $input_value, $connection)
{
    $details = "Suspicious activity detected: $input_value";
    insertLog($user_id, 0, $details, $connection);
}

// Function to insert logs
function insertLog($action_user_id, $updated_user_id, $details, $connection)
{
    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iis", $action_user_id, $updated_user_id, $details);

    if (!$stmt->execute()) {
        echo "Error inserting log: " . $connection->error;
    }

    $stmt->close();
}

// Main block for adding and updating users
if (isset($_POST['submit'])) {
    $fullname = $_POST['fullname'];
    $contact_number = $_POST['contact_number'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];
    $status = $_POST['status'];

    // Check for XSS and SQL Injection
    if (detectXSS($fullname) || detectSQLInjection($fullname) || detectXSS($username) || detectSQLInjection($username)) {
        $user_id = $_SESSION['user_id'];
        logSuspiciousActivity($user_id, "XSS or SQL Injection detected", $connection);
        $_SESSION['error_message'] = "Suspicious activity detected. The operation was not completed.";
        header('Location: manage_users.php');
        exit();
    }

    if (!isUsernameAvailable($username, $connection)) {
        echo "Username is not available. Please choose a different one.";
        exit;
    }

    $sql = "INSERT INTO users (fullname, contact_number, username, password, user_type) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $contact_number, $username, $password, $user_type);

    if ($stmt->execute()) {
        $action_user_id = $_SESSION['user_id'];
        $details = "User added: $fullname";
        insertLog($action_user_id, $connection->insert_id, $details, $connection);

        header('Location: manage_users.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $contact_number = $_POST['contact_number'];
    $status = $_POST['status'];

    // Check for XSS and SQL Injection
    if (detectXSS($fullname) || detectSQLInjection($fullname)) {
        $user_id = $_SESSION['user_id'];
        logSuspiciousActivity($user_id, "XSS or SQL Injection detected", $connection);
        $_SESSION['error_message'] = "Suspicious activity detected. The operation was not completed.";
        header('Location: manage_users.php');
        exit();
    }

    // Sanitize input values
    $fullname = htmlspecialchars(trim($fullname));
    $status = htmlspecialchars(trim($status));

    // Debugging: Check values before executing query
    echo "User ID: " . htmlspecialchars($user_id) . "<br>";
    echo "Fullname: " . htmlspecialchars($fullname) . "<br>";
    echo "Status: " . htmlspecialchars($status) . "<br>";

    // Prepare SQL statement
    $sql = "UPDATE users SET fullname = ?, contact_number = ?, status = ? WHERE user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $fullname, $contact_number, $status, $user_id);

    if ($stmt->execute()) {
        // Log the action
        $action_user_id = $_SESSION['user_id'];
        $details = "Details updated for: $fullname";
        insertLog($action_user_id, $user_id, $details, $connection);

        header('Location: manage_users.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!-- 
include 'DBConnection.php';
// include 'auth.php';
// include 'role_admin.php';

function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    return ($row['count'] == 0);
}

// Function to generate OTPs and return them as an array
function generateOTPs($user_id, $num_otps, $connection)
{
    $otps = []; // Array to store generated OTPs
    for ($i = 0; $i < $num_otps; $i++) {
        $otp = rand(100000, 999999);  // Generate a 6-digit OTP
        // Store OTP in the database
        $stmt = $connection->prepare("INSERT INTO user_otps (user_id, otp, status) VALUES (?, ?, 'unused')");
        $stmt->bind_param("is", $user_id, $otp);
        $stmt->execute();

        // Add OTP to the array for display
        $otps[] = $otp;
    }
    return $otps; // Return the array of OTPs
}

if (isset($_POST['submit'])) {
    // Sanitize input to prevent XSS
    $fullname = htmlspecialchars($_POST['fullname'], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Already hashed
    $user_type = htmlspecialchars($_POST['user_type'], ENT_QUOTES, 'UTF-8');

    // Use prepared statements to prevent SQL Injection
    $sql = "INSERT INTO users (fullname, username, password, user_type) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssss", $fullname, $username, $password, $user_type);

    if ($stmt->execute()) {
        // Generate OTPs for the newly created user and capture them for display
        $generated_otps = generateOTPs($stmt->insert_id, 10, $connection);  // Assuming you want to generate 20 OTPs

        $action_user_id = $_SESSION['user_id'];
        $details = "User added: " . htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); // Safe logging

        insertLog($action_user_id, $stmt->insert_id, $details);

        // Convert OTPs array to a string for the modal
        $otp_list = implode("<br>", $generated_otps);

        // Display the custom modal with OTPs
        echo "
            <div id='otpModal' style='display:block; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;'>
                <div style='margin:10% auto; padding:20px; width:300px; background-color:white; border-radius:10px; max-height:400px; overflow-y:auto; text-align:center;'>
                    <h3>User created successfully!</h3>
                    <p>Here are the OTPs. Please save them securely:</p>
                    <p style='font-weight:bold;'>$otp_list</p>
                    <button onclick='closeModal()'>Close</button>
                </div>
            </div>
            <script>
                function closeModal() {
                    document.getElementById('otpModal').style.display = 'none';
                    window.location.href = 'manage_users.php';
                }
            </script>
        ";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname']; // Encrypt before updating

    $sql = "UPDATE users SET fullname = '$fullname' WHERE user_id = '$user_id'";

    if ($connection->query($sql)) {
        $action_user_id = $_SESSION['user_id'];
        $details = "Fullname updated for : " . $fullname; // Decrypt to display in logs
        insertLog($action_user_id, $user_id, $details);

        header('Location: manage_users.php');
    }
}

if (isset($_POST['status']) && isset($_POST['user_id'])) {
    $status = $_POST['status'];
    $user_id = $_POST['user_id'];
    $sql = "UPDATE users SET status = '$status' WHERE user_id = '$user_id'";

    if ($connection->query($sql)) {
        $action_user_id = $_SESSION['user_id'];
        $details = "Status updated to $status for user ID: $user_id";
        insertLog($action_user_id, $user_id, $details);
        header('Location: manage_users.php');
    } else {
        echo "Error updating status: " . $connection->error;
    }
}

function insertLog($action_user_id, $updated_user_id, $details)
{
    global $connection;
    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) 
            VALUES ('$action_user_id', '$updated_user_id', '$details')";
    $connection->query($sql);
} -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/manage_users.css">
</head>

<style>
    .add-user-btn {
        background-color: #31304D;
        border-radius: 12px;
        border: 0;
        color: #eee;
        cursor: pointer;
        font-size: 18px;
        height: 50px;
        outline: 0;
        padding: 10px 20px;
        text-align: center;
        margin-left: 650px;
    }

    td:nth-child(1),
    th:nth-child(1) {
        display: none;
    }

    td:nth-child(2),
    th:nth-child(2) {
        width: 370px;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 370px;
    }

    td:nth-child(4),
    th:nth-child(4) {
        width: 300px;
    }

    td:nth-child(5),
    th:nth-child(5) {
        display: none;
    }

    td:nth-child(6),
    th:nth-child(6) {
        width: 150px;
    }

    td:nth-child(7),
    th:nth-child(7) {
        width: 130px;
    }

    td:nth-child(8),
    th:nth-child(8) {
        width: 230px;
    }

    .text {
        font-size: 30px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 20px;
        color: #31304D;
    }

    .img-user-btn {
        background-image: url("img/av.jpg");
        background-size: cover;
        background-repeat: no-repeat;
        border-radius: 12px;
        border: 0;
        color: #eee;
        cursor: pointer;
        font-size: 18px;
        height: 50px;
        width: 50px;
        outline: 0;
        padding: 10px 20px;
        text-align: center;
    }


    .containerz {
        display: none;
        position: fixed;
        top: 16%;
        right: 0;
        transform: translate(-10%, 0%);
        background: #fff;
        width: 300px;
        height: 390px;
        padding: 30px;
        padding-top: 20px;
        border-radius: 12px;
        box-shadow: 0 8px 20px 0 rgba(31, 38, 135, 0.37);
        z-index: 1000;
        align-items: center;
    }

    .containerz.active {
        display: block;
    }

    .containerz li a {
        text-decoration: none;
        color: #31304D;
    }

    .containerz li a :hover,
    .active {
        color: #31304D;
    }

    .add-user-btn:hover {
        background-color: #0C359E;
    }

    .custom-select {
        position: relative;
        display: inline-block;
        font-family: Arial, sans-serif;
        /* Matching the font */
        background-color: rgba(255, 255, 255, 0.7);
        /* Match the background color */
        border: 1px solid #cccccc;
        /* Match the border */
        border-radius: 8px;
        padding: 5px 10px;
        cursor: pointer;
        color: #31304D;
        /* Match the text color */
    }

    .custom-select select {
        padding: 10px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        font-family: Arial, sans-serif;
        /* Match the font */
        background-color: rgba(255, 255, 255, 0.7);
        /* Match the background color */
        border: none;
        border-radius: 8px;
        padding-right: 25px;
        cursor: pointer;
        color: #31304D;
        /* Match the text color */
    }

    .custom-select::after {
        content: '\25BC';
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: #31304D;
        /* Match the color */
    }
</style>

<body>
    <div class="sidebar">
        <ul class="menu">
            <br>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
            <li><a href="manage_items.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Manage Items</span>
                </a>
            </li>
            <li style="background: #e0e0e058;"><a href="manage_users.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li><a href="brand.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Brand & Item Type</span>
                </a>
            </li>
            <!-- <li><a href="all_logs.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-clipboard-list" style="margin-left: 5px;"></i>
                    <span style="margin-left: 5px;">Logs</span>
                </a>
            </li> -->
            <li>
                <div class="submenu-toggle2">
                    <a href=" #" class="logs_link">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Logs</span>
                    </a>
                </div>
                <div class="nav-submenu2">
                    <a href="all_logs.php?user_id=<?php echo $user_id; ?>" id="logsLink"
                        style="padding: 15px 0px 15px 0px; margin: 8px 0px 8px 0px; border-radius: 8px; padding-left: 45px;">Activity
                        Logs</a>
                    <a href="sus_logs.php?user_id=<?php echo $user_id; ?>" id="logsLink"
                        style="padding: 15px 0px 15px 0px; margin: 8px 0px 8px 0px; border-radius: 8px; padding-left: 38px;">Intrusion
                        Logs</a>
                </div>
            </li>
            <li>
                <div class="submenu-toggle">
                    <a href=" #" class="link">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                </div>
                <div class="nav-submenu">
                    <a href="stock_in_reports.php?user_id=<?php echo $user_id; ?>" id="stockInLink"
                        style="padding: 15px 0px 15px 0px; margin: 8px 0px 8px 0px; border-radius: 8px; padding-left: 45px;">Stock-In</a>
                    <a href="stock_out_reports.php?user_id=<?php echo $user_id; ?>" id="stockOutLink"
                        style="padding: 15px 0px 15px 0px; margin: 8px 0px 8px 0px; border-radius: 8px; padding-left: 38px;">Stock-Out</a>
                </div>
            </li>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
        </ul>
    </div>

    <div class="main-content">
        <div class="header-wrapper">
            <div class="header-title">
                <h2>Manage Users</h2>
            </div>

            <!-- popup for add_user -->
            <button class="add-user-btn" id="show">Add User</button>
            <div class="container" id="popupContainer">
                <button class="close-btn fas fa-times"></button>
                <div class="text">
                    Add User
                </div>
                <div class="form-container">
                    <form method="post">
                        <div class="form-group">
                            <input type="text" id="fullname" name="fullname" placeholder="Enter Name" autocomplete="off"
                                required>
                        </div>
                        <div class="form-group">
                            <input type="number" class="form-control" id="contact_number" name="contact_number" required
                                maxlength="11" autocomplete="off" placeholder="Enter Contact Number">
                        </div>
                        <div class="form-group">
                            <input type="text" id="username" name="username" placeholder="Enter Username"
                                autocomplete="off" required>
                            <span id="usernameAvailability"></span>
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="Enter Password"
                                autocomplete="off" required>
                        </div>
                        <select name="user_type" id="user_type">
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                        <div class="form-group">
                            <button type="submit" id="submit" name="submit">Add</button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="clear-btn">Clear</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end -->

            <button class="img-user-btn" id="showImg"></button>
            <div class="containerz" id="popupContainerz">
                <div class="form-containerz">
                    <ul class="menu" style="text-align: center;">
                        <span style="font-size: larger;">
                            <strong><?php echo $_SESSION['fullname']; ?></strong>
                        </span>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li>
                            <form method="post" action="save_database.php" style="display: inline;">
                                <button type="submit" name="backup"
                                    style="border: none; background: none; padding: 0; cursor: pointer; color: inherit; ">
                                    <i class="fas fa-database"></i>
                                    <span style="margin-left: 10px; font-size: large; ">Backup Data</span>
                                </button>
                            </form>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="act_log.php?user_id=<?php echo $user_id; ?>">
                                <i class="fas fa-clipboard"></i>
                                <span style="margin-left: 20px; font-size: large; ">
                                    Activity Log</span>
                            </a>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="settings.php?user_id=<?php echo $user_id; ?>">
                                <i class="fas fa-cog"></i>
                                <span style="margin-left: 20px; font-size: large; ">
                                    Settings</span>
                            </a>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span style="margin-left: 20px; font-size: large; ">
                                    Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>



        <!-- popup for update -->
        <div class="container" id="updatePopupContainer">
            <button class="close-btn fas fa-times"></button>
            <div class="text">
                Update User
            </div>
            <div class="form-container">
                <form method="post" action="update_user.php">
                    <div class="form-group">
                        <input type="hidden" id="update_user_id" name="user_id"
                            value="<?php echo isset($user_id) ? $user_id : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="update_fullname" name="fullname" placeholder="Enter Name"
                            autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" class="form-control" id="update_contact_number" name="contact_number"
                            required maxlength="11" autocomplete="off" placeholder="Enter Contact Number">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="update_username" name="username" placeholder="Enter Username"
                            autocomplete="off" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="username">Password</label>
                        <input type="password" id="update_password" name="password" placeholder="Enter Password"
                            autocomplete="off" required readonly>
                    </div>
                    <label for="username">Select User Type</label>
                    <select name="user_type" id="update_user_type">
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>

                    <div class="form-group">
                        <button type="submit" name="update_user">Update</button>
                    </div>
                    <div class="form-group">
                        <button type="button" class="clear-btn">Clear</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- end -->



        <div class="card">
            <div class="table-container">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Contact Number</th>
                            <th scope="col">Username</th>
                            <th scope="col">Password</th>
                            <th scope="col">User Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users ";
                        $result = mysqli_query($connection, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $user_id = $row['user_id'];
                                $fullName = $row['fullname'];
                                $contact_number = $row['contact_number'];
                                $username = $row['username'];
                                $password = $row['password'];
                                $user_type = $row['user_type'];
                                $status = $row['status'];

                                echo ' <tr> 
                                    <td>' . $user_id . '</td>
                                    <td id="fullname_' . $user_id . '">' . $fullName . '</td>
                                    <td id="contact_number_' . $user_id . '">' . $contact_number . '</td>
                                    <td id="username_' . $user_id . '">' . $username . '</td>
                                    <td id="password_' . $user_id . '">' . $password . '</td>
                                    <td id="user_type_' . $user_id . '">' . $user_type . '</td>
                                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            <select name="status" class="custom-select" onchange="this.form.submit()">
                                <option value="unlocked" ' . ($status == 'unlocked' ? 'selected' : '') . '>Unlocked</option>
                                <option value="locked" ' . ($status == 'locked' ? 'selected' : 'disabled') . '>Locked</option>
                            </select>
                        </form>
                    </td>
                                    <td>
                                    <button class="update-button" onclick="showUpdatePopup(' . $user_id . ')">Update</button>
                                        <button><a href="delete_user.php?user_id=' . $user_id . '" style="text-decoration: none; color: white;">Delete</a></button>
                                    </td>
                                    </tr> ';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Function to show update pop-up with user details
        function showUpdatePopup(user_id) {
            // Fetch user details from table
            var fullname = document.getElementById('fullname_' + user_id).innerHTML;
            var contact_number = document.getElementById('contact_number_' + user_id).innerHTML;
            var username = document.getElementById('username_' + user_id).innerHTML;
            var password = document.getElementById('password_' + user_id).innerHTML;
            var user_type = document.getElementById('user_type_' + user_id).innerHTML;

            // Set values in the update pop-up
            document.getElementById('update_user_id').value = user_id;
            document.getElementById('update_fullname').value = fullname;
            document.getElementById('update_contact_number').value = contact_number;
            document.getElementById('update_username').value = username;
            document.getElementById('update_password').value = password;
            document.getElementById('update_user_type').value = user_type;

            // Show the update pop-up
            document.getElementById('updatePopupContainer').classList.add('active');
        }

        // close button update
        document.querySelector('#updatePopupContainer .close-btn').addEventListener('click', function () {
            console.log('Close button clicked');
            document.getElementById('updatePopupContainer').classList.remove('active');
        });

        // clear button for update
        document.querySelector('#updatePopupContainer .clear-btn').addEventListener('click', function () {
            document.getElementById('update_fullname').value = '';

        });
        // add user
        document.getElementById('show').addEventListener('click', function () {
            console.log('Add user button clicked');
            document.getElementById('popupContainer').classList.add('active');
        });

        // close button add_user
        document.querySelector('.container .close-btn').addEventListener('click', function () {
            console.log('Close button clicked');
            document.getElementById('popupContainer').classList.remove('active');
        });

        // clear button for add_user
        document.querySelector('.clear-btn').addEventListener('click', function () {
            document.getElementById('fullname').value = '';
            document.getElementById('contact_number').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('user_type').value = 'admin';
        });
    </script>


    <!-- reports -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reportsSubMenu = document.querySelector('.submenu-toggle');
            const reportsSubMenuList = reportsSubMenu.nextElementSibling;

            // Initially hide the Stock In and Stock Out list items
            reportsSubMenuList.style.display = 'none';

            // Add click event to toggle visibility of Stock In and Stock Out list items
            reportsSubMenu.addEventListener('click', function (event) {
                event.preventDefault();
                reportsSubMenuList.style.display = (reportsSubMenuList.style.display === 'none') ? 'block' : 'none';
            });
        });
    </script>

    <!-- logs -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reportsSubMenu = document.querySelector('.submenu-toggle2');
            const reportsSubMenuList = reportsSubMenu.nextElementSibling;

            // Initially hide the Stock In and Stock Out list items
            reportsSubMenuList.style.display = 'none';

            // Add click event to toggle visibility of Stock In and Stock Out list items
            reportsSubMenu.addEventListener('click', function (event) {
                event.preventDefault();
                reportsSubMenuList.style.display = (reportsSubMenuList.style.display === 'none') ? 'block' : 'none';
            });
        });
    </script>

    <!-- -------popup for sett&log-------- -->
    <script>
        // Function to close popup when clicking outside of it
        function closePopupOutside(event) {
            const popupContainerz = document.getElementById('popupContainerz');
            const addButton = document.getElementById('showImg');

            if (!popupContainerz.contains(event.target) && event.target !== addButton) {
                console.log('Clicked outside the popup');
                popupContainerz.classList.remove('active');
            }
        }

        // Add event listener to show popup
        document.getElementById('showImg').addEventListener('click', function () {
            console.log('Add user button clicked');
            document.getElementById('popupContainerz').classList.add('active');
        });

        // Add event listener to close popup when clicking outside of it
        document.addEventListener('click', closePopupOutside);
    </script>

    <script>
        function syncTextFields(value, targetFieldId) {
            document.getElementById(targetFieldId).value = value;
        }
    </script>

    <!-- -----------username----------------- -->
    <script>
        document.getElementById('username').addEventListener('input', function () {
            var username = this.value;
            var message = document.getElementById('usernameAvailability');
            var submitButton = document.getElementById('submit'); // Get the submit button

            // Clear the message if the username input field is empty
            if (username.trim() === '') {
                message.textContent = ''; // Clear the message
                submitButton.disabled = true; // Disable the submit button
                submitButton.style.backgroundColor = 'gray'; // Set the background color to gray
                return; // Exit the function
            }

            // Send AJAX request to check username availability
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'check_username.php?username=' + username, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);

                    if (response.available) {
                        message.textContent = ''; // Clear the message
                        submitButton.disabled = false; // Enable the submit button
                        submitButton.style.backgroundColor = ''; // Remove any custom background color
                    } else {
                        message.textContent = 'Username is not available.';
                        message.style.color = 'red';
                        submitButton.disabled = true; // Disable the submit button
                        submitButton.style.backgroundColor = 'gray'; // Set the background color to gray
                    }
                }
            };
            xhr.send();
        });
    </script>

    <script>
        const contact_numberInput = document.getElementById('contact_number');
        const errorMessage = document.getElementById('errorMsg');

        contact_numberInput.addEventListener('input', function (e) {
            let input = e.target.value;
            let numericInput = input.replace(/\D/g, '');
            if (numericInput.length > 11) {
                numericInput = numericInput.slice(0, 11);
            }
            e.target.value = numericInput;

            if (input !== numericInput) {
                errorMessage.style.display = 'block';
            } else {
                errorMessage.style.display = 'none';
            }
        });
    </script>

</body>

</html>