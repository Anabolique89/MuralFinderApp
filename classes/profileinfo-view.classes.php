<?php

class ProfileInfoView extends ProfileInfo
{

    public function fetchAbout($userID)
    {
        $profileInfo = $this->getProfileInfo($userID);

        echo $profileInfo[0]["profilesAbout"];
    }

    public function fetchTitle($userID)
    {
        $profileInfo = $this->getProfileInfo($userID);

        echo $profileInfo[0]["profileIntroTitle"];
    }

    public function fetchText($userID)
    {
        $profileInfo = $this->getProfileInfo($userID);

        echo $profileInfo[0]["profileIntroText"];
    }
}
