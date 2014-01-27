<?php

require_once("session.php");
require_once("check.php");
require_once("choices.php");

class Page {

    var $sysClass;
    var $authLevel;
    var $vars;

    public function Page($authentication_level="Public", $pageid=false, $title=false) {
        Session::init();

        //set the webroot
        $this->servername = $_SERVER[HTTP_HOST];

        if (preg_match("/\/~(\w+)\//", $_SERVER[PHP_SELF], $matches)) {
            $this->root = "/~" . $matches[1] . "/";
        } else {
            //$this->root = "/";
        }

        //set the vars array to empty
        $vars = array();

        //set internal authority level
        switch((string)$authentication_level) {
            case "Public"    : 
            case "public"    : $this->authLevel="PUBLIC";
                break;
            case "Admin"    :
            case "admin"    : $this->authLevel="ADMIN";
                break;
            default: $this->authLevel=$authentication_level;
                break;
        }//switch($authentication_level) {

        //authentication checking
        if($this->isNotAuthorized()){
            $this->startTemplate();
            include("include/templates/authError.html");    
            $this->displayFooter();
            exit;
        }
    }

    function isNotAuthorized(){
        //returns true if user is NOT authorized
        //returns false if they are

        //if, a public page, always authorized
        if(!strcmp($this->authLevel, "PUBLIC")){return false;}//strcmp returns 0 on a match

        //for the rest of the tests, a user must be logged in, so stop now if we're not logged in
        if(!Session::isLoggedIn()){return true;}
    
        //if page is admin page, check for admin logged in
        if(!strcmp($this->authLevel,"ADMIN")){
            if(Session::isNotAdmin()){
                //echo "You must be an administrator to view this page!";
                return true;
            }
        }


        //at this point, it's not a public or admin page,
        //so check user's auth_level vs page's authLevel
        if(!Session::isAuthorized($this->authLevel)){
            //echo "You are not authorized to view this page!";
            return true;
        }

        //user fell through all the traps above, so is authorized.
        return false;
    }

    function getWebRoot(){
        return $this->root;
    }

    function printView() {
        if ($this->print == "Y") return true;
        else return false;
    }

    function startTemplate($meta=NULL) {

        include("templates/default_header.html");
    }

    function doTabs(){

        $tabs = array(
            "Register Player"=>"register_player",
            "View Player Profile"=>"view_player",
            "Report Game"=>"report_game",
            "Redeem Skulls"=>"redeem",
            "Leaderboard"=>"leaderboard",
            "Event Achievements"=>"batch_processing",
            "Software Feedback"=>"feedback"
            );

        
        if(Session::isAdmin()){
            $admin_tabs = array(
                "Manage Users"=>"manage_users",
                "General Configuration"=>"general_config",
                "Achievement Configuration"=>"achievement_config"
            );

        }

        $view = $_REQUEST[view];

        include("templates/default_aside.html");
    }


    function close($noheader=false) {
        $this->displayFooter($noheader);
        $this->closeDatabase();
    }
    
    function displayFooter($noheader=false) {

        include("templates/default_footer.html");

    }
    
    function closeDatabase() {
    }
    
    function pageName() {
        return $_SERVER[PHP_SELF];
    }

