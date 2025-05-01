<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';


if (isset($_SESSION['error_message'])) {
    // Style for centered floating error message
    echo "
    <style>
        #errorMessage {
            position: fixed;
            top: 5%;
            left: 50%;
            transform: translate(-50%, -50%); /* Center the message */
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

    // Clear the session message after it's displayed
    unset($_SESSION['error_message']);

    // Add a delay to remove the message after a few seconds
    echo "<script>
        setTimeout(function() {
            document.getElementById('errorMessage').style.opacity = 0;
        }, 3000);  // Message disappears after 3 seconds
    </script>";
}

// Function to detect XSS attacks
function detectXSS($input)
{
    $pattern = '/(<script.*?>|on[a-z]+=[\'"]?.*?[\'"]?|javascript:|data:|base64,|<.*?(on[a-z]+|style|href|src)=)/i';
    return preg_match($pattern, $input);
}

// Function to detect SQL Injection attempts
function detectSQLInjection($input)
{
    $pattern = '/(\b(SELECT|INSERT|DELETE|DROP|UPDATE|UNION|--|#|\/\*|\*\/|xp_)\b|["\']|;)/i';
    return preg_match($pattern, $input);
}

// Function to log suspicious activity
function logSuspiciousActivity($user_id, $input_value)
{
    global $connection;
    $details = "Suspicious activity detected: $input_value";
    insertLog($user_id, 0, $details);
}

// Function to insert logs
function insertLog($action_user_id, $updated_user_id, $details)
{
    global $connection;
    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iis", $action_user_id, $updated_user_id, $details);

    if (!$stmt->execute()) {
        echo "Error inserting log: " . $connection->error;
    }
    $stmt->close();
}

// Search functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = $_POST['search'];

    // Check for XSS or SQL Injection
    if (detectXSS($search) || detectSQLInjection($search)) {
        $user_id = $_SESSION['user_id'];
        logSuspiciousActivity($user_id, "Invalid search input: $search");
        $_SESSION['error_message'] = "Suspicious activity detected. The search was not performed.";
        header('Location: manage_items.php');
        exit();
    }

    // Sanitize and perform search
    $search = mysqli_real_escape_string($connection, $search);
    $sql = "SELECT * FROM items WHERE item_name LIKE '%$search%' 
            OR brand_name LIKE '%$search%' 
            OR type LIKE '%$search%'";
    $result = mysqli_query($connection, $sql);

    if ($result) {
        // Log the search action
        $action_user_id = $_SESSION['user_id'];
        $details = "Searched: $search";
        insertLog($action_user_id, 0, $details);
    } else {
        echo "Error: " . $connection->error;
    }
} else {
    // Default fetch if no search is performed
    $sql = "SELECT * FROM items";
    $result = mysqli_query($connection, $sql);
}

// Add or update item
if (isset($_POST['submit'])) {
    $item_id = $_POST['item_id'];
    $brand_name = $_POST['brand_name'];
    $type = $_POST['type'];
    $item_name = htmlspecialchars($_POST['item_name'], ENT_QUOTES, 'UTF-8');
    $quantity = $_POST['quantity'];
    $quantity_add = isset($_POST['quantity_add']) ? $_POST['quantity_add'] : 0;
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $transaction_type = $_POST['transaction_type'];
    $executed_by = $_POST['executed_by'];
    $user_type = $_POST['user_type'];

    // Check for XSS or SQL Injection
    if (
        detectXSS($item_name) || detectSQLInjection($item_name) ||
        detectXSS($brand_name) || detectSQLInjection($brand_name) ||
        detectXSS($type) || detectSQLInjection($type)
    ) {
        $user_id = $_SESSION['user_id'];
        logSuspiciousActivity($user_id, "Invalid input detected while adding/updating item");
        $_SESSION['error_message'] = "Suspicious activity detected. The operation was not completed.";
        header('Location: manage_items.php');
        exit();
    }

    // Insert data into items table
    $sql_items = "INSERT INTO items (item_id, brand_name, type, item_name, quantity, buying_price, selling_price, transaction_type, executed_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_items = $connection->prepare($sql_items);
    $stmt_items->bind_param("ssssddsss", $item_id, $brand_name, $type, $item_name, $quantity, $buying_price, $selling_price, $transaction_type, $executed_by);

    // Insert data into reports table
    $sql_reports = "INSERT INTO reports (item_id, brand_name, type, item_name, quantity_add, buying_price, selling_price, transaction_type, executed_by, user_type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_reports = $connection->prepare($sql_reports);
    $stmt_reports->bind_param("ssssddssss", $item_id, $brand_name, $type, $item_name, $quantity_add, $buying_price, $selling_price, $transaction_type, $executed_by, $user_type);

    if ($stmt_items->execute() && $stmt_reports->execute()) {
        // Log the action
        $action_user_id = $_SESSION['user_id'];
        $details = "Item added: $item_name";
        insertLog($action_user_id, $connection->insert_id, $details);
        header('Location: manage_items.php');
    } else {
        echo "Error: " . $connection->error;
    }

    $stmt_items->close();
    $stmt_reports->close();
}

