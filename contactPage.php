<?php

include_once "header.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="style1.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <title>Urban Art Worldwide</title>
</head>

<body>
    <main class="main">
        <img src="./img/graphics/bubbles.png" class="hero-graphic0" alt="hero-graphic0">

        <!-- <div class="onboarding-page2">
            <h1 class="roboto-uppercase-heading">Urban Art Worldwide</h1>
            <p class="welcome-heading-small">And new places on the map.</p>
            <p class="welcome-text">Find your favourite walls and share your work while
                exploring and travelling the world.</p>
            <a href="welcome3.php" class="btn">Next</a>
        </div> -->
        <div class="onboarding-page3">
            <div class="contact-form-wrapper">

                <h1 class="roboto-uppercase-heading">Contact Form</h1>
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
        </div>
    </main>
</body>

</html>