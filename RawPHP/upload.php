<?php
include_once "functions.php";
$id = $_SESSION['userid'];

if (isset($_POST['submit'])) {
    $file = $_FILES['file'];

    // Pass the database connection to the functions
    $uploadResult = uploadNewImage($file, $id, $conn);

    if ($uploadResult['success']) {
        updateDatabase($conn, $id, $uploadResult['imageCount'], $uploadResult['fileNameNew']);
        header("Location: profile.php?uploadsuccess");
        exit();
    } else {
        echo $uploadResult['message'];
    }
}