// Display error messages
if (isset($_SESSION['error_message'])) {
    echo "<div id='errorMessage'>" . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . "</div>";
    unset($_SESSION['error_message']);
}
?>



<!-- select brand -->
<?php
// Connect to the database
$pdo = new PDO('mysql:host=localhost;dbname=cpinventory', 'root', '');

// Fetch data for brands
$stmt_brand = $pdo->prepare("SELECT brand_id, brand_name FROM brand order by brand_name ASC");
$stmt_brand->execute();
$rows_brand = $stmt_brand->fetchAll(PDO::FETCH_ASSOC);

// Fetch data for types
$stmt_type = $pdo->prepare("SELECT type_id, type FROM type order by type ASC");
$stmt_type->execute();
$rows_type = $stmt_type->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/manage_items.css">
</head>

<style>
    td:nth-child(2),
    th:nth-child(2) {
        width: 150px;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 150px;
    }

    td:nth-child(4),
    th:nth-child(4) {
        width: 300px;
    }

    td:nth-child(5),
    th:nth-child(5) {
        width: 60px;
    }

    td:nth-child(6),
    th:nth-child(6) {
        width: 250px;
    }

    td:nth-child(7),
    th:nth-child(7) {
        width: 120px;
    }

    /* ----------settings---------- */
    .img-user-btn {
        position: relative;
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
        transform: translate(2050%, -150%);
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

    /* -------table-------- */
    .add-user-btn {
        background-color: #31304D;
        border-radius: 12px;
        border: 0;
        color: #eee;
        cursor: pointer;
        font-size: 18px;
        height: 50px;
        outline: 0;
        padding: 10px 20px;
        text-align: center;
        margin-right: 100px;
        transform: translate(65%, 0%);
    }

    .add-user-btn:hover {
        background-color: #0C359E;
    }

    .table-container {
        height: 400px;
        overflow: auto;
    }

    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        margin: 0 auto;
        width: 100%;
    }

    td,
    th {
        border: 1px solid #31304D;
        text-align: left;
        padding: 8px;
    }

    .table-container {
        height: 400px;

    }

    .card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 8px 20px 0 rgba(31, 38, 135, 0.37);
        padding: 10px 2rem;
        margin-bottom: 1rem;
        width: 100%;
        margin-top: -50px;
        height: 505px;
    }

    /* ----------search----------- */
    .search {
        background: rgb(237, 237, 237);
        background-color: white;
        border-radius: 15px;
        color: black;
        display: flex;
        align-items: center;
        margin: 10px 0px 10px 0px;
        margin-left: 200px;
    }

    .search input {
        background: transparent;
        margin: 0px 0px 0px 10px;
        width: 350px;
        transform: translate(-60%, 0%);
    }

    .search i {
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.5s ease-in-out;
    }

    .search button {
        height: 40px;
        width: 40px;
        background-color: #31304D;
        color: #fff;
        padding: 0px 0px 0px 2px;
        margin: 0px 0px 0px 10px;
        transform: translate(-530%, 0%);
    }

    .search i:hover {
        transform: scale(1.2);
    }
</style>

