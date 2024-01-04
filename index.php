<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: webpage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Artzoro</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>

<body>
    <main class="main">
        <img src="./img/graphics/onboarding1.png" class="graphics" alt="graphic1">

        <div class="onboarding-page">
            <h1 class="roboto-uppercase-heading">Welcome to Artzoro</h1>
            <p class="welcome-heading-small">Explore your full <br> creativity</p>
            <p class="welcome-text">Feel confident in having full coverage <br> when travelling and seeking murals. </p>
            <a href="welcome2.php" class="btn">Next</a>
        </div>
    </main>
</body>

</html>