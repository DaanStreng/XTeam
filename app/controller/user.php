<?php

namespace App\Controllers;

use XTeam\Framework\Core\Filter;
use XTeam\Framework\Core\Controller;
use App\Models\User;
use App\Controllers\UserSecured;
use App\Models\Organisation;

use Core\Utils;

/**
* @Path(user)
*/
class UserController extends UserSecured implements Controller{
   
   /**
    * @Path(login)
    * @Produces(json)
    */
    protected function login($username, $password){
        usleep(200000);
        $password = \Database::passwordify($password);
        $user = User::first('username',$username);
        
        if (!$user){
            $data = array("result"=>"error","reason"=>"username");
            return json_encode($data);
        }
        if ($user->password != $password){
            $data = array("result"=>"error","reason"=>"password");
            return json_encode($data);
        }
        unset($user->password);
        global $_SESSION;
        $_SESSION['user'] = $user;
        if ($trace = $_SESSION['trace']){
            if (count($_SESSION['trace'])>1){
                $trace = $_SESSION['trace'][count($_SESSION['trace'])-2];
            }
            else $trace = reset($trace);
        }
        if (stristr($trace,"login")||!$trace){
            $trace = "home";
        }
        $ret = array("user"=>$user,"target"=>$trace);
        return json_encode($ret);
        
    }
    
    /**
    * @Path(editme)
    * @Secured(5)
    */
    protected function editMe($new_password,$confirm_password,$email){
        global $_SESSION;
        $user = $_SESSION['user'];
        if ($new_password&&$new_password == $confirm_password){
            $user->password = \Database::passwordify($new_password);
        }
        if ($email){
            $user->email = $email;
        }
        $user->save();
        header("Location: /myaccount");
        $_SESSION['msg'] = "Jeeeee, alles is opgeslagen!!";
    }
    /**
    * @Path(edit)
    * @Secured(2)
    */
    protected function edit($user, $password, $email, $profile_id,$security_elevation){
        
        $user = User::fromGUID($user);
        if ($password)
            $user->password = \Database::passwordify($password);
        if ($email)
            $user->email = $email;
        if ($profile_id)
            $user->profile_id = $profile_id;
        if($security_elevation)
            $user->security_elevation = $security_elevation;
        $user->save();
        header("Location: /admin?suser=".$user->guid);
        $_SESSION['msg'] = "Jeeeee, alles is opgeslagen!!";
    }
    
    /**
    * @Path(new)
    * @Secured(2)
    */
    protected function new($username, $password, $email, $profile_id,$security_elevation){
        
        $user = new User();
        $user->username= $username;
            $user->password = \Database::passwordify($password);
        $user->security_elevation = $security_elevation;
            $user->email = $email;
    
            $user->profile_id = $profile_id;
        $user->save();
        header("Location: /admin?suser=".$user->guid);
        $_SESSION['msg'] = "Jeeeee, alles is opgeslagen!!";
        
    }
    
    /**
    * @Path(logout)
    */
    protected function logout(){
        session_destroy();
        header("Location: /home/?x=1");
    }
    
}
