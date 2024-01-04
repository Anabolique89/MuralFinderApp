<?php

include_once "header.php";


include "classes/dbh.classes.php";
include "classes/profileinfo.classes.php";
include "classes/profileinfo-view.classes.php";

$profileInfo = new ProfileInfoView();
?>

</style>
<section class="profile">
    <div class="profile-bg">
        <div class="wrapper">
            <div class="profile-info">
                <div class="profile-info-img">
                    <?php
                    include_once 'includes/dbh.inc.php';
                    $id = $_SESSION['userid'];
                    $sql = "SELECT * FROM profileimg WHERE UserID='$id'";
                    $stmt = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        echo "SQL statement failed";
                    } else {
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '   <a href="#">
                            <img class="profile-info-img" style="background-image: url(img/artworks/' . $row["NewImgName"] . ');">
                        </a> ';
                        }
                    }
                    ?>

                </div>
                <div class="profile-info-about">
                    <div class="together">

                        <p class="username-name"> <?php

                                                    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
                                                        echo $_SESSION["username"];
                                                    }

                                                    ?></p>
                        <a href="profilesettings.php" class="follow-btn follow">FOLLOW</a>
                    </div>

                    <!-- <h3>ABOUT</h3> -->
                    <p class="profile-username-display"> <?php
                                                            $profileInfo->fetchAbout($_SESSION["userid"]);
                                                            ?></p>
                    <h3>FOLLOWERS <span class="followers">56</span></h3>
                    <h3>FOLLOWING <span class="following">23</span></h3>
                    <!-- <hr class="separator"> -->

                    <div class="break"></div>
                    <a href="profilesettings.php" class="follow-btn">PROFILE SETTINGS</a>
                </div>
            </div>
            <div class="profile-content">
                <div class="profile-intro">


                    <h3> <?php
                            $profileInfo->fetchTitle($_SESSION["userid"]);
                            ?></h3>
                    <p> <?php
                        $profileInfo->fetchText($_SESSION["userid"]);
                        ?></p>
                    <div class="highlights">
                        <img class="highlight" src="img/artworks/artworks67.jpg" alt="highlights">
                        <img class="highlight" src="img/artworks/copenhagen-murals-outdoor.654e4e94b54df0.38933123.jpg" alt="highlights">
                        <img class="highlight" src="img/artworks/artworks60.jpg" alt="highlights">
                        <img class="highlight" src="img/artworks/logooo.png" alt="highlights">
                        <img class="highlight" src="img/artworks/longname.jpg" alt="highlights">
                        <img class="highlight" src="img/artworks/artworks66.jpg" alt="highlights">
                        <img class="highlight" src="img/artworks/artworks58.jpg" alt="highlights">
                    </div>
                </div>
                <div class="profile-posts">
                    <h3>POSTS</h3>
                    <div class="profile-post">
                        <h2>IT IS A BUSY DAY IN TOWN</h2>
                        <p>Sed porttitor nulla quis lectus gravida rutrum. Fusce dapibus odio id nibh tincidunt finibus. Praesent in massa at urna feugiat iaculis. Vivamus dictum ante in eleifend semper. Cras nec maximus ante. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Nullam diam ligula, semper sed semper posuere.</p>
                        <p>12:46 - 09/11/2021</p>
                    </div>
                    <div class="profile-post">
                        <h2>RE-USING ELECTRONICS IS A GOOD IDEA</h2>
                        <p>Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut lacinia ligula eget gravida fermentum. Curabitur arcu risus, ornare eu nibh a, porta interdum nunc. Mauris gravida velit dui, eu ultrices lacus finibus sit amet.</p>
                        <p>16:11 - 11/11/2021</p>
                    </div>
                    <a href="profilesettings.php" class="follow-btn posts">SEE ALL POSTS</a>
                </div>

            </div>
        </div>
        <!--Artwork Gallery-->

        <div class="cases-links">

            <h2 class="Artworks-title">Artworks</h2>
            <?php
            //when admin deletes artwork
            if (isset($_SESSION['message'])) {
                // Display the message inside a styled div
                echo '<div class="message-container">' . $_SESSION['message'] . '</div>';

                // Clear or unset the message to avoid displaying it again on subsequent page loads
                unset($_SESSION['message']);
            }
            ?>
            <div class="gallery-container">

                <?php
                include_once 'includes/dbh.inc.php';
                $sql = "SELECT * FROM artwork ORDER BY OrderArtwork DESC";
                $stmt = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($stmt, $sql)) {
                    echo "SQL statement failed";
                } else {
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '   <a class="artwork-contain" href="#">
                            <div class="image" style="background-image: url(img/artworks/' . $row["ImgFullNameArtwork"] . ');"></div>
                            <h3>' . $row["TitleArtwork"] . '</h3>
                            <p>' . $row["DescArtwork"] . '</p>
                        </a> ';
                        if ($_SESSION["Role"] == "Admin") {

                ?>
                            <form action="delete.php" method="post">
                                <input type="hidden" name="id" value="<?php echo $row["id"] ?>"> <!-- Replace '123' with your actual item ID -->
                                <button type="submit" name="delete">Delete</button>
                            </form> <?php

                                }
                            }
                        }

                                    ?>
            </div>
            <?php
            if (isset($_SESSION['username'])) {
                echo '
                <h2>Add new artwork here</h2>
                    <div class="form-wrapper profile-posts">
                    
    <form class="signup-form" action="includes/gallery-upload.inc.php" method="post" enctype="multipart/form-data">
    <div class="input-wrapper">
        <input type="text" name="filename" placeholder="File name..." class="input-text">
        </div>
        <div class="input-wrapper">
        <input type="text" name="filetitle" placeholder="Image title..." class="input-text">
        </div>
        <div class="input-wrapper">
        <input type="text" name="filedesc" placeholder="Image Description..." class="input-text">
        </div
        <div class="input-wrapper">
        <input type="file" name="file" class="add-artwork">
        <button type="submit" name="submit" class="header-login-a">UPLOAD</button>
        </div>
      
    </form>

</div>';
            }
            ?>
        </div>
    </div>
    </div>
