<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_staff.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$executed_by = $_SESSION['fullname'] ?? null;

if (isset($_GET['month'])) {
    $selected_month = $_GET['month'];
    if ($selected_month != '0') {
        $sql = "SELECT * FROM reports WHERE transaction_type = 'Stock-Out' AND MONTH(date) = $selected_month AND executed_by = '$executed_by' ORDER BY date DESC";
    } else {
        $sql = "SELECT * FROM reports WHERE transaction_type = 'Stock-Out' AND executed_by = '$executed_by' ORDER BY date DESC";
    }
} else {
    $sql = "SELECT * FROM reports WHERE transaction_type = 'Stock-Out' AND executed_by = '$executed_by' ORDER BY date DESC";
}

$result = mysqli_query($connection, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out Reports</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/stock_in_reports.css">
</head>

<style>
    td:nth-child(1),
    th:nth-child(1) {
        display: none;
    }

    td:nth-child(2),
    th:nth-child(2) {
        width: 250px;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 200px;
    }

    td:nth-child(4),
    th:nth-child(4) {
        width: 200px;
    }

    td:nth-child(5),
    th:nth-child(5) {
        width: 300px;
    }

    td:nth-child(6),
    th:nth-child(6) {
        width: 80px;
    }

    td:nth-child(7),
    th:nth-child(7) {
        display: none;
    }

    td:nth-child(8),
    th:nth-child(8) {
        display: none;
    }

    .table-container {
        overflow: auto;
        max-height: 470px;
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

    /* ------select month-------- */
    .custom-select-actions {
        position: relative;
        display: inline-block;
        font-family: Arial, sans-serif;
        background-color: #fff;
        border: 1px solid #31304D;
        border-radius: 3px;
        padding: 5px 10px;
        cursor: pointer;
        color: #31304D;
        margin-left: 600px;
    }

    .custom-select-actions select {
        padding: 10px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        font-family: Arial, sans-serif;
        background-color: #fff;
        /* Background color */
        border: none;
        border-radius: 3px;
        /* Border radius */
        padding-right: 25px;
        cursor: pointer;
        color: #31304D;
        /* Text color */
        outline: none;
        /* Remove default outline */
        border: 1px solid transparent;
        /* Hide border */
    }


    .custom-select-actions::after {
        content: '\25BC';
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        pointer-events: none;
        color: #31304D;
        /* Arrow color */
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
            <li style="background: #e0e0e058;"><a href="stock_out_reports_c.php?user_id=<?php echo $user_id; ?>"> <i
                        class="fas fa-book"></i>
                    <span>Stock-Out Reports</span>
                </a>
            </li>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
        </ul>
    </div>

    <div class="main-content">
        <div class="header-wrapper">
            <div class="header-title">
                <h2>Stock-Out Reports</h2>
            </div>


            <!-- select month -->
            <!-- <div class="form-group">
                <div class="custom-select-actions">
                    <select id="monthSelect" onchange="filterByMonth()">
                        <option value="0" <?php if (!isset($_GET['month']) || $_GET['month'] == '0')
                            echo 'selected'; ?>>
                            Select Month</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
            </div> -->

            <button class="img-user-btn" id="showImg"></button>
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



        <div class="card">
            <div class="table-container">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Date Added</th>
                            <th scope="col">Brand Name</th>
                            <th scope="col">Item Type</th>
                            <th scope="col">Item Name/Model</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Price</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $item_id = $row['item_id'];
                                $formatted_date = date('F j, Y', strtotime($row['date']));
                                $brand_name = $row['brand_name'];
                                $type = $row['type'];
                                $item_name = $row['item_name'];
                                $quantity_add = $row['quantity_add'];
                                $buying_price = $row['buying_price'];
                                $selling_price = $row['selling_price'];
                                $transaction_type = $row['transaction_type'];
                                $executed_by = $row['executed_by'];
                                $user_type = $row['user_type'];

                                echo ' <tr> 
                <td>' . $item_id . '</td>
                <td class="date-column">' . $formatted_date . '</td>
                <td id="brand_name_' . $item_id . '">' . $brand_name . '</td>
                <td id="type_' . $item_id . '">' . $type . '</td>
                <td id="item_name_' . $item_id . '">' . $item_name . '</td>
                <td id="quantity_add_' . $item_id . '">' . $quantity_add . '</td>
                <td>
                <span id="buying_price_' . $item_id . '">' . 'Buying :&nbsp' . '₱&nbsp' . $buying_price . '</span><br>
                <span id="selling_price_' . $item_id . '">' . 'Selling :&nbsp' . '₱&nbsp' . $selling_price . '</span>
                </td>
                <td>
                <span style="margin: 15px 0px 5px 0px; id="buying_price_' . $item_id . '">' . 'Transaction Type:&nbsp' . $transaction_type . '</span><br>
                <span style="margin: 5px 0px 5px 0px; id="selling_price_' . $item_id . '">' . 'Executed By:&nbsp' . $executed_by . '</span><br>
                <span style="margin: 5px 0px 5px 0px; id="selling_price_' . $item_id . '">' . 'Role:&nbsp' . $user_type . '</span>
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

    <!-- -------------------select month-------------------------- -->
    <script>
        function filterByMonth() {
            const selectedMonth = document.getElementById('monthSelect').value;
            if (selectedMonth != '0') {
                window.location.href = `stock_out_reports_c.php?user_id=<?php echo $user_id; ?>&month=${selectedMonth}`;
            } else {
                window.location.href = `stock_out_reports_c.php?user_id=<?php echo $user_id; ?>`;
            }
        }
    </script>
</body>

</html>