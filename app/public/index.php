<?php
use App\Controllers\ViewController;
require_once("../../autoload.php");
$APP = new Core\App("view","App\Models\User");
if (isset($_SESSION['user'])&& $_SESSION['user'] != null){
    $_SESSION['user'] = App\Models\User::fromGUID($_SESSION['user']->guid);
}
if ($APP->route()){ exit();}