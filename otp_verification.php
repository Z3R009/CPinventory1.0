<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);
    $session_otp = $_SESSION['otp'] ?? null;
    $otp_expiry = $_SESSION['otp_expiry'] ?? 0;


    echo "Entered OTP: $entered_otp<br>";
    echo "Session OTP: $session_otp<br>";
    echo "OTP Expiry: $otp_expiry<br>";

    if ($session_otp && $entered_otp == $session_otp && time() < $otp_expiry) {
        unset($_SESSION['otp'], $_SESSION['otp_expiry']); // Clear OTP data after successful verification

        // Redirect user based on their type
        if ($_SESSION['user_type'] == 'admin') {
            header('Location: manage_items.php?user_id=' . $_SESSION['user_id']);
        } elseif ($_SESSION['user_type'] == 'staff') {
            header('Location: manage_items_c.php?user_id=' . $_SESSION['user_id']);
        } else {
            echo "Invalid user type.";
        }
        exit();
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 350px;
            width: 100%;
        }

        .container h1 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        form input[type="number"] {
            width: 320px;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        form button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
            width: 100%;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Enter OTP</h1>
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="number" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>

</html>