    //HTML FORM Functions
    function register($varname, $type, $attributes=array()) {

        //first, first, add the damn use_post
        $attributes[use_post]=1;

        //first, check that $type and $attributes are set up correctly
        //optional attr args: check_func (can be "none")
        //              check_func_args (additional args passed to ceck_func)
        //              error_message (required if there is a check_func)
        //              setget (part of set and get functions after "set" or "get"
        //              on_text, off_text (used only for "text view of checkbos)
        //              value, used for submit buttons
        //              filedir (used only for file type)

        switch($type) {
            case "file":     
                if(!Check::arrayKeysFormat(array("filedir", "filedir_webpath"), $attributes)) return false;
                if(!preg_match("/\/$/", $attributes[filedir])) $attributes[filedir] .= "/";
                break;
            case "reset":
            case "submit":    
                if(!Check::arrayKeysFormat(array("value"), $attributes)) return false;
                break;
            case "checkbox":
                if(!Check::arrayKeysFormat(array("on_text", "off_text"), $attributes)) return false;
                $attributes[value]=1;
                break;
            case "checkbox_array";
            case "radio":    
                if(!Check::arrayKeysFormat(array("get_choices_array_func"), $attributes)) return false;
                break;
            case "textbox":
            case "textarea":
            case "hidden":
            case "password":
            case "select":
                break;

            //New HTML5 input types

            case "tel":
                break;
            case "number":
            case "range":
                if(!Check::arrayKeysFormat(array("min", "max", "step"), $attributes)) return false;
                break;
            case "date":
            case "time":
            case "week":
            case "month":
            case "datetime":
            case "color":
            case "email":
            case "search":
            case "url":
                break;
            default:
                return false;
                break;
        }
        
        //set default value to the returned value if one wasn't specified manually
        if($attributes[default_val]===NULL){
            if(array_key_exists("use_post", $attributes) && $attributes[use_post]){
                $attributes[default_val] = $_REQUEST[$varname] = $_POST[$varname];
            } else {
                $attributes[default_val] = $_POST[$varname] = $_REQUEST[$varname];
            }
        }

        if($type == "select" || $type == "radio") { //check_func is always validSelect
            $attributes[check_func] = "validSelect";
            $attributes[check_func_args] = array($attributes[get_choices_array_func], 
                                                 $attributes[get_choices_array_func_args]);
        }
        
        //$attributes[type] = $type;
        if ($type != "checkbox_array" && $type != "file") {
            if((array_key_exists("use_post", $attributes) && $attributes[use_post])
                        || (array_key_exists("usepost", $attributes) && $attributes[usepost])) {
                $_POST[$varname] = trim($_POST[$varname]);
            } else { 
                $_REQUEST[$varname] = trim($_REQUEST[$varname]);
            }
        }

        //put form var into global scope
        global $$varname;
        $$varname = $_REQUEST[$varname];

        //Store type into the attributes array for use later
        if(empty($attributes)){
            $attributes = array("type"=>$type);
        } else {
            if(!array_key_exists("type", $attributes)){
                $attributes["type"] = $type;
            }
        }

        //Add the form var to the stored list of form vars
        $this->vars[$varname] = $attributes;

        return true;
    }

    function unregister($varname) {
        global $$varname;
        unset($$varname);
        unset($this->vars[$varname]);
    }

    function submitIsSet($submitvar_name) {
        global $$submitvar_name;
        if (array_key_exists($submitvar_name, $this->vars) && 
           ($$submitvar_name == $this->vars[$submitvar_name][value])) 
            return true;
        
        return false;
    }

    function setDisplayMode($mode) {
        $this->disp_mode = $mode;
    }

        
    //set disp_type to either "form" or "success"
    function displayVar($varname, $disp_type = false, $args = array()) {
        if ($disp_type == false) {
            if (!$this->disp_mode) {
                $disp_mode = "form";
            } else {
                $disp_type = $this->disp_mode;
            }
        }

        //extract the type from the attributes array
        $type = $this->vars[$varname]["type"];
        unset($this->vars[$varname]["type"]);

        switch ($type) {
            
            //Special cases
            case "hidden": 
                $this->printHidden($varname, $this->vars[$varname], $disp_type);
                break;
            case "submit": 
                $this->printSubmit($varname, $this->vars[$varname], $disp_type);
                break;
            case "select": 
                $this->printSelect($varname, $this->vars[$varname], $disp_type);
                break;
            case "checkbox_array": 
                $this->printCheckboxArray($varname, $this->vars[$varname], $disp_type);
                break;
            case "radio": 
                $this->printRadio($varname, $this->vars[$varname], $disp_type);
                break;
            case "reset":
                $this->printReset($varname, $this->vars[$varname], $disp_type);
                break;
            case "textarea":
                $this->printTextarea($varname, $this->vars[$varname], $disp_type);
                break;
            //Everything else
            default: 
                $this->printGenericInput($varname, $type, $this->vars[$varname], $disp_type);
                break;
        }
    }

