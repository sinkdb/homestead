<?php

PHPWS_Core::initModClass('hms', 'StudentDataProvider.php');
PHPWS_Core::initModClass('hms', 'Student.php');

class StudentFactory {

    public static function getStudentByUsername($username, $term, $provider = NULL)
    {
        if(is_null($provider)){
            $provider = StudentDataProvider::getInstance();
        }

        return $provider->getStudentByUsername($username, $term);
    }

    /**
     * 
     * @param string $bannerID
     * @param integer $term
     * @param ApcDataProvider|LocalCacheDataProvider $provider
     * @return Student
     */
    public static function getStudentByBannerID($bannerID, $term, $provider = NULL)
    {
        if(is_null($provider)){
            $provider = StudentDataProvider::getInstance();
        }

        $provider = StudentDataProvider::getInstance();
        return $provider->getStudentById($bannerID, $term);
    }
}