</section>
<section class="reviews-wrapper">
    <div class=" reviews-container">
        <h3 class="reviews-title">REVIEWS</h3>
        <div class="reviews-individual">
            <h2 class="reviewer-job"><span class="reviewer">David</span> , Photographer</h2>
            <p class="date">09/11/2021</p>
            <p>Sed porttitor nulla quis lectus gravida rutrum. Fusce dapibus odio id nibh tincidunt finibus. Praesent in massa at urna feugiat iaculis. Vivamus dictum ante in eleifend semper. Cras nec maximus ante. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Nullam diam ligula, semper sed semper posuere.</p>
            <div class="review-stars"></div>
        </div>
        <div class="reviews-individual">
            <h2 class="reviewer-job"><span class="reviewer">John</span> , Photographer</h2>
            <p class="date">11/11/2021</p>
            <p>Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut lacinia ligula eget gravida fermentum. Curabitur arcu risus, ornare eu nibh a, porta interdum nunc. Mauris gravida velit dui, eu ultrices lacus finibus sit amet.</p>
            <div class="review-stars"></div>
        </div>
        <a href="profilesettings.php" class=" reviews-btn">SEE ALL REVIEWS</a>
    </div>
</section>

<section class="index-intro">
    <div class="index-intro-bg">

        <div class="wrapper">
            <div class="index-intro-c2">
                <h2>Add New <br>Walls</h2>
                <a href="map.php">ADD NEW WALL</a>
            </div>
            <div class="index-intro-c1">

                <div class="video">
                    <img src="img/graphics/sdfc.png" alt="fluidelement 3" class="hero-graphic">
                </div>
                <p>If you are a registered user and have any legal walls in mind don't hesitate to add them to our map. By doing this you are sharing with and helping
                    thousands of artists that are looking for places to paint or explore. </p>
            </div>

        </div>
    </div>
</section>
</body>



</html>