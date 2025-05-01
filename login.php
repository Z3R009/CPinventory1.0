<?php
include 'DBConnection.php';
require_once 'pear/pear/HTTP/Request2.php';

if (isset($_SESSION['error_message'])) {
    echo "<p style='color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . "</p>";
    unset($_SESSION['error_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $capturedImage = $_POST['captured_image']; // Base64-encoded image

    // Decode the image
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedImage));

    // Check for SQL Injection or XSS
    if (detectSQLInjection($username) || detectSQLInjection($password) || detectXSS($username) || detectXSS($password)) {
        $anomaly = "SQL Injection or XSS detected";

        // Identify which field caused the anomaly
        $anomaly_field = detectSQLInjection($username) || detectXSS($username) ? 'username' : 'password';
        $anomaly_input = $anomaly_field === 'username' ? $username : $password;

        // Save details of the suspicious activity
        $stmt = $connection->prepare("INSERT INTO suspicious_logins (field, input_value, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $anomaly_field, $anomaly_input, $imageData);
        $stmt->execute();
        $stmt->close();

        // Send SMS alert
        // sendAlertMessage("Suspicious activity detected. Input: $anomaly_input");

        $_SESSION['error_message'] = "Suspicious activity detected in the $anomaly_field field.";
        header('Location: login.php');
        exit();
    }

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

            // Generate OTP
            $otp = rand(100000, 999999); // Example OTP generation
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; // OTP expires in 5 minutes

            // Fetch user's contact number
            $contact_number = $row['contact_number'];
            if ($contact_number) {
                // Send OTP using PhilSMS
                $authToken = '1035|i8jvhDHhiHU60rUAP4jTv5Z4PCiWIRPilG6ssyAg';
                $messageText = "Your OTP is $otp. It is valid for 5 minutes.";
                $apiUrl = 'https://app.philsms.com/api/v3/sms/send';
                $senderID = 'PhilSMS';

                $send_data = [
                    'sender_id' => $senderID,
                    'recipient' => "+63" . ltrim($contact_number, '0'), // Ensure +63 format
                    'message' => $messageText,
                ];
                $parameters = json_encode($send_data);

                // Initialize cURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Authorization: Bearer $authToken",
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 200) {
                    echo "OTP sending failed. Response: " . $response;
                    exit();
                }
            } else {
                echo "Contact number not found for the user.";
                exit();
            }

            // Redirect to OTP verification page
            header('Location: otp_verification.php');
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

// Function to detect SQL Injection
function detectSQLInjection($input)
{
    $pattern = '/(\b(SELECT|INSERT|DELETE|DROP|UPDATE|UNION|--|#|\/\*|\*\/|xp_)\b|["\']|;)/i';
    return preg_match($pattern, $input);
}

// Function to detect XSS
function detectXSS($input)
{
    $pattern = '/(<script.*?>|on[a-z]+=[\'"]?.*?[\'"]?|javascript:|data:|base64,|<.*?(on[a-z]+|style|href|src)=)/i';
    return preg_match($pattern, $input);
}

// Function to send SMS alert when scripting attack is detected
function sendAlertMessage($message)
{
    $alert_number = '09277603828'; // The target phone number for the alert
    $authToken = '1035|i8jvhDHhiHU60rUAP4jTv5Z4PCiWIRPilG6ssyAg';
    $apiUrl = 'https://app.philsms.com/api/v3/sms/send';
    $senderID = 'PhilSMS';

    $send_data = [
        'sender_id' => $senderID,
        'recipient' => "+63" . ltrim($alert_number, '0'), // Ensure +63 format
        'message' => $message,
    ];
    $parameters = json_encode($send_data);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $authToken",
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "Alert SMS sending failed. Response: " . $response;
    }
}
?>



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
        <form method="post" enctype="multipart/form-data" id="login-form">
            <img class="img" src="img/img3.png" alt="">
            <video id="video" autoplay style="display: none;"></video>
            <canvas id="canvas" style="display: none;"></canvas>
            <input type="hidden" name="captured_image" id="captured_image">
            <div class="form-group">
                <input type="text" name="username" id="username" placeholder="Username" required autocomplete="off"
                    class="styled-input">
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password" required autocomplete="off"
                    class="styled-input">
            </div>
            <div class="form-group">
                <button type="submit" onclick="captureImage()"><strong>Log In</strong></button>
            </div>
        </form>

    </div>
</body>

<script>
    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");
    const capturedImageInput = document.getElementById("captured_image");

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            console.error("Camera access denied: ", err);
        });

    function captureImage() {
        const context = canvas.getContext("2d");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        capturedImageInput.value = canvas.toDataURL("image/png"); // Convert to Base64
    }

</script>

</html>