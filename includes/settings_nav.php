<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<style>
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
        transform: translate(-9%, -10%);
    }


    .containerz {
        display: none;
        position: fixed;
        top: 16%;
        right: 0;
        transform: translate(-9%, -10%);
        background: #fff;
        width: 190px;
        height: 180px;
        padding: 30px;
        padding-top: 20px;
        border-radius: 12px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
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

    <button class="img-user-btn" id="show"></button>
    <div class="containerz" id="popupContainerz">
        <div class="form-containerz">
            <ul style="text-align: center;">
                <span>
                    <?php echo $_SESSION['fullname']; ?>
                </span>
                <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                <li><a href="act_log.php?user_id=<?php echo $user_id; ?>">
                        <span>Activity Log</span>
                    </a>
                </li>
                <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                <li><a href="settings.php?user_id=<?php echo $user_id; ?>">
                        <span>Settings</span>
                    </a>
                </li>
                <hr style="border-top: 1px solid #000; margin: 10px 0px 10px 0px; ">
                <li><a href="logout.php">
                        <span>Log Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- -------popup for sett&log-------- -->
    <script>
        // Function to close popup when clicking outside of it
        function closePopupOutside(event) {
            const popupContainerz = document.getElementById('popupContainerz');
            const addButton = document.getElementById('show');

            if (!popupContainerz.contains(event.target) && event.target !== addButton) {
                console.log('Clicked outside the popup');
                popupContainerz.classList.remove('active');
            }
        }

        // Add event listener to show popup
        document.getElementById('show').addEventListener('click', function () {
            console.log('Add user button clicked');
            document.getElementById('popupContainerz').classList.add('active');
        });

        // Add event listener to close popup when clicking outside of it
        document.addEventListener('click', closePopupOutside);
    </script>
</body>

</html>