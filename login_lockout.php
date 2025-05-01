<?php
include 'DBconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Initialize session variables if not already set
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = array();
    }

    // Check if the user is locked out
    if (isset($_SESSION['login_attempts'][$username]) && $_SESSION['login_attempts'][$username] >= 3) {
        echo "<script>alert('You have been locked out due to too many incorrect attempts. Please contact the administrator.');</script>";
        exit();
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

        if (password_verify($password, $hashed_password)) {
            // Successful login
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_type'] = $row['user_type'];

            // Reset login attempts for this user
            $_SESSION['login_attempts'][$username] = 0;

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

            // Redirect based on user type
            if ($_SESSION['user_type'] == 'admin') {
                header('Location: manage_items.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['user_type'] == 'staff') {
                header('Location: manage_items_c.php?user_id=' . $_SESSION['user_id']);
            } else {
                echo "<script>alert('Invalid user type value');</script>";
            }
            exit();
        } else {
            // Increment login attempts for this user
            if (isset($_SESSION['login_attempts'][$username])) {
                $_SESSION['login_attempts'][$username]++;
            } else {
                $_SESSION['login_attempts'][$username] = 1;
            }

            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect Username');</script>";
    }
}
?>