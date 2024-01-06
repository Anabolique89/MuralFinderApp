<?php

class Login extends Dbh
{

    protected function getUser($username, $pwd)
    {
        $stmt = $this->connect()->prepare('SELECT pwd FROM user WHERE username = ? OR email = ?;');

        if (!$stmt->execute(array($username, $pwd))) {
            $stmt = null;
            header("location: ../indexlogin.php?error=stmtfailed");
            exit();
        }

        if ($stmt->rowCount() == 0) {
            $stmt = null;
            header("location: ../indexlogin.php?error=usernotfound");
            exit();
        }

        $pwdHashed = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $checkPwd = password_verify($pwd, $pwdHashed[0]["pwd"]);

        if ($checkPwd == false) {
            $stmt = null;
            header("location: ../indexlogin.php?error=wrongpassword");
            exit();
        } elseif ($checkPwd == true) {
            $stmt = $this->connect()->prepare('SELECT * FROM user WHERE username = ? OR email = ? AND pwd = ?;');

            if (!$stmt->execute(array($username, $username, $pwd))) {
                $stmt = null;
                header("location: ../indexlogin.php?error=stmtfailed");
                exit();
            }

            if ($stmt->rowCount() == 0) {
                $stmt = null;
                header("location: ../indexlogin.php?error=usernotfound");
                exit();
            }

            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

            session_start();
            $_SESSION["userid"] = $user[0]["UserID"];
            $_SESSION["username"] = $user[0]["Username"];
            $_SESSION["Role"] = $user[0]["Role"];

            $stmt = null;
        }
    }
}