<body>
    <div class="sidebar">
        <ul class="menu">
            <br>
            <hr style="border-top: 5px solid #fff; margin: 10px 0px 10px 0px;">
            <li style="background: #e0e0e058;"><a href="manage_items.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Manage Items</span>
                </a>
            </li>
            <li><a href="manage_users.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li><a href="brand.php?user_id=<?php echo $user_id; ?>">
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
                <h2>Manage Items</h2>
            </div>

        </div>
        <button class="img-user-btn" id="showImg"></button>
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

        <div class="card">

            <div class="forms">
                <form method="post">
                    <div class="search">
                        <input type="text" name="search" id="search" placeholder="Search Items" autocomplete="off">
                        <button type="submit" class="fa fa-search"></button>
                    </div>
                </form>
            </div>

            <!-- popup for add_user -->

            <button class="add-user-btn" id="show">Add Items</button>
            <div class="container" id="popupContainer">
                <button class="close-btn fas fa-times"></button>
                <div class="text">
                    Add Items
                </div>
                <div class="form-container">
                    <form method="post">
                        <div class="form-group">
                            <input type="hidden" id="item_id" name="item_id" value="<?php
                            echo rand(100000000, 999999999);
                            ?>" required autocomplete="off">
                        </div>
                        <select name="brand_name" id="brand_name">
                            <option selected disabled>Select Brand</option>
                            <?php foreach ($rows_brand as $row): ?>
                                <option value="<?php echo $row['brand_name']; ?>">
                                    <?php echo $row['brand_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="type" id="type">
                            <option selected disabled>Select Type</option>
                            <?php foreach ($rows_type as $row): ?>
                                <option value="<?php echo $row['type']; ?>">
                                    <?php echo $row['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-group">
                            <input type="text" id="item_name" name="item_name" placeholder="Enter Item Name"
                                autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <input type="number" id="quantity" oninput="syncTextFields(this.value, 'quantity_add')"
                                name="quantity" placeholder="Enter Quantity (by pieces)" autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="quantity_add" name="quantity_add" autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <input type="number" id="buying_price" name="buying_price" placeholder="Enter Buying Price"
                                autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <input type="number" id="selling_price" name="selling_price"
                                placeholder="Enter Selling Price" autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="transaction_type" name="transaction_type" value="Stock-In"
                                required>
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="executed_by" name="executed_by"
                                value="<?php echo $_SESSION['fullname']; ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="user_type" name="user_type"
                                value="<?php echo $_SESSION['user_type']; ?>" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="submit">Add</button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="clear-btn">Clear</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- end -->

            <div class="table-container">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Brand Name</th>
                            <th scope="col">Item Type</th>
                            <th scope="col">Item Name/Model</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Price</th>
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

                                echo '<tr> 
                    <td>' . $item_id . '</td>
                    <td id="brand_name_' . $item_id . '">' . htmlspecialchars($brand_name) . '</td>
                    <td id="type_' . $item_id . '">' . htmlspecialchars($type) . '</td>
                    <td id="item_name_' . $item_id . '">' . htmlspecialchars($item_name) . '</td>
                    <td id="quantity_' . $item_id . '">' . htmlspecialchars($quantity) . '</td>
                    <td>
                    <span id="buying_price_' . $item_id . '">' . 'Buying Price:&nbsp' . '₱&nbsp' . htmlspecialchars($buying_price) . '</span><br>
                    <span id="selling_price_' . $item_id . '">' . 'Selling Price:&nbsp' . '₱&nbsp' . htmlspecialchars($selling_price) . '</span>
                    </td>
                    <td>
                    <div class="custom-select-actions">
                        <select onchange="window.location.href=this.value;">
                                <option value="">Actions</option>
                                <option value="stock_in.php?item_id=' . $item_id . '">Stock-In</option>
                                <option value="update_item.php?item_id=' . $item_id . '">Update</option>
                                <option value="delete_item.php?item_id=' . $item_id . '">Delete</option>
                         </select>
                     </div>
                    </td>
                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">No data found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // add user
        document.getElementById('show').addEventListener('click', function () {
            console.log('Add user button clicked');
            document.getElementById('popupContainer').classList.add('active');
        });

        // close button add_user
        document.querySelector('.container .close-btn').addEventListener('click', function () {
            console.log('Close button clicked');
            document.getElementById('popupContainer').classList.remove('active');
        });

        // clear button for add_user
        document.querySelector('.clear-btn').addEventListener('click', function () {
            document.getElementById('item_name').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('selling_price').value = '';
            document.getElementById('buying_price').value = '';

        });


    </script>

    <script>
        fetch('your_endpoint_to_fetch_data_from_database')
            .then(response => response.json())
            .then(data => {
                const selectElement = document.getElementById('brand_name');
                data.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.id; // Assuming option.id contains the value
                    optionElement.textContent = option.brand_name; // Assuming option.name contains the label
                    selectElement.appendChild(optionElement);
                });
            })
            .catch(error => console.error('Error fetching data:', error));
    </script>

    <script>
        function syncTextFields(value, targetFieldId) {
            document.getElementById(targetFieldId).value = value;
        }
    </script>


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