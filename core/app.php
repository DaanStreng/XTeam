<?php
namespace XTeam\Framework\Core;
class App{
    private $defaultController;
    private $content;
    private $userClass;
    public function __construct($defaultController, $userClass){
        
        require_once(dirname(__FILE__)."/database.php");
        $this->includeAll(dirname(__FILE__)."/../core");
        $this->includeAll(dirname(__FILE__)."/../app/model");
        require_once(dirname(__FILE__)."/../app/controller/usersecured.php");
        $this->includeAll(dirname(__FILE__)."/../app/controller");
        $this->defaultController = $defaultController;
        $this->userClass = $userClass;
        session_start();
        if (!isset($_SESSION))
            $_SESSION = array();
    
    }
    function includeAll($dir){
        foreach (glob("$dir/*.php") as $file) {
            require_once($file);
        }
    }
    public function getContent(){
        return $this->content;
    }
    
    function route(){
 
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $res = parse_url($actual_link);
        $path = trim($res['path'],"/");
        $path = str_replace("//","/",$path);
        $parts = explode("/",$path);
        $exparts = array();
        
        foreach($parts as $part){
            if ($part && trim($part))
                $exparts[] = $part;
        }
        $parts = $exparts;
        
        $setcontent = false;
        if (count($parts)<=0|| !$parts[0])
            return false;
        else if (count($parts)==1)
        {
            $func = $parts[0];
            $cont = $this->defaultController;
            $setcontent = true;
            if (isset($_REQUEST['apikey']))
            if ($key = $_REQUEST['apikey']){
                global $_SESSION;
                if (isset($_SESSION)){
                    $_SESSION['user'] = $this->userClass::fromKey($key);
                }
            }
        }
        else if (count($parts)==2)
        {
            $cont = $parts[0];
            $func = $parts[1];
            if (isset($_REQUEST['apikey']))
            if ($key = $_REQUEST['apikey']){
                global $_SESSION;
                if (isset($_SESSION)){
                    $_SESSION['user'] = $this->userClass::fromKey($key);
                }
            }
        }
        else if (count($parts)>2){
            $key = $parts[0];
            global $_SESSION;
            if (isset($_SESSION)){
                $_SESSION['user'] = $this->userClass::fromKey($key);
            }
            $cont = $parts[1];
            $func = $parts[2];
        }
        if (isset($_SESSION['user']) && $_SESSION['user']->guid){
            $_SESSION['user'] = $this->userClass::fromGUID($_SESSION['user']->guid);
        }
        $_SESSION['current_function'] = $func;
        foreach(get_declared_classes() as $class)
        {
            
            $interfaces = class_implements($class);
            if (isset($interfaces['XTeam\Framework\Core\Controller'])){
            
            
                $rc = new \ReflectionClass($class);
                $annos = parseAnnotations($rc->getDocComment());
           
                if (isset($annos['Path']) && $annos['Path'] == $cont){
                    $controller = new $class();
                    $method = getPathFunction($rc,$func);
               
                    $vars = $method['input'];
                    $method = $method['method'];
                    if (!(isset($method) && $method&&strlen($method)>2)){
                        header("HTTP/1.0 404 Not Found");
                        
                        exit();
                    }
                    $args = array();
                    if (count($vars)>0)
                    foreach($vars as $v){
                        $vfound = false;
                        foreach($_REQUEST as $key=>$value){
                            if ($key == trim($v)){
                                $args[] = $value;
                                $vfound = true;
                            }
                        }
                        if(!$vfound){
                            $args[] = false;
                        }
                    }
                    
                    $mannos = getAnnotations($class,$method);
                    
                    $result = call_user_func_array(array($controller,$method), $args);
                    if (isset($mannos['Produces'])){
                        
                        if ($mannos['Produces'] == "json"){
                            header('Content-Type: application/json');
                        }
                        if ($mannos['Produces'] == "image"){
                           
                            header("Content-Type: image/* ");
                        }
                        if ($mannos['Produces'] == "jpeg"){
                           
                            header("Content-Type: image/jpeg ");
                        }
                    }
                    if ($setcontent){
                        $this->content = $result;
                        return false;
                    }
                    else if ($result)
                        echo $result;
                    
                }
            }
            
        }
        return true;
    }
}