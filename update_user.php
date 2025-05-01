<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';

if (isset($_SESSION['error_message'])) {
    echo "
    <style>
        #errorMessage {
            position: fixed;
            top: 5%;
            left: 50%;
            transform: translate(-50%, -50%);
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
    unset($_SESSION['error_message']);
    echo "<script>
        setTimeout(function() {
            document.getElementById('errorMessage').style.opacity = 0;
        }, 3000);
    </script>";
}

// Utility functions
function detectXSS($input)
{
    $pattern = '/(<script.*?>|on[a-z]+=[\'"]?.*?[\'"]?|javascript:|data:|base64,|<.*?(on[a-z]+|style|href|src)=)/i';
    return preg_match($pattern, $input);
}

function detectSQLInjection($input)
{
    $pattern = '/(\b(SELECT|INSERT|DELETE|DROP|UPDATE|UNION|--|#|\/\*|\*\/|xp_)\b|["\']|;)/i';
    return preg_match($pattern, $input);
}

function logSuspiciousActivity($user_id, $input_value, $connection)
{
    $details = "Suspicious activity detected: $input_value";
    insertLog($user_id, 0, $details, $connection);
}

function insertLog($action_user_id, $updated_user_id, $details, $connection)
{
    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iis", $action_user_id, $updated_user_id, $details);

    if (!$stmt->execute()) {
        error_log("Error inserting log: " . $stmt->error);
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $fullname = $_POST['fullname'];
    $contact_number = $_POST['contact_number'];
    $status = $_POST['status'];

    // Check for scripting attacks
    if (detectXSS($fullname) || detectSQLInjection($fullname) || detectXSS($status) || detectSQLInjection($status)) {
        $current_user_id = $_SESSION['user_id'];
        logSuspiciousActivity($current_user_id, "Scripting attack detected during update attempt", $connection);
        $_SESSION['error_message'] = "Suspicious activity detected. Update aborted.";
        header('Location: manage_users.php');
        exit();
    }

    // Sanitize input values
    $fullname = htmlspecialchars(trim($fullname));
    $contact_number = htmlspecialchars(trim($contact_number));
    $status = htmlspecialchars(trim($status));

    // Update query
    $sql = "UPDATE users SET fullname = ?, contact_number = ?, status = ? WHERE user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $fullname, $contact_number, $status, $user_id);

    if ($stmt->execute()) {
        $action_user_id = $_SESSION['user_id'];
        $details = "User updated successfully: Fullname=$fullname, Status=$status";
        insertLog($action_user_id, $user_id, $details, $connection);
        header('Location: manage_users.php');
    } else {
        $_SESSION['error_message'] = "Error updating user. Please try again.";
        logSuspiciousActivity($_SESSION['user_id'], $stmt->error, $connection);
        header('Location: manage_users.php');
    }

    $stmt->close();
}
?>