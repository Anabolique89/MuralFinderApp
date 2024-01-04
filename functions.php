<?php
session_start();
include_once "classes/dbh.classes.php";
$instance = new Dbh();
$conn = $instance->connect();

$id = $_SESSION['userid'];


function deleteOldImage($oldImage)
{
    if (!empty($oldImage)) {
        $oldImagePath = 'img/artworks/' . $oldImage;
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }
}

function uploadNewImage($file, $id, $conn)
{
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($fileActualExt, $allowed) && $fileError === 0 && $fileSize < 5000000) {
        try {
            // Check if a profile image exists
            $sqlCheckImage = "SELECT COUNT(*) as imageCount, NewImgName FROM profileimg WHERE UserID=?";
            $stmt = $conn->prepare($sqlCheckImage);
            $stmt->execute([$id]);
            $rowCheckImage = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete the old image
            deleteOldImage($rowCheckImage['NewImgName']);

            // Upload the new image
            $fileNameNew = "artworks" . $id . "." . $fileActualExt;
            $fileDestination = 'img/artworks/' . $fileNameNew;


            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                return ['success' => true, 'fileNameNew' => $fileNameNew, 'imageCount' => $rowCheckImage['imageCount']];
            } else {
                return ['success' => false, 'message' => "There was an error uploading your file!"];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Error: " . $e->getMessage()];
        }
    } else {
        return ['success' => false, 'message' => "Invalid file format or file size exceeds the limit."];
    }
}

function UpdateDatabase($conn, $id, $imageCount, $fileNameNew)
{
    // Update or insert into the database
    if ($imageCount > 0) {
        $sqlUpdate = "UPDATE profileimg SET NewImgName=?, status=0 WHERE UserID=?";
    } else {
        $sqlUpdate = "INSERT INTO profileimg (NewImgName, UserID, status) VALUES (?, ?, 0)";
    }

    $stmt = $conn->prepare($sqlUpdate);
    $stmt->execute([$fileNameNew, $id]);
}
function deleteArtwork($id, $conn)
{
    $sqlSelect = "SELECT ImgFullNameArtwork FROM artwork WHERE id = :id";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtSelect->execute();
    $imageFilename = $stmtSelect->fetchColumn();
    $stmtSelect->closeCursor();


    $sqlDelete = "DELETE from artwork WHERE id = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->execute([$id]);
    $stmt->closeCursor();
    deleteOldImage($imageFilename);
}