    function checkOneVar($v) {
        if(!is_array($this->emessages)) $this->emessages = array();
        if(!is_array($this->elocators)) $this->elocators = array();

        $start_count = count($this->elocators);
        $check = new Check();
        $attr = $this->vars[$v];
        global $$v;
        $_REQUEST[$v] = $$v;
        
        if($attr[type] == "checkbox_array") {
            $ch = new Choices();
            $func = $attr[get_choices_array_func];
            $a = $attr[get_choices_array_func_args];

            if(!is_array($a)) {
                $choices = $ch->$func();
            } else {
                switch(count($a)) {
                    case 0: $choices = $ch->$func(); break;
                    case 1: $choices = $ch->$func($a[0]); break;
                    case 2: $choices = $ch->$func($a[0], $a[1]); break;
                    case 3: $choices = $ch->$func($a[0], $a[1], $a[2]); break;
                    default: $choices = $ch->$func();
                }
            }
        
            foreach ($choices as $c) {
                if($_REQUEST[$v][$c[value]] != "Y") {
                    $_REQUEST[$v][$c[value]] = "N";
                }
            }
        } elseif($attr[type] == "file") {
            if(array_key_exists("check_func", $atr) && $attr[check_func] != "none") {
                $func = $attr[check_func];
                $farr_name = $v . "_file_array";
                global $$v;
                global $$farr_name;

                $ret = false;
                $a = $attr[check_func_args];

                if(!is_array($a)) {
                    $ret = $check->$func($$farr_name);
                } else {
                    switch(count($a)) {
                        case 0: $ret = $check->$func($$farr_name); break;
                        case 1: $ret = $check->$func($$farr_name, $a[0]); break;
                        case 2: $ret = $check->$func($$farr_name, $a[0], $a[1]); break;
                        case 3: $ret = $check->$func($$farr_name, $a[0], $a[1], $a[2]); break;
                        default: $ret = $check->$func($$farr_name);
                    }
                }

                if($ret) {
                    array_push($this->emessages, $attr[error_message] . $ret);
                    array_push($this->elocators, $v);
                }    
    
            }
        } else {
            if(array_key_exists("check_func", $attr) && $attr[check_func] != "none") {
                $func = $attr[check_func];
                $ret = false;
                
                if(!is_array($attr[check_func_args])) {
                    $ret = $check->$func($_REQUEST[$v]);
                } else {
                    $a = $attr[check_func_args];
                    switch(count($a)) {
                        case 0: $ret = $check->$func($_REQUEST[$v]); break;
                        case 1: $ret = $check->$func($_REQUEST[$v], $a[0]); break;
                        case 2: $ret = $check->$func($_REQUEST[$v], $a[0], $a[1]); break;
                        case 3: $ret = $check->$func($_REQUEST[$v], $a[0], $a[1], $a[2]); break;
                        case 4: $ret = $check->$func($_REQUEST[$v], $a[0], $a[1], $a[2], $a[3]); break;
                        default: $ret = $check->$func($_REQUEST[$v]);
                    }
                }
                    
                if($ret) {
                    array_push($this->emessages, $attr[error_message] . $ret);
                    array_push($this->elocators, $v);
                }
            }
        }
        
        $$v = $_REQUEST[$v];
        if($start_count < count($this->elocators)) return false;
        return true;
    }

