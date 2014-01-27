<?php

require_once("classes/session.php");

Session::init();

//If we're here and not logged in, turn and run
/*
if(!Session::isLoggedIn()){
    include("login.php");
    return;
}
*/

//Pull the view from the request variable
$view = $_REQUEST[view];

//if request var is empty, pick a default view
if(empty($view)){
    $view = "register_player";
}

if((@include("acumen/$view.php")) == false){;
    include ("acumen/404.php");
}
?>

