<?php
include("autoload.php");
$APP = new DonQuishoot\Framework\Core\App("view","App\Models\User");
if ($argv){
    echo "\r\n";
    if ($argv[1]=="refresh"){
        refreshModel();
    }
    else if ($argv[1]=="reload"){
        reloadModel();
    }
    if (isset($argv[2])&&$argv[2] == "--seed"){
        seed();
    }
    echo "\r\n";

}

function refreshModel(){
    runInModel("refreshTable");

}
function reloadModel(){
  
    runInModel("reloadTable");

}
function seed(){
    $user = DonQuishoot\Framework\Models\User::make("familietoernooi@donquishoot.nl","daanisgek");
    $user->elevation = 1;
    $user->save();
   
}
function runInModel($method){
    foreach(get_declared_classes() as $class)
    {
        if(is_subclass_of($class,"DonQuishoot\Framework\Core\Model")){
           
           echo $class::{$method}();
        }
    }
}