    function checkVars($opj_type = false) {
        if(!is_array($this->emessages)) $this->emessages = array();
        if(!is_array($this->elocators)) $this->elocators = array();
        
        $check = new Check();

        foreach($this->vars as $v => $attr) {
            if($obj_type == false || ($obj_type != false && $obj_type == $attr[obj_type])) {
                $this->checkOneVar($v);
            }
        }

        return array("error_messages" => $this->emessages, "error_locators" => $this->elocators);
    }

    function setVars(&$obj, $obj_type = false) {
        foreach($this->vars as $v => $attr) {
            if(array_key_exists("setget", $attr)) {
                if((($obj_type == false && !array_key_exists("obj_type", $attr))
                    ||    ($obj_type != false && $attr[obj_type] == $obj_type))
                    && $attr[setget] != "none"
                      ) {
                    if($attr[type] != "file") {
                        $func = "set" . $attr[setget];
                        global $$v;
                        $obj->$func($$v);
                    } else {
                        $func = "set" . $attr[setget];
                        $farr_name = $v . "_file_array";
                        global $$v;
                        global $$farr_name;
                        $file_array = $$farr_name;
                        $obj->$func($$v);
                    }
                }
            }
        }
    }

    function getVars(&$obj, $obj_type = false) {

        foreach($this->vars as $v => $attr) {
            if(array_key_exists("setget", $attr)) {
                if((($obj_type == false && !array_key_exists("obj_type", $attr))
                    || ($obj_type != false && $attr[obj_type] == $obj_type))
                    && $attr[setget] != "none"
                    ) {
                    
                    if($attr[type] != "date_month_year" && $attr[type] != "file") {
                        $func = "get" . $attr[setget];
                        global $$v;
                        $obj->$func($$v);
                        $_REQUEST[$v] = $$v;
                    } elseif($attr[type] == "file") {
                        $func = "get" . $attr[setget];
                        $farr_varname = $v . "_file_array";
                        global $$v;
                        global $$farr_varname;
                        $file_array = array("name"=>$obj->$func());
                        $$farr_varname = $file_array;
                        $$v = $file_array[name];
                    } else {
                        $func = "get" . $attr[setget];
                        $vm = $v . "_month";
                        $vy = $v . "_year";
                        global $$vm;
                        global $$vy;
                        $$vm = $obj->$func("month");
                        $$vy = $obj->$func("year");
                        $_REQUEST[$vm] = $$vm;
                        $_REQUEST[$vy] = $$vy;
                    }
                }
            }
        }
    }
                        
    function errText() {
        if(is_array($this->emessages)) {
            echo "<font class=error>";
            foreach($this->emessages as $e) {
                echo $e . "<br/>";
            }
            echo "</font>";
        }
    }

    function getVar($v) {
        global $$v;
        $_REQUEST[$v] = $$v;
        return stripslashes($$v);
    }


    function getChoices() {
        foreach ($this->vars as $v=>$attr) {
            if($attr["type"]=="select" || $attr["type"]=="checkbox_array" || $attr["type"]=="radio"){

                if(strlen($attr["choices_array_var"]) > 0){
                    $cname = $attr["choices_array_var"];
                } else {
                    $cname = $v . "_choices";
                }
                if(isset($$cname)){//variable already exists, let's clear it??
                    continue;
                }

                $cfunc = $attr["get_choices_array_func"];
                $ch = new Choices();
                global $$cname;
                $a = $attr["get_choices_array_func_args"];
        
                if(!is_array($a)) $a = array();
                switch(count($a)) {
                    case 0: $$cname = $ch->$cfunc(); break;
                    case 1: $$cname = $ch->$cfunc($a[0]); break;
                    case 2: $$cname = $ch->$cfunc($a[0], $a[1]); break;
                    case 3: $$cname = $ch->$cfunc($a[0], $a[1], $a[2]); break;
                    case 4: $$cname = $ch->$cfunc($a[0], $a[1], $a[2], $a[3]); break;
                    default: $$cname = $ch->$cfunc();
                }
            }//if
        }//foreach
    }//function


