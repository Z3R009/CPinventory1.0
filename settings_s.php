<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_staff.php';

$error_old = "";
$error_new = "";
$error_con = "";
?>

<?php
if (isset($_POST['change'])) {
    $user_id = $_SESSION['user_id'];  // assuming 'user_id' is stored in the session
    $password_old = $_POST['password_old'];
    $password_new = $_POST['password_new'];
    $password_hash = password_hash($password_new, PASSWORD_DEFAULT);
    $password_con = $_POST['password_con'];

    $password_new = $_POST['password_new'];
    $hasNumber = preg_match('/\d/', $password_new);
    $hasCapital = preg_match('/[A-Z]/', $password_new);
    $isLengthValid = strlen($password_new) >= 8;

    if (!$isLengthValid || !$hasNumber || !$hasCapital) {
        $error_new = "Password must be at least 8 characters long and contain a number and a capital letter.";
    } elseif ($password_new === $password_con) {
        // Update the password in the database
        // ... (existing code)
    } else {
        $error_new = "Passwords do not match.";
        $error_con = "Passwords do not match.";
    }


    // Fetch the current password from the database
    $query = "SELECT password FROM users WHERE user_id = '$user_id'";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_password_hash = $row['password'];

        // Verify the old password
        if (password_verify($password_old, $current_password_hash)) {
            // Check if new password and confirm password match
            if ($password_new === $password_con) {
                // Hash the new password
                $password_hash = password_hash($password_new, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_query = "UPDATE users SET password = '$password_hash' WHERE user_id = '$user_id'";

                if ($connection->query($update_query)) {
                    // Log the action
                    $action_user_id = $_SESSION['user_id'];
                    $details = "Password Changed";
                    insertLog($action_user_id, $user_id, $details);

                    header('Location: settings_s.php');
                } else {
                    // Handle error
                }
            } else {
                $error_new = "Passwords do not match.";
                $error_con = "Passwords do not match.";
            }
        } else {
            $error_old = "Incorrect old password.";
        }
    } else {
        $error_old = "User not found.";
    }
}

function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    return ($row['count'] == 0);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['upd_name'])) {
        $user_id = $_SESSION['user_id'];
        $username = $_POST['username'];

        $sql = "UPDATE users SET username='$username' WHERE user_id=$user_id";
        $result = mysqli_query($connection, $sql);
        if ($result) {
            $_SESSION['username'] = $username;
            // Log the action
            $action_user_id = $_SESSION['user_id'];
            $details = "Username Changed";
            insertLog($action_user_id, $user_id, $details);

            header('Location: settings_s.php');
        } else {
            // Handle error
        }
    }
}

function insertLog($action_user_id, $updated_user_id, $details)
{
    global $connection;

    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) 
            VALUES ('$action_user_id', '$updated_user_id', '$details')";

    $connection->query($sql);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/settings.css">
</head>

