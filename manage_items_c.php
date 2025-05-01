<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_staff.php';

?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = mysqli_real_escape_string($connection, $_POST['search']);

    // Modify your SQL query to include the search condition
    $sql = "SELECT * FROM items WHERE item_name LIKE '%$search%'
            OR brand_name LIKE '%$search%'
            OR type LIKE '%$search%'";

    $result = mysqli_query($connection, $sql);

    if ($connection->query($sql)) {
        // Log the action
        $action_user_id = $_SESSION['user_id'];
        $details = "Searched : $search";
        insertLog($action_user_id, $user_id, $details);
    } else {
        // Handle error
    }
} else {
    // If no search was performed, fetch all records
    $sql = "SELECT * FROM items";
    $result = mysqli_query($connection, $sql);
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
    <title>Manage Items</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/cashier.css">
</head>

<style>
    td:nth-child(2),
    th:nth-child(2) {
        width: 150px;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 200px;
    }

    td:nth-child(4),
    th:nth-child(4) {
        width: 500px;
    }

    td:nth-child(5),
    th:nth-child(5) {
        width: 60px;
    }

    td:nth-child(6),
    th:nth-child(6) {
        display: none;
    }

    td:nth-child(7),
    th:nth-child(7) {
        width: 120px;
    }

    /* ----------settings--------- */
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
</style>

<body>
    <div class="sidebar">
        <ul class="menu">
            <br>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
            <li style="background: #e0e0e058;"><a href="manage_items_c.php?user_id=<?php echo $user_id; ?>">
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
                <h2>
                    Items
                </h2>
            </div>
            <div class="forms">
                <form method="post">
                    <div class="search">
                        <input type="text" name="search" id="search" placeholder="Search Items" autocomplete="off">
                        <button type="submit" class="fa fa-search"></button>
                    </div>
                </form>
            </div>

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


        <!-- popup for update -->
        <div class="container" id="updatePopupContainer">
            <button class="close-btn fas fa-times"></button>
            <div class="text">
                Stock-In
            </div>
            <div class="form-container">
                <form method="post" action="stock_in.php">
                    <div class="form-group">
                        <input type="hidden" id="update_id" name="id"
                            value="<?php echo isset($item_id) ? $item_id : ''; ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" id="stock_in_id" name="item_id" required>
                    </div>
                    <div class="form-group">
                        <label for="brand_name">Brand Name</label>
                        <input type="text" id="update_brand_name" name="brand_name" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="item_name">Item Name</label>
                        <input type="text" id="stock_in_name" name="item_name" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="update_quantity" name="quantity" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="buying_price">Buying Price</label>
                        <input type="number" id="update_buying_price" name="buying_price" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label for="selling_price">Selling Price</label>
                        <input type="number" id="update_selling_price" name="selling_price" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="stock_in">Stock-in</button>
                    </div>
                    <div class="form-group">
                        <button type="button" class="clear-btn">Clear</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- end -->

        <div class="card">
            <div class="table-container">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Brand Name</th>
                            <th scope="col">Item Type</th>
                            <th scope="col">Item Name</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Selling Price</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            // Display existing records or search results
                            while ($row = mysqli_fetch_assoc($result)) {
                                $item_id = $row['item_id'];
                                $brand_name = $row['brand_name'];
                                $type = $row['type'];
                                $item_name = $row['item_name'];
                                $quantity = $row['quantity'];
                                $buying_price = $row['buying_price'];
                                $selling_price = $row['selling_price'];

                                echo ' <tr> 
                                    <td>' . $item_id . '</td>
                                    <td id="brand_name_' . $item_id . '">' . $brand_name . '</td>
                                    <td id="type_' . $item_id . '">' . $type . '</td>
                                    <td id="item_name_' . $item_id . '">' . $item_name . '</td>
                                    <td id="quantity_' . $item_id . '">' . $quantity . '</td>
                                    <td id="selling_price_' . $item_id . '">' . $selling_price . '</td>
                                    <td>
                                    <button id="stock-out-button" ><a href="stock_out.php?item_id=' . $item_id . '" style="text-decoration: none; color: white;">Stock-Out</a></button>
                                    </td>
                                    </tr> ';
                            }
                        } else {
                            echo '<tr><td colspan="5">No data found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
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