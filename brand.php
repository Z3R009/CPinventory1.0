<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';

if (isset($_POST['submit_brand'])) {

    $brand_name = $_POST['brand_name'];

    $sql = "INSERT into brand (brand_name) 
        values ('$brand_name')";

    if ($connection->query($sql)) {
        // Log the action
        $action_user_id = $_SESSION['user_id'];
        $details = "Brand Added: $brand_name";
        insertLog($action_user_id, $connection->insert_id, $details);

        header('Location: brand.php');
    } else {
        // Handle error
    }
}

if (isset($_POST['submit_type'])) {

    $type = $_POST['type'];

    $sql = "INSERT into type (type) 
        values ('$type')";

    if ($connection->query($sql)) {
        // Log the action
        $action_user_id = $_SESSION['user_id'];
        $details = "Item Type Added: $type";
        insertLog($action_user_id, $connection->insert_id, $details);

        header('Location: brand.php');
    } else {
        // Handle error
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
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/brand.css">
</head>

<style>
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
            <li><a href="manage_users.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li style="background: #e0e0e058;"><a href="brand.php?user_id=<?php echo $user_id; ?>">
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
                <h2>Brand & Item Type</h2>
            </div>

            <button class="img-user-btn" id="show"></button>
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

        <div class="card1">
            <div class="form-container">
                <form method="post">
                    <div class="form-group">
                        <input type="text" id="brand_name" name="brand_name" placeholder="Enter Brand Name"
                            autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit_brand">Add</button>
                    </div>
                </form>
            </div>
        </div>


        <div class="card2">
            <div class="form-container">
                <form method="post">
                    <div class="form-group">
                        <input type="text" id="type" name="type" placeholder="Enter Item Type" autocomplete="off"
                            required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit_type">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table_brand">
            <div class="table-container-brand">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Brand</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM brand order by brand_name ASC ";
                        $result = mysqli_query($connection, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $brand_id = $row['brand_id'];

                                $brand_name = $row['brand_name'];

                                echo ' <tr> 
                                    <td>' . $brand_id . '</td>
                                    <td id="username_' . $brand_id . '" ">' . $brand_name . '</td>
                                    <td>
                                    
                                        <button><a href="delete_brand.php?brand_id=' . $brand_id . '" style="text-decoration: none; color: white;">Delete</a></button>
                                    </td>
                                    </tr> ';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table_type">
            <div class="table-container-type">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Item Type</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM type order by type ASC ";
                        $result = mysqli_query($connection, $sql);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $type_id = $row['type_id'];
                                $type = $row['type'];

                                echo ' <tr> 
                                    <td>' . $type_id . '</td>
                                    <td id="type_' . $type_id . '">' . $type . '</td>
                                    <td>
                                    
                                        <button><a href="delete_brand.php?type_id=' . $type_id . '" style="text-decoration: none; color: white;">Delete</a></button>
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

</body>

</html>