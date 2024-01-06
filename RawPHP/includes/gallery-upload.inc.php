<?php
session_start();
if (isset($_POST['submit'])) {
    $newFileName = $_POST['filename'];
    if (empty($newFileName)) {
        $newFileName = "gallery";
    } else {
        $newFileName = strtolower(str_replace(" ", "-", $newFileName));
    }
    $imageTitle = $_POST['filetitle'];
    $imageDesc = $_POST['filedesc'];

    $userID = $_SESSION['userid'];
    $file = $_FILES['file'];
    $fileName = $file["name"];
    $fileType = $file["type"];
    $fileTempName = $file["tmp_name"];
    $fileError = $file["error"];
    $fileSize = $file["size"];


    $fileExt = explode(".", $fileName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = array("jpg", "jpeg", "png", "gif", "mp4");
    //error handling

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError == 0) {
            if ($fileSize < 2000000) {
                //adding image to gallery
                $imageFullName = $newFileName . "." . uniqid("", true) . "." . $fileActualExt;
                $fileDestination = "../img/artworks/" . $imageFullName;

                include_once "dbh.inc.php";
                //error handling
                if (empty($imageTitle) || empty($imageDesc)) {
                    header("Location: ../profile.php?upload=empty");
                    exit();
                } else {
                    $sql = "SELECT * FROM artwork;";
                    $stmt = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        echo "SQL statement failed!";
                    } else {
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $rowCount = mysqli_num_rows($result);
                        $setImageOrder = $rowCount + 1;

                        //insert image data in database

                        $sql = "INSERT INTO artwork (TitleArtwork, DescArtwork, ImgFullNameArtwork, OrderArtwork, UserID) VALUES (?, ?, ?, ?, ?);";
                        if (!mysqli_stmt_prepare($stmt, $sql)) {
                            echo " SQL statement failed!";
                        } else {
                            mysqli_stmt_bind_param($stmt, "sssss", $imageTitle, $imageDesc, $imageFullName, $setImageOrder, $userID);
                            mysqli_stmt_execute($stmt);

                            move_uploaded_file($fileTempName, $fileDestination);

                            header("Location: ../profile.php?uploadsuccess");
                        }
                    }
                }
            } else {
                echo "File size is too big!";
            }
        } else {
            echo "You had an error!";
        }
    } else {
        echo "You need to upload a proper file type!";
        exit();
    }
}
