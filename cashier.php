<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_staff.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = mysqli_real_escape_string($connection, $_POST['search']);

    // Modify your SQL query to include the search condition
    $sql = "SELECT * FROM items WHERE item_name LIKE '%$search%'";
    $result = mysqli_query($connection, $sql);
} else {
    // If no search was performed, fetch all records
    $sql = "SELECT * FROM items";
    $result = mysqli_query($connection, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/cashier.css">
</head>

<style>
    td:nth-child(2),
    th:nth-child(2) {
        width: 120px;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 140px;
    }

    td:nth-child(4),
    th:nth-child(4) {
        width: 250px;
    }

    td:nth-child(5),
    th:nth-child(5) {
        width: 60px;
    }

    td:nth-child(6),
    th:nth-child(6) {
        width: 200px;
    }

    td:nth-child(7),
    th:nth-child(7) {
        width: 150px;
    }

    td:nth-child(8),
    th:nth-child(8) {
        width: 120px;
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
        transform: translate(-8%, -8%);
        background: #fff;
        width: 190px;
        height: 180px;
        padding: 30px;
        padding-top: 20px;
        border-radius: 12px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
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
</style>

<body>
    <div class="sidebar">
        <ul class="menu">
            <br>
            <li style="background: #e0e0e058;"><a href="cashier.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-users"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
            <li><a href="manage_items_c.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Items</span>
                </a>
            </li>
            <li><a href="stock_out_reports_c.php?user_id=<?php echo $user_id; ?>"> <i class="fas fa-book"></i>
                    <span>Stock-Out Reports</span>
                </a>
            </li>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
        </ul>
    </div>

    <div class="main-content">
        <div class="header-wrapper">
            <div class="header-title">
                <h2>
                    Dashboard
                </h2>
            </div>

            <button class="img-user-btn" id="showImg"></button>
            <div class="containerz" id="popupContainerz">
                <div class="form-containerz">
                    <ul style="text-align: center;">
                        <span>
                            <?php echo $_SESSION['fullname']; ?>
                        </span>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="act_log_s.php?user_id=<?php echo $user_id; ?>">
                                <span>Activity Log</span>
                            </a>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="settings_s.php?user_id=<?php echo $user_id; ?>">
                                <span>Settings</span>
                            </a>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="logout.php">
                                <span>Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

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

</body>

</html>