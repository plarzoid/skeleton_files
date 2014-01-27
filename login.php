<?php

require_once("classes/page.php");

$page = new Page();

//remove any possibility of being previously logged in
if(Session::isLoggedIn()){
    Session::logout();
    $page->unregister("uname");
    $page->unregister("upass");
    $page->unregister("login_submit");
}


/* -- Gather inputs -- */
//Register vars for the default log in action
$page->register("user_name", "textbox", array("use_post"=>1, "size"=>20, "required"=>true));
$page->register("password", "password", array("use_post"=>1, "size"=>20, "required"=>true));
$page->register("login_submit", "submit", array("value"=>"Log In!", "use_post"=>1));

//set default template
$template="templates/login.html";
$page->setDisplayMode("form");
$form_action = $_SERVER[PHP_SELF];
$form_method = "post";


/* -- Process the inputs -- */
//User clicked the Log In button..
if($page->submitIsSet("login_submit")) {
    
    //assume failure to login
    $success = 0;

    //get username / password from $_POST
    $username = $page->getVar("user_name");
    $password = $page->getVar("password");

    //attempte to authenticate
    $user = Session::authenticate($username, md5($password));

    //if user was successfully authenticated, echo some data
    if($user){
	$template="templates/welcome.html";
	$success = 1; 	
    } else {
	//generate error string on failure to login
        $login_error = "Invalid Username or Password.  Please try again!";
    }
}


/* -- Display the Page -- */
$page->startTemplate();

//navigation bar if they're logged in
if(Session::isLoggedIn()){
    $page->doTabs();
}

include($template);
$page->close();

?>
