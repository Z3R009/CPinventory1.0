<?php

include 'DBConnection.php';
include 'auth.php';
include 'role_staff.php';

$error_message = "";

if (isset($_POST['stock_out'])) {

    $item_id = $_POST['item_id'];
    $brand_name = $_POST['brand_name'];
    $type = $_POST['type'];
    $item_name = $_POST['item_name'];
    $quantity_add = $_POST['quantity_add'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $transaction_type = $_POST['transaction_type'];
    $executed_by = $_POST['executed_by'];
    $user_type = $_POST['user_type'];



    $sql = "INSERT into reports (item_id, brand_name, type, item_name, quantity_add,  buying_price, selling_price, transaction_type, executed_by, user_type) 
        values ('$item_id', '$brand_name', '$type', '$item_name', '$quantity_add', '$buying_price', '$selling_price', '$transaction_type', '$executed_by', '$user_type')";

    if ($connection->query($sql)) {
        // Log the action
        $action_user_id = $_SESSION['user_id'];
        $details = "Stock-Out : $item_name, Qty: $quantity_add";
        insertLog($action_user_id, $user_id, $details);

        header('Location: cashier.php?user_id=<?php echo $user_id; ?>');
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

<?php
$item_id = $_GET['item_id'];
$sql = "SELECT * FROM items where item_id=$item_id";
$result = mysqli_query($connection, $sql);
$row = mysqli_fetch_assoc($result);
$brand_name = $row['brand_name'];
$type = $row['type'];
$item_name = $row['item_name'];
$quantity = $row['quantity'];
$buying_price = $row['buying_price'];
$selling_price = $row['selling_price'];
$transaction_type = $row['transaction_type'];
$executed_by = $row['executed_by'];

if (isset($_POST['stock_out'])) {

    $brand_name = $_POST['brand_name'];
    $type = $_POST['type'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $transaction_type = $_POST['transaction_type'];
    $executed_by = $_POST['executed_by'];

    $sql = "UPDATE items set item_id=$item_id, quantity='$quantity' 
   where item_id=$item_id";
    $result = mysqli_query($connection, $sql);
    if ($connection->query($sql)) {
        header('Location: manage_items_c.php?user_id=<?php echo $user_id; ?>');
        exit();
    } else {
    }
}
?>

<?php
// Connect to the database
$pdo = new PDO('mysql:host=localhost;dbname=cpinventory', 'root', '');

$stmt_users = $pdo->prepare("SELECT user_id, username FROM users");
$stmt_users->execute();
$rows_users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- <?php
$item_id = $_GET['item_id'];
$sql = "SELECT * FROM items where item_id=$item_id";
$result = mysqli_query($connection, $sql);
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];
$item_id = $row['item_id'];

?> -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/stock_out.css">
</head>
<style>
    body {
        overflow: auto;
    }
</style>

<body>

    <div class="form-container">
        <div class="header-title">
            <h2>Stock-Out</h2>
        </div>
        <div class="form-group">
        </div>
        <form method="post" onsubmit="return validateQuantity();">
            <div class=" form-group">
                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
            </div>
            <div class="form-group">
                <label for="brand_name">Brand Name</label>
                <input type="text" name="brand_name" value="<?php echo $brand_name; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <input type="text" name="type" value="<?php echo $type; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="item_name">Item Name/Model</label>
                <input type="text" name="item_name" value="<?php echo $item_name; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="quantity">Stock</label>
                <input type="number" name="quantitystock" id="quantity_stock" value="<?php echo $quantity; ?>"
                    autocomplete="off">
            </div>
            <div class="form-group">
                <label for="quantity_add">Quantity</label>
                <input type="number" name="quantity_add" id="quantity_add" autocomplete="off" required
                    oninput="calculateTotal()">
                <span id="quantityError" style="color: red;"></span>
            </div>
            <div class="form-group">
                <input type="hidden" name="quantity" id="quantity" autocomplete="off">
            </div>
            <div class="form-group">
                <input type="hidden" name="buying_price" id="buying_price" value="<?php echo $buying_price; ?>"
                    autocomplete="off">
            </div>
            <div class="form-group">
                <input type="hidden" name="selling_price" id="selling_price" value="<?php echo $selling_price; ?>"
                    autocomplete="off">
            </div>
            <div class="form-group">
                <input type="hidden" id="transaction_type" name="transaction_type" value="Stock-Out" required>
            </div>
            <div class="form-group">
                <input type="hidden" id="executed_by" name="executed_by" value="<?php echo $_SESSION['fullname']; ?>"
                    required>
            </div>
            <div class="form-group">
                <input type="hidden" id="user_type" name="user_type" value="<?php echo $_SESSION['user_type']; ?>"
                    required>
            </div>
            <div class="form-group">
                <button type="submit" id="stockOutButton" name="stock_out">Stock-Out</button>
                <button
                    onclick="window.location.href='manage_items_c.php?user_id=<?php echo $user_id; ?>'">Cancel</button>
            </div>
        </form>
    </div>
    <?php
    //     } else {
//         echo "<h3>users not found</h3>";
//     }
// }
    ?>
</body>

<script>
    function calculateTotal() {
        var quantity_stock = parseInt(document.getElementById('quantity_stock').value);
        var quantity_add = parseInt(document.getElementById('quantity_add').value);
        var total = quantity_stock - quantity_add;
        document.getElementById('quantity').value = total;

        // Enable or disable the button based on the conditions
        var stockOutButton = document.getElementById('stockOutButton');
        var errorMessage = '';

        if (quantity_add > quantity_stock) {
            stockOutButton.disabled = true;
            stockOutButton.style.backgroundColor = "#747264"; // Change button color to red
            errorMessage = 'Stock is not enough.';
        } else if (quantity_add === 0) {
            stockOutButton.disabled = true;
            stockOutButton.style.backgroundColor = "#747264"; // Change button color to red
            errorMessage = 'Quantity should be greater than zero.';
        } else if (quantity_add < 0) {
            stockOutButton.disabled = true;
            stockOutButton.style.backgroundColor = "#747264"; // Change button color to red
            errorMessage = 'Quantity should not be negative.';
        } else {
            stockOutButton.disabled = false;
            stockOutButton.style.backgroundColor = "#31304D"; // Change button color to default blue
        }

        document.getElementById('quantityError').innerText = errorMessage; // Display error message
    }

    function validateQuantity() {
        var quantityStock = parseInt(document.getElementById('quantity_stock').value);
        var quantityAdd = parseInt(document.getElementById('quantity_add').value);

        if (quantityAdd > quantityStock || quantityAdd === 0 || quantityAdd < 0) {
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }
</script>





</html>