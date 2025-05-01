<?php
include 'DBConnection.php';

if (isset($_SESSION['error_message'])) {
    echo "<p style='color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . "</p>";
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Initialize login attempts for the session if not already set
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    if (!isset($_SESSION['login_attempts'][$username])) {
        $_SESSION['login_attempts'][$username] = 0;
    }

    // Query the database for the user
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];
        $user_status = $row['status'];

        // Check if the account is locked
        if ($user_status === 'locked') {
            $_SESSION['error_message'] = 'Your account is locked. Please contact the administrator.';
            header('Location: login.php');
            exit();
        }

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Successful login
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_type'] = $row['user_type'];


            // Log the login activity
            $currentDateTime = date("Y-m-d H:i:s");
            $action_user_id = $_SESSION['user_id'];
            $details = "User logged in";
            $insertLogSql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($insertLogSql);

            if ($stmt) {
                $stmt->bind_param("sss", $action_user_id, $_SESSION['user_id'], $details);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Error inserting login log: " . $connection->error;
            }

            // Reset login attempts on success
            unset($_SESSION['login_attempts'][$username]);

            // Redirect to the dashboard or appropriate page
            if ($_SESSION['user_type'] == 'admin') {
                header('Location: manage_items.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['user_type'] == 'staff') {
                header('Location: manage_items_c.php?user_id=' . $_SESSION['user_id']);
            } else {
                $_SESSION['error_message'] = 'Invalid user type value';
                header('Location: login.php');
                exit();
            }
            exit();
        } else {
            // Increment login attempts for failed password
            $_SESSION['login_attempts'][$username]++;

            // Lock the account after 3 failed attempts
            if ($_SESSION['login_attempts'][$username] >= 3) {
                $lock_sql = "UPDATE users SET status = 'locked' WHERE username = ?";
                $lock_stmt = $connection->prepare($lock_sql);
                $lock_stmt->bind_param("s", $username);
                $lock_stmt->execute();
                $lock_stmt->close();

                $_SESSION['error_message'] = 'Your account is now locked due to too many failed login attempts. Please contact the administrator.';
                header('Location: login.php');
                exit();
            }

            $_SESSION['error_message'] = 'Incorrect password. Attempts left: ' . (3 - $_SESSION['login_attempts'][$username]);
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Incorrect username.';
        header('Location: login.php');
        exit();
    }

}
?>

<!-- include 'DBconnection.php';
// Display error message if it exists
if (isset($_SESSION['error_message'])) {
    echo "<p style='color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . "</p>";
    unset($_SESSION['error_message']); // Clear the message after displaying it
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $otp = $_POST['otp']; // OTP entered by user

    // Initialize session variables if not already set
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = array();
    }

    // Initialize OTP attempts tracking in session
    if (!isset($_SESSION['otp_attempts'])) {
        $_SESSION['otp_attempts'] = array();
    }

    // Query the user from the database
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];
        $user_status = $row['status'];  // Fetch the current status (locked or unlocked)

        // Check if the user is locked
        if ($user_status === 'locked') {
            $_SESSION['error_message'] = 'Your account is locked. Please contact the administrator.';
            header('Location: login.php');
            exit();
        }

        // Verify the password if the account is not locked
        if (password_verify($password, $hashed_password)) {
            // Now check for the OTP
            if (!isset($_SESSION['otp_attempts'][$username])) {
                $_SESSION['otp_attempts'][$username] = 0;
            }

            // Check if OTP attempts have exceeded the limit (e.g., 3 attempts)
            if ($_SESSION['otp_attempts'][$username] >= 3) {
                // Lock the account if too many OTP attempts
                $lock_sql = "UPDATE users SET status = 'locked' WHERE username = ?";
                $lock_stmt = $connection->prepare($lock_sql);
                $lock_stmt->bind_param("s", $username);
                $lock_stmt->execute();
                $lock_stmt->close();

                $_SESSION['error_message'] = 'Your account is locked due to too many failed OTP attempts. Please contact the administrator.';
                header('Location: login.php');
                exit();
            }

            // Check if the OTP is correct
            $otp_sql = "SELECT * FROM user_otps WHERE user_id = ? AND otp = ? AND status = 'unused' LIMIT 1";
            $otp_stmt = $connection->prepare($otp_sql);
            $otp_stmt->bind_param("is", $row['user_id'], $otp);
            $otp_stmt->execute();
            $otp_result = $otp_stmt->get_result();

            if ($otp_result->num_rows > 0) {
                // OTP is correct, mark it as used
                $otp_row = $otp_result->fetch_assoc();
                $update_otp_sql = "UPDATE user_otps SET status = 'used' WHERE id = ?";
                $update_otp_stmt = $connection->prepare($update_otp_sql);
                $update_otp_stmt->bind_param("i", $otp_row['id']);
                $update_otp_stmt->execute();
                $update_otp_stmt->close();

                // Successful login
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['login_attempts'][$username] = 0; // Reset login attempts
                $_SESSION['otp_attempts'][$username] = 0; // Reset OTP attempts

                // Log the login activity
                $currentDateTime = date("Y-m-d H:i:s");
                $action_user_id = $_SESSION['user_id'];
                $details = "User logged in";
                $insertLogSql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
                $stmt = $connection->prepare($insertLogSql);
                $stmt->bind_param("sss", $action_user_id, $_SESSION['user_id'], $details);
                $stmt->execute();
                $stmt->close();

                // Check and refill OTPs if all are used
                checkAndRefillOTPs($_SESSION['user_id'], $connection);

                // Redirect based on user type
                if ($_SESSION['user_type'] == 'admin') {
                    header('Location: manage_items.php?user_id=' . $_SESSION['user_id']);
                } elseif ($_SESSION['user_type'] == 'staff') {
                    header('Location: manage_items_c.php?user_id=' . $_SESSION['user_id']);
                } else {
                    $_SESSION['error_message'] = 'Invalid user type value';
                    header('Location: login.php');
                    exit();
                }
                exit();
            } else {
                // Increment OTP attempts if OTP is incorrect
                $_SESSION['otp_attempts'][$username]++;

                $_SESSION['error_message'] = 'Invalid or expired OTP';
                header('Location: login.php');
                exit();
            }
        } else {
            // Handle incorrect password and login attempts (existing code)
            // Increment login attempts if password is incorrect
            if (!isset($_SESSION['login_attempts'][$username])) {
                $_SESSION['login_attempts'][$username] = 0;
            }

            $_SESSION['login_attempts'][$username]++;

            // Lock account after 3 failed attempts
            if ($_SESSION['login_attempts'][$username] >= 3) {
                // Lock the account in the database
                $lock_sql = "UPDATE users SET status = 'locked' WHERE username = ?";
                $lock_stmt = $connection->prepare($lock_sql);
                $lock_stmt->bind_param("s", $username);
                $lock_stmt->execute();
                $lock_stmt->close();

                $_SESSION['error_message'] = 'Your account is now locked due to too many failed login attempts. Please contact the administrator.';
                header('Location: login.php');
                exit();
            }

            $_SESSION['error_message'] = 'Incorrect password';
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Incorrect Username';
        header('Location: login.php');
        exit();
    }
}

