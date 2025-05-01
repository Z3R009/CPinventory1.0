<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';
?>

<?php

$sql = "SELECT * FROM reports";
$result = mysqli_query($connection, $sql);
$chart_data = " ";
while ($row = mysqli_fetch_array($result)) {
    $transaction_type[] = $row['transaction_type'];
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<style>
    .nav-submenu :hover,
    .active {
        color: white;
        background: #31304D;
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

    /* -------------card------------- */
    .card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        background: #fff;
        border-radius: 10px;
        padding: 10px 2rem;
        margin-bottom: 1rem;
        width: 100%;
        height: 500px;
    }
</style>

<body>
    <div class="sidebar">
        <ul class="menu">
            <br>
            <li style="background: #e0e0e058;"><a href="#">
                    <i class="fas fa-users"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
            <li><a href="manage_users.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li><a href="manage_items.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Manage Items</span>
                </a>
            </li>
            <li><a href="brand.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Brand & Item Type</span>
                </a>
            </li>
            <li><a href="all_logs.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Logs</span>
                </a>
            </li>
            <li>
                <div class="submenu-toggle">
                    <a href=" #" class="link">
                        <i class="fas fa-book"></i>
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
                <h2>
                    Dashboard
                </h2>
            </div>

            <button class="img-user-btn" id="show"></button>
            <div class="containerz" id="popupContainerz">
                <div class="form-containerz">
                    <ul style="text-align: center;">
                        <span>
                            <?php echo $_SESSION['fullname']; ?>
                        </span>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="act_log.php?user_id=<?php echo $user_id; ?>">
                                <span>Activity Log</span>
                            </a>
                        </li>
                        <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                        <li><a href="settings.php?user_id=<?php echo $user_id; ?>">
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

        <div class="card">
            <canvas id="bargraph">

            </canvas>
        </div>

    </div>

    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById("myDropdown");
            dropdown.classList.toggle("show");
        }

        function changeValue(value) {
            document.getElementById("dropbtn").innerText = value;
            toggleDropdown(); // Hide the dropdown after selecting an option
        }
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

    <script>
        var ctx = document.getElementById("bargraph").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($transaction_type); ?>,
                datasets: [{
                    backgroundcolor: [
                        "#5969ff",
                        "#5945fd",
                        "#25d5f2",
                        "#2ec551",
                        "#ff044e"
                    ],
                    data: <?php echo json_encode($CPinventory); ?>
                }]
            },
            option: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        fontColor: '#71748d',
                        fontFamily: 'Circular Std Book',
                        fontSize: 14,
                    }
                }
            }
        });
    </script>

</body>

</html>