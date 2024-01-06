<?php
//database related stuff

// do smth inside database in this file

//errors

error_reporting(E_ALL);

//create class

class Signup extends Dbh
{

    protected function setUser($username, $pwd, $email, $profile, $role)
    {
        //separate data from query to prevent sql injection

        $stmt = $this->connect()->prepare('INSERT INTO user (username, pwd, email, UserProfile, Role) VALUES (?, ?, ?, ?, ?);');
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);



        if (!$stmt->execute(array($username, $hashedPwd, $email, $profile, $role))) {
            $stmt = null;
            header("location: ../indexsignup.php?error=stmtfailed");
            exit();
        }

        $stmt = null;
    }

    protected function checkUser($username, $email)
    {
        //separate data from query to prevent sql injection

        $stmt = $this->connect()->prepare('SELECT UserID FROM user WHERE UserID = ? OR email = ?;');
        if (!$stmt->execute(array($username, $email))) {
            $stmt = null;
            header("location: ../indexsignup.php?error=stmtfailed");
            exit();
        }

        $resultCheck = "";

        if ($stmt->rowCount() > 0) {
            $resultCheck = false;
        } else {
            $resultCheck = true;
        }
        return $resultCheck;
    }

    protected function getUserId($username)
    {
        $stmt = $this->connect()->prepare('SELECT UserID FROM user WHERE username = ?;');
        if (!$stmt->execute(array($username))) {
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
}
