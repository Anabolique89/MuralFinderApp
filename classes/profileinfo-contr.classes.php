<?php
//use this file for changing data inside database
class ProfileInfoContr extends ProfileInfo
{
    private $userID;
    private $userUID;

    public function __construct($userID, $userUID)
    {
        $this->userID = $userID;
        $this->userUID = $userUID;
    }
    public function defaultProfileInfo()
    {
        $profileAbout = "Describe your profile with a sentence.";
        $profileTitle = "Hi! I am " . $this->userID;
        $profileText = "Welcome to my profile page! Soon you will be able to read more about my art.";
        $this->setProfileInfo($profileAbout, $profileTitle, $profileText, $this->userUID);
    }

    //here the user can update the default info given already up

    public function updateProfileInfo($about, $introTitle, $introText)
    {
        //error handlers
        if ($this->emptyInputCheck($about, $introTitle, $introText) == true) {
            header("location: ../profilesettings.php?error=emptyinput");
            exit();
        }
        //run actual creation of profile - update profile info 
        $this->setNewProfileInfo($about, $introTitle, $introText, $this->userID);
    }
    private function emptyInputCheck($about, $introTitle, $introText)
    {
        $result = "";
        if (empty($about) || empty($introTitle) || empty($introText)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}