    function printGenericInput($v, $type, $attrs, $disp_type = "form"){
        //Pull out the requested variable's registered data from teh global variable space
        global $$v;

        //Set the REQUEST variable to the global array
        $_REQUEST[$v] = $$v; //-- I don't know what this does, actually...

        //Create the simple form, for printing
        $lvar = stripslashes($$v);

        //Check for variable existence...
        if(($lvar===null) || (empty($lvar) && !is_numeric($lvar))){  //empty(0) == true, but we may want the number 0
            
            //If it's not there, set it to the default
            $lvar = $attr[default_val];
        }

        //if we're just showing data, do it and quit now
        if(strcmp($disp_type, "form")){ //returns 0 on true
            echo $lvar;
            return;
        }

        //else, generate the input form:

        //Use or make up a label for the input
        if($attrs[label]){
            $label = $attrs[label];
        } else {
            $label = $this->generateLabel($v);
        }

        //detect units
        $units="";
        if($attrs[units]){
            $units = $attrs[units];
        }
        
        //generate the input header
        $str.= '<input type="'.$type.'" name="'.$v.'" ';
       
        if(!strcmp($type, "checkbox")){
            $str.= "value=\"1\" ";
        }

        //Add the attributes
        foreach($attrs as $attr=>$value){
            switch($attr){

                //Skip these
                case "use_post":
                case "label":
                case "units":
                case "on_text":
                case "off_text":
                case "check_func":
                case "check_func_args";
                case "get_choices_array_func":
                case "get_choices_array_func_args":
                    break;

                //Boolean attributes
                case "disabled":
                case "required":
                case "multiple":
                case "autofocus":
                case "novalidate":
                case "formnovalidate":
                    $str.= "$attr ";
                    break;

                case "default_val":
                    if(!(($value===null) || (empty($value) && !(is_numeric($value))))){
                        if(!strcmp($type, "checkbox")){
                            if(!strcmp($value, "1")){
                                $str.= "CHECKED ";
                            }
                        } else {
                            $str.= "value=\"$value\" ";
                        }
                    }
                    break;
                
                //Everything else
                default:
                    $str.= "$attr=\"$value\" ";
                    break;
            }
        }

        //Close the input
        $str.="> $units";

        //Finally, echo the HTML
        $this->printComplexInput($v, $label, $str);
    }

    function printComplexInput($name, $label, $input){
        $str = "<div class=\"input_container\">";
        $str.=     "<div class=\"label\"><label for=\"$v\">$label:</label></div>";
        $str.=     "<div class=\"input\">$input</div>";
        $str.= "</div>";

        echo $str;
    }

    function generateLabel($v){
        $label = "";
        $name_parts = preg_split("~_~", $v);
        foreach($name_parts as $part){
            $label .= ucfirst(strtolower($part));
            if(strcmp($part, end($name_parts))){
                $label.= " ";
            }
        }
        return $label;
    }

    function printSimpleInput($input){
        echo "<div class=\"input_container\"><div class=\"simple\">$input</div></div>";
    }

    function printHidden($v, $attr, $disp_type = "form"){
        if($disp_type == "form"){
            echo "<input type=\"hidden\" name=\"$v\" value=\"".$attr[value]."\">";
        }
    }
    /* 
    function printHidden($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        $lvar = stripslashes($$v);

        if(empty($lvar)){$lvar=$attr[value];}

        if($disp_type == "form"){
            $str = "<input type=\"hidden\" name=\"$v\" value=\"".htmlspecialchars($lvar)."\">";
            //$this->printSimpleInput($str);
            echo $str;
        } else {
            //echo $lvar;
        }
    }
    */
    function printSubmit($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if($disp_type == "form") {
            $str = "<input type=\"submit\" name=\"$v\" value=\"".$attr[value]."\">";
            $this->printSimpleInput($str);
        }
    }

