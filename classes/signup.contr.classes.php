<?php

// do smth inside database in this file

//create class

class SignupContr extends Signup
{
    private $username;
    private $pwd;
    private $pwdRepeat;
    private $email;
    private $profile;
    private $role;

    //create constructor

    public function __construct($username, $pwd, $pwdRepeat, $email, $profile, $role)
    {
        $this->username = $username;
        $this->pwd = $pwd;
        $this->pwdRepeat = $pwdRepeat;
        $this->email = $email;
        $this->profile = $profile;
        $this->role = $role;
    }

    //error handlers

    public function signupUser()
    {
        if ($this->emptyInput() == false) {
            // echo "Empty input!"
            header("location: ../indexsignup.php?error=emptyinput");
            exit();
        }
        if ($this->invalidUid() == false) {
            // echo "Invalid username!"
            header("location: ../indexsignup.php?error=invalidusername");
            exit();
        }
        if ($this->invalidEmail() == false) {
            // echo "Invalid email!"
            header("location: ../indexsignup.php?error=invalidemail");
            exit();
        }
        if ($this->pwdMatch() == false) {
            // echo "Passwords don't match!"
            header("location: ../indexsignup.php?error=passwordmatch");
            exit();
        }

        if ($this->uidTakenCheck() == false) {
            // echo "Username of email taken"
            header("location: ../indexsignup.php?error=useroremailtaken");
            exit();
        }
        $this->setUser($this->username, $this->pwd, $this->email, $this->profile, $this->role);
    }

    private function emptyInput()
    {
        $result = "";
        if (empty($this->username) || empty($this->pwd) || empty($this->pwdRepeat) || empty($this->email) || empty($this->profile)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private function invalidUid()
    {
        $result = "";
        if (!preg_match("/^[a-zA-Z0-9]*$/", $this->username)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private function invalidEmail()
    {
        $result = "";
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private function pwdMatch()
    {
        $result = "";
        if ($this->pwd !== $this->pwdRepeat) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    private function uidTakenCheck()
    {
        $result = "";
        if (!$this->checkUser($this->username, $this->email)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    public function fetchUserId($username)
    {
        $userID = $this->getUserId($username);
        return $userID[0]["UserID"];
    }
}
