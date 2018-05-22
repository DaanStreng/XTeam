<?php

namespace App\Controllers;
use XTeam\Framework\Core\Filter;
use XTeam\Framework\Core\Utils;
abstract class UserSecured extends Filter{
    protected function checkUser($elevation){
        global $_SESSION;
        if ($_SESSION){
            if (isset($_SESSION['user']) && $_SESSION['user']->checkElevation($elevation)){
                return true;
            }
        }
        return false;
    }
    
    function filter($annotations){
        foreach($annotations as $key=>$value){
            if ($key == "Secured"){
                if (!$this->checkUser($value)){
                    return array("result"=>false, "message"=>"user is not allowed to use this function");
                }
            }
        }
        return array("result"=>true);
    }
}