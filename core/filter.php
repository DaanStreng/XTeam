<?php 
namespace XTeam\Framework\Core;

abstract class Filter{
    function filter($annotations){}
    
    public function __call($method,$arguments) {
     
        if(method_exists($this, $method)) {
            $annotations = $this->getAnnotations($method);
            $value = $this->filter($annotations);
            if ($value["result"] == true)
                return call_user_func_array(array($this,$method),$arguments);
            else
            {
                if (isset($annotations['Redirect'])){
                    header("Location: ".$annotations['Redirect']);
                    return "";
                }
                return $value['message'];
            }
        }
    }
    protected function getAnnotations($function){
        return getAnnotations(get_class($this),$function);
    }
    
}
 function readMe($class){
       $class = new \ReflectionClass($class);
       $file = $class->getFileName();
       return file_get_contents($file);
    }
     function getComments($class, $function){
         
         $f = new \ReflectionMethod($class, $function);
         $sub = $f->getDocComment();
     
         if ($sub) return stripComments($sub);
        return false;
    }
    function getPathFunction($class,$path){
       // echo $class;
       
        $vars = $class->getMethods();
        $class= $class->getName();
        $starMethod = false;
        foreach($vars as $method){
            $annos = getAnnotations($class,$method->getName());
           
            if (isset($annos['Path'])){
                if ($annos['Path']==$path){
                    return array("method"=>$method->getName(),"input"=>get_func_argNames($class,$method->getName()));
                }
                else if ( trim($annos['Path']) == "*"){
                    $starMethod = array("method"=>$method->getName(),"input"=>get_func_argNames($class,$method->getName()));
                }
            }
        }
       
        return $starMethod;
    }
    function get_func_argNames($class, $funcName) {
        $f = new \ReflectionMethod($class, $funcName);
        $result = array();
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;   
        }
        return $result;
    }
    function stripComments($sub){
        $sub = str_replace("/**","",$sub);
            $sub = str_replace("/*","",$sub);
            $sub = str_replace("/***","",$sub);
            $sub = str_replace("* ","",$sub);
            $sub = str_replace("/","",$sub);
           // $sub = str_replace("*","",$sub);
            return trim($sub);
    }
     function getAnnotations($class,$function){
        $comments = getComments($class, $function);
    
        return parseAnnotations($comments);
        
    }
    function parseAnnotations($comments){
        $ans = array();
        if ($comments){
            if (stristr($comments,"@"));
            {
                while(stristr($comments,"@")){
                    $annotation = substr($comments,strpos($comments,"@"));
                    if (stristr($annotation," "))
                        $annotation = substr($annotation,0, strpos($annotation," "));
                    $comments = str_replace($annotation,"",$comments);
                    $value = "void";
                    if (stristr($annotation,"(")){
                        
                        $value = substr($annotation,strpos($annotation,"("));
                        
                        $annotation = str_replace($value,"",$annotation);
                    
                        $value = substr($value,1,strpos($value,")")-1);
                       
                      
                    }
                    $ans[trim($annotation,"@")] = $value; 
                }
            }
        }
        return $ans;
    }