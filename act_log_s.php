<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_staff.php';

$fullname = $_SESSION['fullname'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/fontawesome-free-5.15.4-web/css/all.min.css">
    <link rel="stylesheet" href="css/logs.css">
</head>

<style>
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
        margin-left: 650px;
    }

    td:nth-child(1),
    th:nth-child(1) {
        display: none;
    }

    td:nth-child(2),
    th:nth-child(2) {
        width: 500px;
        text-align: center;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 800px;
        text-align: center;
    }

    .text {
        font-size: 30px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 20px;
        color: #31304D;
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
                <h2>Log</h2>
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
        <div class="card">
            <div class="table-container">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fullname = $_SESSION['fullname'];

                        $stmt = $connection->prepare("SELECT logs.logs_id, logs.date, logs.time, users.fullname, logs.details 
                        FROM logs 
                        JOIN users ON logs.action_user_id = users.user_id 
                        WHERE users.fullname = ? order by date DESC, time DESC");
                        $stmt->bind_param("s", $fullname);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $logs_id = $row['logs_id'];
                                $formatted_date = date('F j, Y', strtotime($row['date']));
                                // Format the time to display in 12-hour format
                                $formatted_time = date('h:i A', strtotime($row['time']));
                                $details = $row['details'];

                                echo ' <tr> 
                                    <td>' . $logs_id . '</td>
                                    <td class="date-column">' . $formatted_date . ', ' . $formatted_time . '</td>
                                    <td id="details_' . $logs_id . '">' . $details . '</td>
                                    </tr> ';
                            }
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

    <script>
        function syncTextFields(value, targetFieldId) {
            document.getElementById(targetFieldId).value = value;
        }
    </script>

</body>

</html>