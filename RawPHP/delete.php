<?php
include_once "functions.php";
$id = $_SESSION['userid'];

if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    deleteArtwork($id, $conn);
    $_SESSION["message"] = "Image Successfully deleted!";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
