<?php

namespace App\Models;
use XTeam\Framework\Core\Model;

class User extends Model{
    
    const table_name = "user";
    const column_defenitions = array("columns"=>
        array(
            ["name"=>"username","type"=>"VARCHAR(128)","options"=>"UNIQUE"],
            ["name"=>"password","type"=>"VARCHAR(128)"],
            ["name"=>"email","type"=>"VARCHAR(256)"],
            ["name"=>"profile_id","type"=>"INT","options"=>"DEFAULT '0'"],
            ["name"=>"security_elevation","type"=>"ENUM('root','admin','editor','user','visitor')", "options"=>"DEFAULT 'visitor'"],
            ["name"=>"analytic_data","type"=>"TEXT", "options"=>"DEFAULT NULL"],
            ["name"=>"push_id","type"=>"VARCHAR(255)", "options"=>"DEFAULT NULL"],
            ["name"=>"push_data","type"=>"TEXT", "options"=>"DEFAULT NULL"],
            
            )
    );
    
    public function pushMessage($title,$body,$icon="data/img/schild.png"){
        $data = array("title"=>$title,"body"=>$body,"icon"=>$icon);
        $this->push_data = json_encode($data);
        $this->save();
       // \Core\Utils::sendPush($this->push_id);
    }
    
    public function __construct($id = null){
        parent::__construct($id);
    }
    public static function make($username, $password, $email, $elevation){
       
    }
    public function checkElevation($elevationInt){
        if (!is_numeric($elevationInt))
            $elevationInt = $this->getElevationInt($elevationInt);
       
        return($elevationInt>= $this->getElevationInt());
    }
    public function getElevationInt($int = false){
        $el = $int;
        if (!$int)
            $el = $this->security_elevation;
        switch($el){
            case 'root':
                return 1;
                break;
            case 'admin':
                return 2;
                break;
            case 'editor':
                return 3;
                break;
            case 'user':
                return 4;
                break;
            case 'visitor':
                return 5;
                break;
            default:
                return 99;
                break;
        }
    }
    public static function fromKey($key){
        return static::fromGUID($key);
    }
}
?>