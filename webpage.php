<?php

session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtZoro Presentation Website</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="homepage">
        <header class="main-header">

            <nav class="main-nav">
                <div class="navigation-container">
                    <div class="logo-container"><a href="webpage.php"><img class="logo" src="./img/LOGOBlack.png" alt="logo white"></a></div>
                    <ul class="menu-main">
                        <li><a href="webpage.php">HOME</a></li>
                        <li><a href="about.php">ABOUT</a></li>
                        <li><a href="map.php">MAP</a></li>
                        <li><a href="walls.php">WALLS</a></li>
                        <li><a href="community.php">COMMUNITY</a></li>
                        <li><a href="shops.php">SHOPS</a></li>
                        <li><a href="contactPage.php">CONTACT</a></li>
                    </ul>
                </div>
                <ul class="menu-member">
                    <?php
                    if (isset($_SESSION["userid"])) {
                    ?>
                        <li><a href="profile.php"><?php echo $_SESSION["username"]; ?></a></li>
                        <li><a href="includes/logout.inc.php" class="header-login-a">LOGOUT</a></li>
                    <?php
                    } else {
                    ?>
                        <li><a href="indexsignup.php">SIGN UP</a></li>
                        <li><a href="indexlogin.php" class="header-login-a ">LOGIN</a></li>
                    <?php
                    }
                    ?>
                </ul>
            </nav>
        </header>

        <main>
            <!--insert carousel hero section here-->
            <section class="index-intro">
                <div class="index-intro-bg">
                    <div class="video"><img src="img/graphics/fluidElement4.png" alt="fluidelement 1" class="hero-graphic1"></div>
                    <div class="wrapper">
                        <div class="index-intro-c1">

                            <!-- <div class="video"><img src="img/graphics/" alt="fluidelement 1" class="hero-graphic1"></div> -->
                            <i class="fa-brands fa-x-twitter icon"></i>
                            <i class="fa-brands fa-facebook icon"></i>
                            <i class="fa-brands fa-instagram icon"></i>
                            <p class="cardss p2">A platform that connects the urban art community worldwide and allows artists to explore new
                                terrain and expand their creative talents easily all the while meeting new people and sharing
                                new experiences with fellow artists. </p>
                        </div>
                        <div class="index-intro-c2">
                            <h2>Welcome to<br>ArtZoro</h2>
                            <a class="header-login-a" href="map.php">FIND WALLS</a>
                        </div>
                    </div>
                </div>
            </section>
            <section class="Artwork-gallery-main">

                <div class="cases-links">

                    <h2 class="Artworks-title">Artworks Feed</h2>
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
                                echo '   <a href="#">
                <div class="image" style="background-image: url(img/artworks/' . $row["ImgFullNameArtwork"] . ');"></div>
                <h3>' . $row["TitleArtwork"] . '</h3>
                <p>' . $row["DescArtwork"] . '</p>
            </a> ';
                            }
                        }


                        ?>
                    </div>
                </div>
            </section>
            <!-- publish new walls section -->
            <section class="index-intro">
                <div class="index-intro-bg">

                    <div class="wrapper">

                        <div class="index-intro-c1">

                            <div class="video">
                                <img src="img/graphics/sfa1.png" alt="fluidelement 2" class="hero-graphic">
                            </div>
                            <p class="cardss">If you are an artist or a stakeholder and you want to share a new painted wall with the world for them to paint on or just explore in a certain location, we are here to make that happen. Just contact us and we will verify the information and make it happen! </p>
                        </div>
                        <div class="index-intro-c2">
                            <h2>Review<br>Walls</h2>
                            <a href="map.php">REVIEW WALL</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Subscribe or Register to Newsletter section here  -->
            <section class="newsletter">
                <div class="newsletter-bg">
                    <div class="wrapper">

                        <form action="newsletter.php">
                            <h2 class="Artworks-title">Newsletter</h2>
                            <p class="newsletter-p">Subscribe to our newsletter to receive updates and news.</p>
                            <div class="input-wrapper">
                                <input type="text" name="email" placeholder="Your email here..." class="input-text">
                            </div>
                            <a class="contact-btn header-login-a" href="map.php">SUBSCRIBE</a>
                        </form>
                    </div>
                </div>
            </section>
            <!-- publish new walls section 3 -->
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
                            <p class="cardss">If you are a registered user and have any legal walls in mind don't hesitate to add them to our map. By doing this you are sharing with and helping
                                thousands of artists that are looking for places to paint or explore. </p>
                        </div>

                    </div>
                </div>
            </section>
            <!--wall feed or POST FEED goes here-->
            <!--contact form-->
            <!-- <h2 class="Artworks-title">HAVE A BURNING QUESTION?</h2> -->
            <section class="index-intro2">

                <div class="index-intro-c1 contact-form-text">
                    <h2 class="contact-title">Find more information here</h2>


                    <div class="video2">
                        <img src="img/graphics/xs.png" alt="fluidelement 3" class="hero-graphic contact-img">
                    </div>
                    <p class="contact-section-text">
                        Please don't hesitate to write to us if you have any suggestions about how we can improve and be of
                        even more help to the artistic community.
                        <br><br>
                        So you found a wall that is allegedly legal, but want to know for sure. If a spot is truly legal,
                        some basic web searches usually quickly confirm it. If not, do some research. Here is how to go about this. Find our blog
                        posts where we share all the information you need to know when searching for walls to paint or visit.
                        <br><br>
                        Still not satisfied? Write to us now and we will try to get back to you asap.
                    </p>

                    <!-- <a class="header-login-a contact-btn" href="contact.php">LEARN MORE</a> -->
                </div>
                <div class="contact-form-wrapper">


                    <br>
                    <form class="about-form" action="contactform.php" method="post">
                        <div class="input-wrapper2">
                            <input type="text" name="name" placeholder="Name" class="input-text2" required>
                        </div><br>
                        <div class="input-wrapper2">
                            <input type="text" name="mail" placeholder="Email" class="input-text2" required>
                        </div><br>
                        <div class="input-wrapper2">
                            <input type="text" name="subject" placeholder="Subject" class="input-text2" required>
                        </div>
                        <br>
                        <div class="input-wrapper2">
                            <textarea name="message" rows="10" cols="30" class="input-text2" placeholder="Your message here..."></textarea>
                        </div>
                        <br>
                        <button class="follow-btn" type="submit" name="submit">SEND</button>

                    </form>
                </div>

            </section>
            <!-- find  new walls section -->
            <section class="index-intro">
                <div class="index-intro-bg">

                    <div class="wrapper">

                        <div class="index-intro-c1">

                            <div class="video">
                                <img src="img/graphics/sd.png" alt="fluidelement4" class="hero-graphic">
                            </div>
                            <p class="cardss">If you are an artist or a stakeholder and you want to share a new painted wall with the world for them to paint on or just explore in a certain location, we are here to make that happen. Just contact us and we will verify the information and make it happen! </p>
                        </div>
                        <div class="index-intro-c2">
                            <h2>Find New<br>Walls</h2>
                            <a href="map.php">FIND WALLS</a>
                        </div>
                    </div>
                </div>
            </section>


        </main>
    </div>
</body>

</html>