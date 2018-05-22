<?php
namespace  XTeam\Framework\Core;
class AsyncMailer{
    private $subject;
    private $to;
    private $html;
    private $text;
    private $apistring;
    private $apiurl;
    private $from;
    
    private $done;
    private $result;
    
    private $procHandle;
    private $processCommand;
    private $descriptorspec = array(
               0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
               1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
               2 => array("pipe", "w"), // stderr is a file to write to
            );
    private $processPipes;
    
    
    public function __construct($from, $to, $subject, $text, $html, $apiurl,$apistring){
        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
        $this->text = $text;
        $this->html = $html;
        $this->apiurl = $apiurl;
        $this->apistring = $apistring;
        $this->done = false;
        $this->result = false;
        $this->processCommand = "php ".dirname(__FILE__)."/curlscript.php";
    }
    public function __destruct(){
      
            if($this->processPipes){
                foreach($this->processPipes as $pipe){
                    try{
                        if(is_resource($pipe))
                        fclose($pipe);
                    }catch(Exception $ex){}
                }
                
            }
            try{
                if(is_resource($this->procHandle))
                    proc_close($this->procHandle);
            }catch(Exception $ex){}
            
        
    }
    
    public function start(){
        $command = $this->processCommand;
        $command .= " -f '$this->from' -t '$this->to' -s '$this->subject' -x '$this->text' -a '$this->apiurl' -p '$this->apistring'";

        $this->procHandle = proc_open($command , $this->descriptorspec, $pipes); 
     
        $this->processPipes = $pipes;
        if (is_resource($this->procHandle)) {
            try{
                fwrite($pipes[0], $this->html);
                fclose($pipes[0]);
                return true;
            }
            catch(Exception $ex){
                
            }
        }
        return false;
    }
    public function join(){
        if($this->processPipes){
            try{
                $this->result = stream_get_contents($this->processPipes[1]);
                fclose($this->processPipes[1]);
                fclose($this->processPipes[2]);
                // It is important that you close any pipes before calling
                // proc_close in order to avoid a deadlock
                $return_value =  proc_close($this->procHandle);
                $this->done = !$return_value;
            }
            catch(Exception $e){
                $this->result = false;
                $this->done = true;
            }
        }else{
        $this->result = false;
        $this->done = true;
        }
        return $this->done;
    }
    public function isDone(){
        return $this->done;
    }
    public function getResult(){
        return $this->result;
    }
    
}
?>