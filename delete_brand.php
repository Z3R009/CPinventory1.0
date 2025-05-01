<?php
include 'DBConnection.php';
if (isset($_GET['brand_id'])) {
    $brand_id = $_GET['brand_id'];

    $sql = "SELECT brand_name FROM brand WHERE brand_id = $brand_id";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $brand_name = $row['brand_name'];

    // Check if user confirmed the deletion
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $sql = "DELETE FROM brand where brand_id=$brand_id";
        $result = mysqli_query($connection, $sql);

        if ($connection->query($sql)) {
            // Log the action
            $action_user_id = $_SESSION['user_id'];
            $details = "Brand Deleted: $brand_name";
            insertLog($action_user_id, $user_id, $details);

            header('Location: brand.php');
        } else {
            // Handle error
        }
        exit();
    } else {
        echo "<script>
                if(confirm('Are you sure you want to delete this Brand?')) {
                    window.location.href = 'delete_brand.php?brand_id=$brand_id&confirm=yes';
                } else {
                    window.location.href = 'brand.php';
                }
              </script>";
    }
}

if (isset($_GET['type_id'])) {
    $type_id = $_GET['type_id'];

    $sql = "SELECT type FROM type WHERE type_id = $type_id";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $type = $row['type'];

    // Check if user confirmed the deletion
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $sql = "DELETE FROM type where type_id=$type_id";
        $result = mysqli_query($connection, $sql);

        if ($connection->query($sql)) {
            // Log the action
            $action_user_id = $_SESSION['user_id'];
            $details = "Item Type Deleted: $type";
            insertLog($action_user_id, $user_id, $details);

            header('Location: brand.php');
        } else {
            // Handle error
        }
        exit();
    } else {
        echo "<script>
                if(confirm('Are you sure you want to delete this Item Type?')) {
                    window.location.href = 'delete_brand.php?type_id=$type_id&confirm=yes';
                } else {
                    window.location.href = 'brand.php';
                }
              </script>";
    }
}

function insertLog($action_user_id, $updated_user_id, $details)
{
    global $connection;

    $sql = "INSERT INTO logs (action_user_id, updated_user_id, details) 
            VALUES ('$action_user_id', '$updated_user_id', '$details')";

    $connection->query($sql);
}
