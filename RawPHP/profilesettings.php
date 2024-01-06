<?php
include_once "header.php";

include "classes/dbh.classes.php";
include "classes/profileinfo.classes.php";
include "classes/profileinfo-view.classes.php";
$profileInfo = new ProfileInfoView();
?>

<section class="profile">
    <div class="profile-bg">
        <div class="wrapper">
            <div class="profile-settings">
                <h3>PROFILE SETTINGS</h3>
                <p>Change your profile picture here!</p>

                <div class="profile-change-wrapper">
                    <?php
                    //trying to change profile picture here! - but first I'm trying to display a picture next to my form - which is not working. 
                    include_once 'includes/dbh.inc.php';


                    $id = $_SESSION['userid'];
                    $sqlImg = "SELECT * FROM profileimg WHERE UserID='$id'";
                    $resultImg = mysqli_query($conn, $sqlImg);
                    while ($rowImg = mysqli_fetch_assoc($resultImg)) {
                        echo '<div class="user-container">';
                        if ($rowImg['status'] == 0) {
                            echo "<img src='img/artworks/" . $rowImg['NewImgName'] . "'>";
                        } else {

                            //this is the default image I want to display if the user has not uploaded an image already
                            echo "<img src ='img/artworks/Default.jpg'>";
                        }
                        echo "</div>";
                    }


                    if (isset($_SESSION['userid'])) {
                        echo '  <form class="browse" action="upload.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="file">
                    <button type="submit" name="submit">UPLOAD</button>
                </form>';
                    } else {
                        echo "You are not logged in!";
                    }

                    ?>


                </div>
                <!-- everything below works -->
                <p>Change your about section here!</p>
                <form class="about-form" action="includes/profileinfo.inc.php" method="post">
                    <div class="input-wrapper2">
                        <textarea name="about" rows="10" cols="30" class="input-text2" placeholder="Profile about section..." value=""><?php $profileInfo->fetchAbout($_SESSION["userid"]); ?></textarea>
                    </div>
                    <br><br>
                    <p>Change your profile page intro here!</p>
                    <br>
                    <div class="input-wrapper2">
                        <input type="text" name="introtitle" placeholder="Profile title..." class="input-text2" value="<?php $profileInfo->fetchTitle($_SESSION["userid"]); ?>">
                    </div>
                    <div class="input-wrapper2">
                        <textarea name="introtext" rows="10" cols="30" class="input-text2" placeholder="Profile introduction..."><?php $profileInfo->fetchText($_SESSION["userid"]); ?></textarea>
                    </div>
                    <button type="submit" name="submit">SAVE</button>
                </form>
                <br>

                <!-- I also want to be able to change this info for the user - note to myself -->
                <p>Change your bio info here!</p>
                <br>
                <form class="about-form" action="" method="post">
                    <div class="input-wrapper2">
                        <input type="text" name="firstName" placeholder="First Name" class="input-text2">
                    </div><br>
                    <div class="input-wrapper2">
                        <input type="text" name="lastName" placeholder="Last Name" class="input-text2">
                    </div><br>
                    <div class="input-wrapper2">
                        <input type="date" name="dob" placeholder="Date of Birth" class="input-text2">
                    </div>
                    <button type="submit" name="submitUserInfo">SAVE</button>

                </form>
            </div>
        </div>
    </div>
</section>

</body>

</html>