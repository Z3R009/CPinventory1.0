<?php
include 'DBConnection.php';

$sql = "SELECT * FROM reports";
$result = mysqli_query($connection, $sql);
$chart_data = "";
while ($row = mysqli_fetch_array($result)) {
    $date[] = $row['date'];
    $transaction_type = $row['transaction_type'];
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div class="row">
        <div class="card">
            <div class="card-header bg">
                <h1></h1>
                <div class="card-body">
                    <canvas id="bargraph"></canvas>
                </div>
            </div>
        </div>
    </div>
</body>

</html>