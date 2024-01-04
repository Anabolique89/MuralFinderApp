<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Grabbing the data
    $username = htmlspecialchars($_POST["username"], ENT_QUOTES, 'UTF-8');
    $pwd = htmlspecialchars($_POST["pwd"], ENT_QUOTES, 'UTF-8');

    // Instantiate SignupContr class
    include "../classes/dbh.classes.php";
    include "../classes/login.classes.php";
    include "../classes/login-contr.classes.php";


    $login = new LoginContr($username, $pwd);

    // Running error handlers and user signup

    $login->loginUser();



    // Going to back to front page
    header("location: ../profile.php?error=none");
}
