<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';

// Utility functions
function detectXSS($input)
{
    $pattern = '/(<script.*?>|on[a-z]+=[\'"]?.*?[\'"]?|javascript:|data:|base64,|<.*?(on[a-z]+|style|href|src)=)/i';
    return preg_match($pattern, $input);
}

function detectSQLInjection($input)
{
    $pattern = '/(\b(SELECT|INSERT|DELETE|DROP|UPDATE|UNION|--|#|\/\*|\*\/|xp_)\b|["\']|;)/i';
    return preg_match($pattern, $input);
}

function logSuspiciousActivity($user_id, $input_value, $connection)
{
    $details = "Suspicious activity detected: $input_value";
    insertLog($user_id, 0, $details, $connection);
}

function insertLog($action_user_id, $updated_user_id, $details)
{
    global $connection;

    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iis", $action_user_id, $updated_user_id, $details);

    if (!$stmt->execute()) {
        error_log("Error inserting log: " . $stmt->error);
    }

    $stmt->close();
}

// Fetch the item data
$item_id = intval($_GET['item_id']);
$sql = "SELECT * FROM items WHERE item_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Item not found.");
}

$brand_name = $row['brand_name'];
$type = $row['type'];
$item_name = $row['item_name'];
$buying_price = $row['buying_price'];
$selling_price = $row['selling_price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    $new_brand_name = trim($_POST['brand_name']);
    $new_type = trim($_POST['type']);
    $new_item_name = trim($_POST['item_name']);
    $new_buying_price = trim($_POST['buying_price']);
    $new_selling_price = trim($_POST['selling_price']);

    // Check for scripting attacks
    if (
        detectXSS($new_brand_name) || detectSQLInjection($new_brand_name) ||
        detectXSS($new_type) || detectSQLInjection($new_type) ||
        detectXSS($new_item_name) || detectSQLInjection($new_item_name) ||
        detectXSS($new_buying_price) || detectSQLInjection($new_buying_price) ||
        detectXSS($new_selling_price) || detectSQLInjection($new_selling_price)
    ) {
        logSuspiciousActivity($_SESSION['user_id'], "Scripting attack detected during item update", $connection);
        $_SESSION['error_message'] = "Suspicious activity detected. Update aborted.";
        header('Location: manage_items.php');
        exit();
    }

    // Sanitize input
    $new_brand_name = htmlspecialchars($new_brand_name, ENT_QUOTES, 'UTF-8');
    $new_type = htmlspecialchars($new_type, ENT_QUOTES, 'UTF-8');
    $new_item_name = htmlspecialchars($new_item_name, ENT_QUOTES, 'UTF-8');
    $new_buying_price = floatval($new_buying_price);
    $new_selling_price = floatval($new_selling_price);

    // Prepare update query
    $sql = "UPDATE items SET brand_name = ?, type = ?, item_name = ?, buying_price = ?, selling_price = ? WHERE item_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssdii", $new_brand_name, $new_type, $new_item_name, $new_buying_price, $new_selling_price, $item_id);

    if ($stmt->execute()) {
        // Log updates
        $details = "Item updated: Brand Name='$brand_name' to '$new_brand_name', Type='$type' to '$new_type', Item Name='$item_name' to '$new_item_name', Buying Price='₱$buying_price' to '₱$new_buying_price', Selling Price='₱$selling_price' to '₱$new_selling_price'.";
        insertLog($_SESSION['user_id'], $item_id, $details);
        header('Location: manage_items.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating item. Please try again.";
        logSuspiciousActivity($_SESSION['user_id'], $stmt->error, $connection);
        header('Location: manage_items.php');
        exit();
    }
}
?>


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
    <title>Document</title>
    <link rel="stylesheet" href="css/stock_out.css">
</head>
<style>
    body {
        overflow: auto;
    }

    select[name="brand_name"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        text-align: center;
        margin-bottom: 20px;
    }

    select[name="type"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        text-align: center;
        margin-bottom: 20px;
    }
</style>

<body>

    <div class="form-container">
        <div class="header-title">
            <h2>Update Item</h2>
        </div>
        <div class="form-group">
        </div>
        <form method="post" onsubmit="return validateQuantity();">
            <div class=" form-group">
                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
            </div>
            <label for="brand_name">Brand Name</label>
            <select name="brand_name" id="brand_name">
                <option disabled>Select Brand</option>
                <?php foreach ($rows_brand as $row): ?>
                    <option value="<?php echo $row['brand_name']; ?>" <?php echo ($row['brand_name'] === $brand_name) ? 'selected' : ''; ?>>
                        <?php echo $row['brand_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="type">Item Type</label>
            <select name="type" id="type">
                <option disabled>Select Item Type</option>
                <?php foreach ($rows_type as $row): ?>
                    <option value="<?php echo $row['type']; ?>" <?php echo ($row['type'] === $type) ? 'selected' : ''; ?>>
                        <?php echo $row['type']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-group">
                <label for="item_name">Item Name/Model</label>
                <input type="text" name="item_name" value="<?php echo $item_name; ?>">
            </div>
            <div class="form-group">
                <label for="buying_price">Buying Price</label>
                <input type="number" name="buying_price" id="buying_price" value="<?php echo $buying_price; ?>"
                    autocomplete="off">
            </div>
            <div class="form-group">
                <label for="selling_price">Selling Price</label>
                <input type="number" name="selling_price" id="selling_price" value="<?php echo $selling_price; ?>"
                    autocomplete="off">
            </div>
            <div class="form-group">
                <button type="submit" id="update_item" name="update_item">Update</button>
                <button><a href="manage_items.php?user_id=<?php echo $user_id; ?>"
                        style="text-decoration: none; color: #fff; ">Cancel</a></button>
            </div>
        </form>
    </div>
</body>
<script>
    function calculateTotal() {
        var quantity_stock = parseInt(document.getElementById('quantity_stock').value);
        var quantity_add = parseInt(document.getElementById('quantity_add').value);
        var total = quantity_stock + quantity_add;
        document.getElementById('quantity').value = total;

        // Enable or disable the button based on the conditions
        var stockInButton = document.getElementById('stockInButton');
        var errorMessage = '';

        if (quantity_add === 0) {
            stockInButton.disabled = true;
            stockInButton.style.backgroundColor = "#747264"; // Change button color to red
            errorMessage = 'Quantity should be greater than zero.';
        } else if (quantity_add < 0) {
            stockInButton.disabled = true;
            stockInButton.style.backgroundColor = "#747264"; // Change button color to red
            errorMessage = 'Quantity should not be negative.';
        } else {
            stockInButton.disabled = false;
            stockInButton.style.backgroundColor = "#31304D"; // Change button color to default blue
        }

        document.getElementById('quantityError').innerText = errorMessage; // Display error message
    }

</script>

</html>