    function printReset($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if($disp_type == "form") {
            $str = "<input type=\"reset\" value=\"".$attr[value]."\">";
            $this->printSimpleInput($str);
        }
    }

    function printSelect($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if(strlen($attr[choices_array_var]) > 0) {
            $vchoices = $attr[choices_array_var];
        } else {
            $vchoices = $v . "_choices";
        }

        global $$vchoices;
        $choices = $$vchoices;

        if($disp_type == "form"){

            //Build the open select tag
            $reloading = "";
            
            if($attr[reloading]){
                $reloading.= " onChange=\"this.form.submit()\"";
            }
            
            if($attr[multiple]){
                $reloading.=" multiple";
            }

            $str = "<select name=\"$v\"$reloading>";

            if($_REQUEST[$v]){
                $selected_option = $_REQUEST[$v];
            } else {
                $selected_option = $attr[default_val];
            }

            //Toss in the choices
            foreach($choices as $c) {

                $selected = "";
                if($selected_option == $c[value]) $selected = " SELECTED";
            
                $str.= "<option value=\"".$c[value]."\"$selected>".$c[text]."</option>";
            }

            //Close the select tag
            $str.= "</select>";
           
            if($attr[label]){
                $label = $attr[label];
            } else {
                $label = $this->generateLabel($v);
            }

            $this->printComplexInput($v, $label, $str);

        } else {
            foreach($choices as $c) {
                if($_REQUEST[$v] == $c[value]) {
                    if($args[lowercase] == true) echo strtolower($c[text]);
                    else echo $c[text];
                }
            }            
        }
    }        

    function printTextarea($v, $attr, $disp_type = "form"){
        global $$v;
        $_REQUEST[$v] = $$v;
        
        if($_REQUEST[$v]){
            $lvar = $_REQUEST[$v];
        } else {
            $lvar = $attr[default_val];
        }

        if($disp_type == "form"){

            //Build the input String
            $str = "<textarea name=\"$v\"";

            if($attr[rows]){
                $str.= " rows=\"".$attr[rows]."\"";
            }

            if($attr[cols]){
                $str.= " cols=\"".$attr[cols]."\"";
            }

            if($attr[placeholder]){
                $str.= " placeholder=\"".$attr[placeholder]."\"";
            }

            $str.= ">";

            //$str.=$lvar;

            $str.= "</textarea>";

            //create the Label
            if($attr[label]){
                $label = $attr[label];
            } else {
                $label = $this->generateLabel($v);
            }

            //Print it

            $this->printComplexInput($v, $label, $str);
        } else {
            echo $lvar;
        }
    }


    function printRadio($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if(strlen($attr[choices_array_var]) > 0) {
            $vchoices = $attr[choices_array_var];
        } else {
            $vchoices = $v . "_choices";
        }

        global $$vchoices;
        $choices = $$vchoices;

        if(!$_REQUEST[$v]){$_REQUEST[$v]=$choices[0][value];}

        if($disp_type == "form"){
            $str = "";
            foreach($choices as $c) {
                $reloading = "";
                if($attr[reloading]) $reloading = " onClick=\"this.form.submit()\"";

                $checked = "";
                if($_REQUEST[$v] == $c[value]) $checked = " CHECKED";
                
                $str.= "<input type=\"radio\" name=\"$v\" value=\"".$c[value]."\"$reloading$checked>".$c[text];
            }

            if($attr[label]){
                $label = $attr[label];
            } else {
                $label = $this->generateLabel($v);
            }

            $this->printComplexInput($v, $label, $str);
        } else {
            foreach($choices as $c) {
                if($_REQUEST[$v] == $c[value]) {
                    echo $c[text];
                }
            }
        }
    }

    function isChecked($v){
        global $$v;
        $_REQUEST[$v] = $$v;
        $actual_var = $_REQUEST[$v];
        
        if($actual_var=="Y"){return true;}        
        return false;
    }

}

?>