// Function to check if all OTPs are used and refill if needed
function checkAndRefillOTPs($user_id, $connection)
{
    // Check if all OTPs for the user are marked as "used"
    $sql = "SELECT COUNT(*) as total_otps, SUM(status = 'used') as used_otps FROM user_otps WHERE user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // If all OTPs are used, refill them
    if ($row['total_otps'] == $row['used_otps']) {
        generateOTPs($user_id, 20, $connection);  // Generate 20 new OTPs
    }
    $stmt->close();
}

// Function to generate new OTPs
function generateOTPs($user_id, $num_otps, $connection)
{
    for ($i = 0; $i < $num_otps; $i++) {
        $otp = rand(100000, 999999);  // Generate a 6-digit OTP
        $stmt = $connection->prepare("INSERT INTO user_otps (user_id, otp, status) VALUES (?, ?, 'unused')");
        $stmt->bind_param("is", $user_id, $otp);
        $stmt->execute();
    }
} -->


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        body {
            overflow: hidden;
        }

        body {
            background-image: url('img/2.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            overflow-y: none;

        }

        h1 {
            color: black;
            font-family: clinton;
            font-weight: bold;
            text-align: center;
        }

        h2 {
            font-family: clinton;
            font-weight: bold;
            text-align: center;
        }

        .forms {
            background-color: #fff;
            margin-bottom: 20px;
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            width: 400px;
            height: 350px;
            margin-top: 70px;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(0.3px);
            -webkit-backdrop-filter: blur(0.3px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .form-group {
            margin-bottom: 10px;
        }

        input[type="text"],
        [type="number"] {
            margin-top: 10px;
            width: 400px;
            height: 50px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: medium;
        }

        input[type="password"] {
            width: 400px;
            height: 50px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: medium;
        }

        button {
            margin-top: 10px;
            background-color: #31304D;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 400px;
            height: 50px;
            font-size: 16px;
        }

        .styled-input::placeholder {
            font-size: medium;
        }

        .img {
            height: 85px;
            width: 380px;
            margin-left: 10px;
            border-radius: 28px;
            margin-bottom: 30px;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="forms">
        <form method="post">
            <img class="img" src="img/img3.png" alt="">
            <div class="form-group">
                <input type="text" name="username" id="username" placeholder="Username" required autocomplete="off"
                    class="styled-input">
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password" required autocomplete="off"
                    class="styled-input">
            </div>

            <!-- <div class="form-group">
                <input type="number" name="otp" placeholder="One-Time Password" required>
            </div> -->
            <div class="form-group">
                <button type="submit"><strong>Log In</strong></button>
            </div>
            <?php if (isset($locked_message)): ?>
                <div class="error-message"><?php echo $locked_message; ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>


</html>