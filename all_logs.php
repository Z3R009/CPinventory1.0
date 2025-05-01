<?php
include 'DBConnection.php';
include 'auth.php';
include 'role_admin.php';


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
    .table-container {
        overflow: auto;
        height: 470px;
    }


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
        width: 300px;
        text-align: center;
    }

    td:nth-child(3),
    th:nth-child(3) {
        width: 300px;
        text-align: center;
    }


    td:nth-child(4),
    th:nth-child(4) {
        width: 500px;
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

    .card2 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        background: #fff;
        border-radius: 10px;
        padding: 10px 2rem;
        margin-bottom: 1rem;
        width: 100%;
    }

    th {
        background-color: #31304D;
        color: white;
    }

    /* modal */

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-content {
        margin: 15% auto;
        padding: 20px;
        background-color: white;
        width: 80%;
        text-align: center;
        border-radius: 10px;
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 20px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
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
            <li><a href="brand.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Brand & Item Type</span>
                </a>
            </li>
            <!-- <li style="background: #e0e0e058;"><a href="all_logs.php?user_id=<?php echo $user_id; ?>">
                    <i class="fas fa-clipboard-list" style="margin-left: 5px;"></i>
                    <span style="margin-left: 5px;">Logs</span>
                </a>
            </li> -->
            <li style="background: #e0e0e058;">
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
                <h2>Logs</h2>
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
        </div>
        <div class="card">
            <div class="table-container">
                <table class="table" border="1">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Date</th>
                            <th scope="col">Executed By</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT logs.*, users.fullname AS username FROM logs LEFT JOIN users ON logs.action_user_id = users.user_id ORDER BY date DESC, time DESC";
                        $result = mysqli_query($connection, $sql);

                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $logs_id = $row['logs_id'];
                                $formatted_date = date('F j, Y', strtotime($row['date']));
                                // Format the time to display in 12-hour format
                                $formatted_time = date('h:i A', strtotime($row['time']));
                                $action_username = $row['username'];
                                $details = $row['details'];

                                echo ' <tr> 
                <td>' . $logs_id . '</td>
                <td class="date-column">' . $formatted_date . ', ' . $formatted_time . '</td>
                <td id="action_user_id_' . $logs_id . '">' . $action_username . '</td>
                <td id="details_' . $logs_id . '">' . $details . '</td>
                </tr> ';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Modal for Viewing Image -->
        <div id="imageModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <img id="modalImage" src="" alt="Suspicious Activity Image" style="width: 100%; height: auto;">
            </div>
        </div>
        <script>
            // Function to show update pop-up with user details
            function showUpdatePopup(user_id) {
                // Fetch user details from table
                var fullname = document.getElementById('fullname_' + user_id).innerHTML;
                var username = document.getElementById('username_' + user_id).innerHTML;
                var password = document.getElementById('password_' + user_id).innerHTML;
                var user_type = document.getElementById('user_type_' + user_id).innerHTML;

                // Set values in the update pop-up
                document.getElementById('update_user_id').value = user_id;
                document.getElementById('update_fullname').value = fullname;
                document.getElementById('update_username').value = username;
                document.getElementById('update_password').value = password;
                document.getElementById('update_user_type').value = user_type;

                // Show the update pop-up
                document.getElementById('updatePopupContainer').classList.add('active');
            }

            // close button update
            document.querySelector('#updatePopupContainer .close-btn').addEventListener('click', function () {
                console.log('Close button clicked');
                document.getElementById('updatePopupContainer').classList.remove('active');
            });

            // clear button for update
            document.querySelector('#updatePopupContainer .clear-btn').addEventListener('click', function () {
                document.getElementById('update_username').value = '';

            });
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
                document.getElementById('fullname').value = '';
                document.getElementById('username').value = '';
                document.getElementById('password').value = '';
                document.getElementById('user_type').value = 'admin';
            });
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

        <script>
            function syncTextFields(value, targetFieldId) {
                document.getElementById(targetFieldId).value = value;
            }
        </script>

        <!-- view image -->
        <script>
            // JavaScript to handle modal functionality
            const modal = document.getElementById("imageModal");
            const modalImage = document.getElementById("modalImage");
            const closeModal = document.querySelector(".close");

            document.querySelectorAll(".view-image-btn").forEach(button => {
                button.addEventListener("click", function () {
                    const imageSrc = this.getAttribute("data-image");
                    modalImage.setAttribute("src", imageSrc);
                    modal.style.display = "block";
                });
            });

            closeModal.addEventListener("click", function () {
                modal.style.display = "none";
            });

            // Close modal when clicking outside the modal content
            window.addEventListener("click", function (event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        </script>

</body>

</html>