<?php
include 'DBConnection.php';

if (isset($_POST['submit'])) {
    $item_name = $_POST['item_name'];

    $checkuid = "SELECT * FROM items ORDER BY id DESC LIMIT 1";
    $checkresult = mysqli_query($connection, $checkuid);
    if (mysqli_num_rows($checkresult) > 0) {
        if ($row = mysqli_fetch_assoc($checkresult)) {
            $uid = $row["item_id"];
            $get_numbers = str_replace("SR", "", $uid);
            $id_increase = $get_numbers + 1;
            $get_string = str_pad($id_increase, 5, 0, STR_PAD_LEFT);
            $id = "SR" . $get_string;
            $brand_name = $_POST['brand_name'];
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $buying_price = $_POST['buying_price'];
            $selling_price = $_POST['selling_price'];
            $transaction_type = $_POST['transaction_type'];
            $executed_by = $_POST['executed_by'];

            $insert_qry = "INSERT INTO items (item_id, brand_name, item_name, quantity,  buying_price, selling_price, transaction_type, executed_by )
            values ('$item_id', '$brand_name', '$item_name', '$quantity', '$buying_price', '$selling_price', '$transaction_type', '$executed_by')";
            $result = mysqli_query($connection, $insert_qry);
            if ($result) {
                echo "added" . '<br>' . "reg num: " . $id;
            } else {
                echo "error";
            }
        }
    } else {

        $id = "SR00001";
        $insert_qry = "INSERT INTO items (item_id, brand_name, item_name, quantity,  buying_price, selling_price, transaction_type, executed_by )
        values ('$item_id', '$brand_name', '$item_name', '$quantity', '$buying_price', '$selling_price', '$transaction_type', '$executed_by')";
        $result = mysqli_query($connection, $insert_qry);

        if ($resut) {
            echo "added" . '<br>' . "reg num: " . $id;
        } else {
            echo "error";
        }
    }
}