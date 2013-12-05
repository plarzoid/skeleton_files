

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

        include("include/templates/default_header.html");
    }



    function close($noheader=false) {
        $this->displayFooter($noheader);
        $this->closeDatabase();
    }
    
    function displayFooter($noheader=false) {

        include("include/templates/default_footer.html");

    }
    
    function closeDatabase() {
    }
    
    function pageName() {
        return $_SERVER[PHP_SELF];
    }

    //HTML FORM Functions
    function register($varname, $type, $attributes=array()) {
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
            case "submit":    
                if(!Check::arrayKeysFormat(array("value"), $attributes)) return false;
                break;
            case "checkbox":
                if(!Check::arrayKeysFormat(array("on_text", "off_text"), $attributes)) return false;
                break;
            case "checkbox_array";
            case "radio":    
                if(!Check::arrayKeysFormat(array("get_choices_array_func"), $attributes)) return false;
                break;
            case "date_month_year":
                if(!Check::arrayKeysFormat(array("start_year", "get_choices_array_func"), $attributes)) return false;
                break;
            case "textbox":
            case "textarea":
            case "hidden":
            case "hiddenarray":
            case "password":
            case "select":
                break;
            case "counter":
                if(!Check::arrayKeysFormat(array("init"), $attributes)) return false;
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
        
        //set default value if one wasn't chosen already
        if($attributes[default_val]===NULL){$attributes[default_val]="";}
        
        //if we're using post, pull variable from post array
        if(array_key_exists("use_post", $attributes) && $attributes[use_post]) {
                    //use if getting array from html
            $_REQUEST[$varname] = $_POST[$varname];
        }

                //if variable wasn't returned, at all, set to default
                if(empty($_REQUEST[$varname]) && !is_numeric($_REQUEST[$varname])){
                    $_REQUEST[$varname] = $_POST[$varname] = $attributes[default_val];
                }

        if($type == "select" || $type == "radio") { //check_func is always validSelect
            $attributes[check_func] = "validSelect";
            $attributes[check_func_args] = array($attributes[get_choices_array_func], $attributes[get_choices_array_func_args]);
        }
        
        //$attributes[type] = $type;
        if ($type != "checkbox_array" && $type != "date_month_year" && $type != "file") {
            if((array_key_exists("use_post", $attributes) && $attributes[use_post])
                        || (array_key_exists("usepost", $attributes) && $attributes[usepost])) {
                $_POST[$varname] = trim($_POST[$varname]);
            } else { 
                $_REQUEST[$varname] = trim($_REQUEST[$varname]);
            }
        }

        if($type != "date_month_year") {
            global $$varname;
            $$varname = $_REQUEST[$varname]; //put form var into global scope
        } else {
            $vm = $varname . "_month";
            $vy = $varname . "_year";
            global $$vm;
            global $$vy;
            if ($attributes[usepost] || $attributes[use_post]) {
                $$vm = $_POST[$vm];
                $$vy = $_POST[$vy];
            } else {
                $$vm = $_REQUEST[$vm];
                $$vy = $_REQUEST[$vy];
            }
        }    

        if($type=="counter"){
            $attributes[value]=$_REQUEST[$varname];
            if(!$attributes[value]){
                $attributes[value]=$attributes[init];
            }
        }
        

        if(empty($attributes)){
            $attributes = array("type"=>$type);
        } else {
            if(!array_key_exists("type", $attributes)){
                $attributes["type"] = $type;
            }
        }

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

    function showVars(){
        print_r($this->vars);
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

        //extract the type from teh attributes array
        $type = $this->vars[$varname]["type"];
        unset($this->vars[$varname]["type"]);

        switch ($type) {
            
            case "checkbox":
            case "textbox": 
                //$this->printTextbox($varname, $this->vars[$varname], $disp_type);
                $this->printGenericInput($varname, $type, $this->vars[$varname], $disp_type);
                break;
            case "textarea": 
                $this->printTextarea($varname, $this->vars[$varname], $disp_type);
                break;
            case "hidden": 
                $this->printHidden($varname, $this->vars[$varname], $disp_type);
                break;
            case "hiddenarray": 
                $this->printHiddenArray($varname, $this->vars[$varname], $disp_type);
                break;
            case "file": 
                $this->printFile($varname, $this->vars[$varname], $disp_type);
                break;
            case "password": 
                $this->printPassword($varname, $this->vars[$varname], $disp_type);
                break;
            case "submit": 
                $this->printSubmit($varname, $this->vars[$varname], $disp_type);
                break;
            case "select": 
                $this->printSelect($varname, $this->vars[$varname], $disp_type);
                break;
            /*case "checkbox": 
                //$this->printCheckbox($varname, $this->vars[$varname], $disp_type);
                echo "here";
                $this->printGenericInput($varname, $type, $this->vars[$varname], $disp_type);
                break;*/
            case "checkbox_array": 
                $this->printCheckbxArray($varname, $this->vars[$varname], $disp_type);
                break;
            case "radio": 
                $this->printRadio($varname, $this->vars[$varname], $disp_type);
                break;
            case "date_month_year": 
                $this->printDateMonthYear($varname, $this->vars[$varname], $disp_type);
                break;
            case "counter": 
                $this->printCounter($varname, $this->vars[$varname], $disp_type);
                break;
                        
            // New HTML5 input types

            case "date":
                $this->printDate($varname, $this->vars[$varname], $disp_type);
                break;
            case "time":
                $this->printTime($varname, $this->vars[$varname], $disp_type);
                break;
            case "week":
                $this->printWeek($varname, $this->vars[$varname], $disp_type);
                break;
            case "month":
                $this->printMonth($varname, $this->vars[$varname], $disp_type);
                break;
            case "datetime":
                $this->printDatetime($varname, $this->vars[$varname], $disp_type);
                break;
            case "color":
                $this->printColor($varname, $this->vars[$varname], $disp_type);
                break;
            case "email":
                $this->printEmail($varname, $this->vars[$varname], $disp_type);
                break;
            case "search":
                $this->printSearch($varname, $this->vars[$varname], $disp_type);
                break;
            case "url":
                $this->printUrl($varname, $this->vars[$varname], $disp_type);
                break;
            case "tel":
                $this->printTel($varname, $this->vars[$varname], $disp_type);
                break;
            case "number":
                /*$this->printNumber($varname, $this->vars[$varname], $disp_type);
                break;*/
            case "range":
                //$this->printRange($varname, $this->vars[$varname], $disp_type);
                $this->printGenericInput($varname, $type, $this->vars[$varname], $disp_type);
                break;

            default: 
                return;
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
        } elseif ($attr[type] == "date_month_year") {
            if(array_key_exists("check_func", $attr) && $attr[check_func] != "none") {
                $func = $attr[check_func];
                $vmonth = $v . "_month";
                $vyear = $v . "_year";
                global $$vmonth;
                global $$vyear;
            
                $_REQUEST[$vmonth] = $$vmonth;
                $_REQUEST[$vyear] = $$vyear;
                $ret = false;
                $a = $attr[check_func_args];

                if(!is_array($a)) {
                    $ret = $check->$func($$vmonth, $$vyear);
                } else {
                    switch(count($a)) {
                        case 0: $ret = $check->$func($$vmonth, $$vyear); break;
                        case 1: $ret = $check->$func($$vmonth, $$vyear, $a[0]); break;
                        case 2: $ret = $check->$func($$vmonth, $$vyear, $a[0], $a[1]); break;
                        case 3: $ret = $check->$func($$vmonth, $$vyear, $a[0], $a[1], $a[2]); break;
                        default: $ret = $check->$func($$vmonth, $$vyear);
                                    }
                }
                
                if($ret) {
                    array_push($this->emessages, $attr[error_message] . $ret);
                    array_push($this->elocators, $v);
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
                if(    (    ($obj_type == false && !array_key_exists("obj_type", $attr))
                    ||    ($obj_type != false && $attr[obj_type] == $obj_type))
                    && $attr[setget] != "none"
                      ) {
                    if($attr[type] != "date_month_year" && $attr[type] != "file") {
                        $func = "set" . $attr[setget];
                        global $$v;
                        $obj->$func($$v);
                    } elseif($attr[type] == "file") {
                        $func = "set" . $attr[setget];
                        $farr_name = $v . "_file_array";
                        global $$v;
                        global $$farr_name;
                        $file_array = $$farr_name;
                        $obj->$func($$v);
                    } else {
                        $func = "set" . $attr[setget];
                        $vm = $v . "_month";
                        $vy = $v . "_year";
                        global $$vm;
                        global $$vy;
                        $obj->$func($$vm, "month");
                        $obj->$func($$vy, "year");
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
                if(isset($$cname)){//variable already exists, so don't worry about it
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

            } elseif($attr["type"] == "date_month_year"){
                $cmname = $v . "_month_choices";
                $cyname = $v . "_year_choices";

                $cfunc = $attr["get_choices_array_func"];
                                $ch = new Choices();
                                global $$cmname;
                global $$cyname;
                $a = $attr["get_choices_array_func_args"];
                
                if(!is_array($a)) $a = array();
                    switch(count($a)) {
                        default: $$cmname = $ch->$cfunc("month", $attr["start_year"]); 
                                 $$cmname = $ch->$cfunc("year", $attr["start_year"]);break;
                    }    
            }//if / elseif
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
        if(empty($lvar) && !is_numeric($lvar)){  //empty(0) == true, but we may want the number 0
            
            //If it's not there, set it to the default
            $lvar = $attr[default_val];
        }

        //if we're just showing data, do it and quit now
        if(strcmp($disp_type, "form")){ //returns 0 on true
            echo $lvar;
            return;
        }

        //else, generate the input form:

        //Start with the input container
        $str = '<div class="input_container">';


        //Use or make up a label for the input
        if($attrs[label]){
            $label = $attrs[label];
        } else {
            $label = "";
            $name_parts = preg_split("~_~", $v);
            foreach($name_parts as $part){
                $label .= ucfirst(strtolower($part));
                if(strcmp($part, end($name_parts))){
                    $label.= " ";
                }
            }
        }

        //detect units
        $units="";
        if($attrs[units]){
            $units = $attrs[units];
        }
        
        //Add the label
        $str.= '<div class="label"><label for="'.$v.'">'.$label.':</label></div>';


        //generate the input header
        $str.= '<div class="input"><input type="'.$type.'" name="'.$v.'" ';
       
        //Add the attributes
        foreach($attrs as $attr=>$value){
            switch($attr){
                case "default_val":
                case "label":
                case "units":
                    break;
                case "disabled":
                case "required":
                case "multiple":
                case "autofocus":
                case "novalidate":
                case "formnovalidate":
                    $str.= "$attr ";
                    break;
                default:
                    $str.= "$attr=\"$value\" ";
                    break;
            }
        }

        //Close the input
        $str.="> $units</div>";

        //Close the container
        $str.= "</div>";

        //Finally, echo the HTML
        echo $str;
    }


    function printTextbox($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        $lvar = stripslashes($$v);
        if(empty($lvar) && !is_numeric($lvar)){$lvar=$attr[default_val];}
        if(Check::notInt($attr[box_size])){
            $attr[box_size] = 82;
        }

        $disabled="";        
        
        if($attr[disabled]){
            $disabled="disabled";
        }

        if($disp_type == "form"){
            ?><input type=text size=<?=$attr[box_size]?> maxlength=255 name="<?=$v?>" value="<?=htmlspecialchars($lvar)?>" <?=$disabled?>><?
        } else {
            echo $lvar;
        }
    }

        function printPassword($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                $lvar = stripslashes($$v);
        if(empty($lvar)){$lvar=$attr[default_val];}
                if(Check::notInt($attr[box_size])){
                        $attr[box_size] = 82;
                }
                if($disp_type == "form"){
                        ?><input type=password size=<?=$attr[box_size]?> maxlength=255 name="<?=$v?>" value=""><?
                } else {
                        echo $lvar;
                }
        }

        function printFile($v, $attr, $disp_type = "form") {
        $farr_name = $v . "_file_array";
                global $$v;
        global $$farr_name;
        $file_array = $$farr_name;

        if(!is_array($file_array)) $file_array = array();

                if($disp_type == "form"){
                        if(array_key_exists("origname", $file_array)) {
                ?><input type=file name="<?=$v?>" value="<?=$file_array[origname]?>"><?
                    } else {
                            ?><input type=file name="<?=$v?>" value="<?=$file_array[name]?>"><?
            }
        } else {
            ?><a href="<?=($attr[filedir_webpath] . "/" . $file_array[name])?>"><?=$file_array[name]?></a><?
                }
        }

        function printTextarea($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                $lvar = stripslashes($$v);
                if(empty($lvar)){$lvar=$attr[default_val];}
        if($disp_type == "form"){
                    if(array_key_exists("rows", $attr)){
                $attr[rows] = 7;
            }
            if(array_key_exists("cols", $attr)){
                $attr[cols] = 80;
            }
                ?><textarea rows="<?=$attr[rows]?>" cols="<?=$attr[cols]?>" wrap=hard name="<?=$v?>"><?=$lvar?></textarea><?
                } else {
                        echo $lvar;
                }
        }

        function printHidden($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                $lvar = stripslashes($$v);

        if(empty($lvar)){$lvar=$attr[value];}

                if($disp_type == "form"){
                        ?><input type=hidden name="<?=$v?>" value="<?=htmlspecialchars($lvar)?>"><?
                } else {
                        //echo $lvar;
                }
        }

        function printHiddenArray($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = implode(',',$$v);
                $lvar = stripslashes(implode(',',$$v));

                if(empty($lvar)){$lvar=$attr[value];}

                if($disp_type == "form"){
            
                        ?><input type=hidden name="<?=$v?>" value="<?=htmlspecialchars($lvar)?>"><?
                } else {
                        //echo $lvar;
                }
        }

        function printSubmit($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                if($disp_type == "form") {
                    $str = "<div class=\"input_container\"><div class=\"submit\">";
                    $str.= "<input type=\"submit\" name=\"$v\" value=\"".$attr[value]."\">";
                    $str.= "</div></div>";

                    echo $str;
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
                        ?><select name="<?=$v?>"<?
                if($attr[reloading]){
                    ?> onChange="this.form.submit()"<?
                }
            ?>><?
            //echo "<pre>" . var_dump($choices) . "</pre>";
            foreach($choices as $c) {?>
                <option value="<?=$c[value]?>" <?if($_REQUEST[$v] == $c[value]) echo "SELECTED";?>
                ><?=$c[text];
            }?>
            </select><?
                } else {
                        foreach($choices as $c) {
                if($_REQUEST[$v] == $c[value]) {
                    if($args[lowercase] == true) echo strtolower($c[text]);
                    else echo $c[text];
                }
            }            
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
                        foreach($choices as $c) {?>
                                <input type="radio" name="<?=$v?>" value="<?=$c[value]?>" <?if($attr[reloading]){?>onClick="this.form.submit()"<?}?> <?if($_REQUEST[$v] == $c[value]) echo "CHECKED";?>
                                ><?=$c[text]?><br/><?;
                        }
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
//var_dump($actual_var);
        if($actual_var=="Y"){return true;}        
        return false;
    }


       function printCheckbox($v, $attr, $disp_type = "form", $key = -1) {
                if($key != -1) {
            global $$v;
                    $_REQUEST[$v] = $$v;
            $actual_var = $_REQUEST[$v][$key];
            $actual_name = $v . "[" . $key . "]";
        } else {
            global $$v;
            $_REQUEST[$v] = $$v;
            $actual_var = $_REQUEST[$v];
            $actual_name = $v;
        }
                
        if($disp_type == "form"){//           "Y"                    <?=$actual_name? >
                        ?><input type=checkbox value="Y" name="<?=$actual_name?>"
            <?if ($actual_var == "Y") 
                echo "CHECKED";?> > <?=$attr[on_text]?><?
        } else {
            if($actual_var = "Y") {
                echo $attr[on_text];
            } else {
                echo $attr[off_text];
            }
                }
        }

       function printCheckboxArray($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                $vchoices = $v . "_choices";

                global $$vchoices;
                $choices = $$vchoices;

        $str="";
        
                if($disp_type == "form"){
                    $counter=0;
            foreach($choices as $c) {
                $attr[on_text] = $c[text];
                $this->printCheckbox($v, $attr, "form", $c[value]);
                ?><br/><?
            }
        } else {
            $num_yes = 0;
            foreach($choices as $c){
                if($_REQUEST[$v][$c[value]] == "Y") {
                    if($num_yes++ >= 1) {
                        $str .= ", ";
                    }
                    $str .= $c[text];
                }
            }
            if($str=="") {
                $str = "None";
            }
            echo $str;
        }
        }


    function printDateMonthYear($v, $attr, $disp_type="form") {
        if($disp_type == "form") {
            $this->printSelect($v."_month", array("get_choices_array_func"=>$attr[get_month_choices_array_func]));
            $this->printSelect($v."_year", array("get_choices_array_func"=>$attr[get_year_choices_array_func],
                        "get_choices_array_func_args"=>$attr[get_year_choices_array_func_args]));
        } else {
            $this->printSelect($v."_month", array("get_choices_array_func"=>$attr[get_month_choices_array_func]), "text");
            echo " ";
                        $this->printSelect($v."_year", array("get_choices_array_func"=>$attr[get_year_choices_array_func],
                                                "get_choices_array_func_args"=>$attr[get_year_choices_array_func_args]), "text");
        

        }
    }


        function printCounter($v, $attr, $disp_type = "form") {

                if($disp_type == "form"){
                        ?><input type=hidden name="<?=$v?>" value="<?=htmlspecialchars($attr[value])?>"><?
                } 
        }

    function getCounter($v){
                foreach($this->vars as $var=>$attr){
                        if($var==$v && $attr[type]="counter"){
                                return $this->vars[$v][value];
                        }
                }
                return false;
        }

    function setCounter($v, $value){
        foreach($this->vars as $var=>$attr){
            if($var==$v && $attr[type]="counter"){
                $this->vars[$v][value]=$value;
                return true;
            }
        }
        return false;
    }

        function resetCounter($v){
                foreach($this->vars as $var=>$attr){
                        if($var==$v && $attr[type]="counter"){
                                $this->vars[$v][value]=$attr[init];
                                return $attr[init];
                        }
                }
                return false;
        }

    function registerSegment($segment, $n){
        if(!is_array($segment)){$segment = NULL;}
    
        //hidden variable which will retain fidelity of the segment ids
                $this->register("testid".$n, "hidden", array("use_post"=>1, "default_val"=>$segment['id']));

                //textbox for user-defined test name
                $this->register("testname".$n, "textbox", array("use_post"=>1, "box_size"=>25, "default_val"=>$segment['name']));

                //dropdown for type of test
                $this->register("testtype".$n, "select", array("use_post"=>1, "get_choices_array_func"=>"getTestTypeChoices",
                                                        "get_choices_array_func_args"=>0, "reloading"=>1,
                                                                "default_val"=>$segment['test_type']));

                //dropdown for call medium (SIP, DAHDI, etc)  Choices set in choices class
                $this->register("technology".$n, "select", array("use_post"=>1, "get_choices_array_func"=>"getTechnologyChoices",
                                                        "get_choices_array_func_args"=>0, "reloading"=>1,
                                                                "default_val"=>$segment['channel_type']));
                //dropdown for channel number
                $this->register("channel".$n, "select", array("use_post"=>1, "get_choices_array_func"=>"getAvailableChannels",
                                                        "get_choices_array_func_args"=>array($this->getVar("technology".$n), Session::userid()),
                                                                "default_val"=>$segment['channel']));
                //textbox for number of calls
                $this->register("numcalls".$n, "textbox", array("use_post"=>1, "box_size"=>5, "default_val"=>1,
                                                                "default_val"=>$segment['number_of_calls']));

                //textbox for phone number
                $this->register("phonenumber".$n, "textbox", array("use_post"=>1, "box_size"=>15,
                                                                "default_val"=>$segment['phone_number']));

                //textbox for callerid
                $this->register("callerid".$n, "textbox", array("use_post"=>1, "box_size"=>15,
                                                                "default_val"=>$segment['callerid']));

                //textbox for pause between calls (singular, or range), (sec)
                $this->register("pausetime".$n, "textbox", array("use_post"=>1, "box_size"=>15,
                                                                "default_val"=>$segment['call_rate']));


                //dropdown for directory holding wav files
                $this->register("wavdir".$n, "select", array("use_post"=>1, "get_choices_array_func"=>"getWavDirectoryChoices",
                                                        "get_choices_array_func_args"=>array($this->getVar("testtype".$n)), "reloading"=>1,
                                                        "default_val"=>$segment['wav_dir']));

                //dropdown for specific files inside wav file directory
                $this->register("wavfile".$n, "select", array("use_post"=>1, "get_choices_array_func"=>"getWavFileChoices",
                                                        "get_choices_array_func_args"=>array($this->getVar("testtype".$n), $this->getVar("wavdir".$n)),
                                                        "default_val"=>$segment['wav_file']));

         //drop-down for DTMF
                $this->register("metadata_type".$n, "select", array("use_post"=>1, "get_choices_array_func"=>"getDTMFTestChoices",
                                                                "get_choices_array_func_args"=>0, "default_val"=>$segment['metadata_type'],
                                                                "reloading"=>1));
                //textbox for metadata
                $this->register("metadata".$n, "textbox", array("use_post"=>1, "box_size"=>25, "default_val"=>$segment['metadata']));

                //dtmf stats
                $this->register("dtmf_tone_on".$n, "textbox", array("use_post"=>1, "box_size"=>10, "default_val"=>$segment['dtmf_tone_on']));
                $this->register("dtmf_tone_off".$n, "textbox", array("use_post"=>1, "box_size"=>10, "default_val"=>$segment['dtmf_tone_off']));
                $this->register("dtmf_amplitude".$n, "textbox", array("use_post"=>1, "box_size"=>10, "default_val"=>$segment['dtmf_amplitude']));
                $this->register("dtmf_freq_err".$n, "textbox", array("use_post"=>1, "box_size"=>10, "default_val"=>$segment['dtmf_freq_error']));
                $this->register("dtmf_twist".$n, "textbox", array("use_post"=>1, "box_size"=>10, "default_val"=>$segment['dtmf_twist']));

                //textbox for start delay (sec)
                $this->register("startdelay".$n, "textbox", array("use_post"=>1, "box_size"=>25, "default_val"=>$segment['start_delay']));

                //textarea for comments
                $this->register("comments".$n, "textarea", array("use_post"=>1, "cols"=>1, "rows"=>8, "default_val"=>$segment['comments']));
    }
}

?>