<style>
    .card1 {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        background: #fff;
        border-radius: 10px;
        padding: 10px 2rem;
        margin-bottom: 1rem;
        float: left;
        width: 35%;
        height: 85%;
    }

    .card2 {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        background: #fff;
        border-radius: 10px;
        padding: 10px 2rem;
        margin-bottom: 1rem;
        float: right;
        width: 64%;
        height: 85%;
    }



    input[type="text"],
    input[type="password"],
    select[name="user_type"] {
        width: 330px;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        text-align: center;
        margin-bottom: 20px;
    }

    button[type="submit"] {
        background-color: #31304D;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        width: 320px;
        font-size: 16px;
        margin-bottom: 20px;
        float: right;
    }

    button[type="clear"] {
        background-color: #31304D;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        width: 320px;
        font-size: 16px;
        margin-bottom: 20px;
        float: left;
    }

    h3 {
        margin-bottom: 50px;
        margin-top: 60px;
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
        height: 300px;
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

    .error-message {
        color: red;
        font-size: 14px;
    }
</style>

<body>
    <div class="sidebar">
        <ul class="menu">
            <br>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
            <li><a href="manage_items_c.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Items</span>
                </a>
            </li>
            <li><a href="stock_out_reports_c.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Stock-Out Reports</span>
                </a>
            </li>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
        </ul>
    </div>

    <div class="main-content">
        <div class="header-wrapper">
            <div class="header-title">
                <h2>Settings</h2>
            </div>

            <button class="img-user-btn" id="show"></button>
            <div class="containerz" id="popupContainerz">
                <div class="form-containerz">
                    <ul class="menu" style="text-align: center;">
                        <span style="font-size: larger;">
                            <strong><?php echo $_SESSION['fullname']; ?></strong>
                        </span>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="act_log_s.php?user_id=<?php echo $user_id; ?>">
                                <i class="fas fa-clipboard"></i>
                                <span style="margin-left: 20px; font-size: large; ">
                                    Activity Log</span>
                            </a>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="settings_s.php?user_id=<?php echo $user_id; ?>">
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

        <div class="card1">
            <div class="form-container">
                <form method="post">
                    <h3>User Info</h3>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" value="<?php echo $_SESSION['fullname']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" value="<?php echo $_SESSION['username']; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="user_type">Role</label>
                        <input type="text" value="<?php echo $_SESSION['user_type']; ?>" readonly>
                    </div>
                </form>
            </div>
        </div>

        <div class="card2">
            <div class="form-container">
                <form method="post">
                    <h3 style="margin-bottom: 10px; margin-top: 20px">Change Password</h3>
                    <p style="margin-bottom: 10px;">Password should consist of atleast<strong> 8 characters,</strong>
                        contain a
                        <strong>Capital Letter</strong> and a <strong>Number</strong>
                    </p>
                    <div class="form-group">
                        <input type="password" id="password_old" style="width: 650px; margin-bottom: 30px; "
                            name="password_old" placeholder="Current Password" required autocomplete="off">
                        <span class="error-message">
                            <?php echo $error_old; ?>
                        </span>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password_new" style="width: 650px; margin-bottom: 30px; "
                            name="password_new" placeholder="New Password" required autocomplete="off">
                        <span id="password-strength"></span>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password_con" style="width: 650px; margin-bottom: 30px; "
                            name="password_con" placeholder="Re-Type New Password" required autocomplete="off">
                        <span class="error-message" id="error_con">
                            <?php echo $error_con; ?>
                        </span>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="change" disabled>Change Password</button>
                    </div>
                    <div class="form-group">
                        <button type="clear" name="clear">Clear</button>
                    </div>
                </form>
            </div>
            <div class="form-container">
                <form method="post">
                    <hr style="border-top: 5px solid #31304D;">
                    <h3 style="margin-top: 20px;">Change Username</h3>
                    <div class="form-group" style="margin-top: -10px;">
                        <input type="text" id="usernameInput" style="float: left; width: 410px; margin-top: -15px; "
                            name="username" placeholder=" New Username" autocomplete="off" required>
                        <br>
                    </div>
                    <div class="form-group">
                        <button type="clear" id="submitButton"
                            style="float: left; width: 230px; margin-left: 420px; margin-top: -60px; "
                            name="upd_name">Change
                            Username</button>
                        <br>
                        <span id="usernameAvailability"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.clear').addEventListener('click', function () {
            document.getElementById('password_old').value = '';
            document.getElementById('password_new').value = '';
            document.getElementById('password_con').value = '';
        });
    </script>


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

    <!-- -------popup for sett&log-------- -->
    <script>
        // Function to close popup when clicking outside of it
        function closePopupOutside(event) {
            const popupContainerz = document.getElementById('popupContainerz');
            const addButton = document.getElementById('show');

            if (!popupContainerz.contains(event.target) && event.target !== addButton) {
                console.log('Clicked outside the popup');
                popupContainerz.classList.remove('active');
            }
        }

        // Add event listener to show popup
        document.getElementById('show').addEventListener('click', function () {
            console.log('Add user button clicked');
            document.getElementById('popupContainerz').classList.add('active');
        });

        // Add event listener to close popup when clicking outside of it
        document.addEventListener('click', closePopupOutside);
    </script>

    <!-- ---------password--------- -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password_new');
            const strengthIndicator = document.getElementById('password-strength');
            const changeButton = document.querySelector('button[name="change"]');

            passwordInput.addEventListener('input', function () {
                const password = passwordInput.value;

                // Regular expressions to check for number and capital letter
                const hasNumber = /\d/.test(password);
                const hasCapital = /[A-Z]/.test(password);

                // Check if the password length is at least 8 characters
                const isLengthValid = password.length >= 8;

                // Update the strength indicator based on the conditions
                if (password === '') {
                    strengthIndicator.textContent = '';  // Clear the text if the password is empty
                } else if (isLengthValid && hasNumber && hasCapital) {
                    strengthIndicator.textContent = '';
                    strengthIndicator.style.color = 'green';

                    // Enable the "Change Password" button
                    changeButton.removeAttribute('disabled');
                } else {
                    strengthIndicator.textContent = 'Password is Weak';
                    strengthIndicator.style.color = 'red';


                    // Disable the "Change Password" button
                    changeButton.setAttribute('disabled', 'true');
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordNewInput = document.getElementById('password_new');
            const passwordConInput = document.getElementById('password_con');
            const errorConSpan = document.getElementById('error_con');
            const changeButton = document.querySelector('button[name="change"]');

            function checkPasswords(event) {
                // Check if the event is originating from the password_con input field
                if (event.currentTarget === passwordConInput) {
                    const passwordNew = passwordNewInput.value;
                    const passwordCon = passwordConInput.value;

                    if (passwordCon !== passwordNew && passwordCon !== '') {
                        errorConSpan.textContent = 'Passwords do not match.';
                        changeButton.setAttribute('disabled', 'true');
                    } else if (passwordCon === '') {
                        errorConSpan.textContent = '';
                        changeButton.setAttribute('disabled', 'true');
                    } else {
                        errorConSpan.textContent = '';
                        changeButton.removeAttribute('disabled');
                    }
                }
            }

            // Add event listeners to both password fields
            passwordNewInput.addEventListener('input', checkPasswords);
            passwordConInput.addEventListener('input', checkPasswords);
        });
    </script>

    <!-- -----------username----------------- -->
    <script>
        document.getElementById('usernameInput').addEventListener('input', function () {
            var username = this.value;
            var message = document.getElementById('usernameAvailability');
            var submitButton = document.getElementById('submitButton'); // Get the submit button

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
</body>

</html>