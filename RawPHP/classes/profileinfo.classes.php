<?php

class ProfileInfo extends Dbh
{
    protected function getProfileInfo($userID)
    {
        $stmt = $this->connect()->prepare('SELECT * FROM profiles WHERE UserID = ?;');
        if (!$stmt->execute(array($userID))) {
            $stmt = null;
            header("location: ../profile.php?error=stmtfailed");
            exit();
        }

        if ($stmt->rowCount() == 0) {
            $stmt = null;
            header("location: ../profile.php?error=profilenotfound");
            exit();
        }
        $profileData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $profileData;
    }

    protected function setNewProfileInfo($profileAbout, $profileTitle, $profileText, $userID)
    {
        $stmt = $this->connect()->prepare('UPDATE profiles SET profilesAbout = ?, profileIntroTitle = ?, profileIntroText = ? WHERE UserID = ?;');
        if (!$stmt->execute(array($profileAbout, $profileTitle, $profileText, $userID))) {
            $stmt = null;

            //array for errors
            // $_SESSION["errors"] = "error=stmtfailed";

            header("location: ../profile.php?error=stmtfailed");
            exit();
        }
        $stmt = null;
    }

    protected function setProfileInfo($profileAbout, $profileTitle, $profileText, $userID)
    {
        $stmt = $this->connect()->prepare('INSERT INTO profiles (profilesAbout, profileIntroTitle, profileIntroText, UserID ) VALUES (?, ?, ?, ?)');
        if (!$stmt->execute(array($profileAbout, $profileTitle, $profileText, $userID))) {
            $stmt = null;
            header("location: ../profile.php?error=stmtfailed");
            exit();
        }
        $stmt = null;
    }
}
