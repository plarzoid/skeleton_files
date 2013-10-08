
<?php

require_once("session.php");
require_once("check.php");
//require_once("choices.php"); //included in check

class Page {

	var $sysClass;
	var $vars;

	public function Page($authentication_level="Public", $pageid=false, $title=false) {
		Session::init();

		//set the webroot
                //$this->servername = $_SERVER[HTTP_HOST];
                if (preg_match("/\/~(\w+)\//", $_SERVER['PHP_SELF'], $matches)) {
                        $this->root = "/~" . $matches[1] . "/";
                } else {
                        $this->root = "/";
                }

		//set the vars array to empty
                $vars = array();

		//set internal authority level
		switch((string)$authentication_level) {
			case "Public"	: 
			case "public"	: $this->authLevel="PUBLIC";
				break;
			case "Admin"	:
			case "admin"	: $this->authLevel="ADMIN";
				break;
			default: $this->authLevel=$authentication_level;
				break;
		}//switch($authentication_level) {

		//authentication checking
		/*if($this->isNotAuthorized()){
			$this->startTemplate();
			include("include/templates/authError.tpl");	
			$this->displayFooter();
			exit;
		}*/
	}

	/*function isNotAuthorized(){
		//returns true if user is NOT authorized
		//returns false if they are

		//if, a public page, always authorized
		if(!strcmp($this->authLevel, "PUBLIC")){return false;}//strcmp returns 0 on a match

		//for the rest of the tests, a user must be logged in, so stop now if we're not logged in
		//if(!Session::isLoggedIn()){return true;}
	
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
	}*/

	function getWebRoot(){
		return $this->root;
	}

	function printView() {
		if ($this->print == "Y") return true;
		else return false;
	}

	function startTemplate($noheader=false) {

		include("include/templates/default_header.tpl");
	}



	function close($noheader=false) {
		$this->displayFooter($noheader);
		$this->closeDatabase();
	}
	
	function displayFooter($noheader=false) {

		include("include/templates/default_footer.tpl");

	}
	
	function closeDatabase() {
	}
	
	function pageName() {
		return $_SERVER['PHP_SELF'];
	}

	//HTML FORM Functions
	function register($varname, $type, $attributes=array()) {
		//first, check that $type and $attributes are set up correctly
		//optional attr args: check_func (can be "none")
		//		      check_func_args (additional args passed to ceck_func)
		//		      error_message (required if there is a check_func)
		//		      setget (part of set and get functions after "set" or "get"
		//		      on_text, off_text (used only for "text view of checkbos)
		//		      value, used for submit buttons
		//		      filedir (used only for file type)

		switch($type) {
			case "file": 	
				if(!Check::arrayKeysFormat(array("filedir", "filedir_webpath"), $attributes)) return false;
				if(!preg_match("/\/$/", $attributes['filedir'])) $attributes['filedir'] .= "/";
				break;
			case "submit":	
				if(!Check::arrayKeysFormat(array("value"), $attributes)) return false;
				break;
			case "checkbox":
				if(!Check::arrayKeysFormat(array("on_text", "off_text"), $attributes)) return false;
				break;
			case "checkbox_array";
				//same as radio
			case "radio":	
				if(!Check::arrayKeysFormat(array("get_choices_array_func"), $attributes)) return false;
				break;
			case "date_month_year":
				if(!Check::arrayKeysFormat(array("start_year", "get_choices_array_func"), $attributes)) return false;
				break;
			case "textbox":
				break;
			case "textarea":
				break;
			case "hidden":
				break;
			case "hiddenarray":
				break;
			case "password":
				break;
			case "select":
				break;
			case "counter":
				if(!Check::arrayKeysFormat(array("init"), $attributes)) return false;
				break;
			default:
				return false;
				break;
		}
		
		//set default value if one wasn't chosen already
		if(in_array('default_val', $attributes)){$attributes['default_val']="";}
		
		//if we're using post, pull variable from post array
		if((array_key_exists("use_post", $attributes) && $attributes['use_post']) 
			|| (array_key_exists("usepost", $attributes) && $attributes['usepost'])) {//use if getting array from html
			$_REQUEST[$varname] = $_POST[$varname];
		}

                //if variable wasn't returned, at all, set to default
                if(empty($_REQUEST[$varname]) && !is_numeric($_REQUEST[$varname])){$_REQUEST[$varname] = $_POST[$varname] = $attributes['default_val'];}

		if($type == "select" || $type == "radio") { //check_func is always validSelect
			$attributes['check_func'] = "validSelect";
			$attributes['check_func_args'] = array($attributes['get_choices_array_func'], $attributes['get_choices_array_func_args']);
		}
		
		//$attributes[type] = $type;
		if ($type != "checkbox_array" && $type != "date_month_year" && $type != "file") {
			if((array_key_exists("use_post", $attributes) && $attributes['use_post'])
                        || (array_key_exists("usepost", $attributes) && $attributes['usepost'])) {
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
			if ($attributes['usepost'] || $attributes['use_post']) {
				$$vm = $_POST[$vm];
				$$vy = $_POST[$vy];
			} else {
				$$vm = $_REQUEST[$vm];
				$$vy = $_REQUEST[$vy];
			}
		}	

		if($type=="counter"){
			$attributes['value']=$_REQUEST[$varname];
			if(!$attributes['value']){
				$attributes['value']=$attributes['init'];
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
		if (array_key_exists($submitvar_name, $this->vars) && ($$submitvar_name == $this->vars[$submitvar_name]['value'])) return true;
		return false;
	}

	function setDisplayMode($mode) {
		$this->disp_mode = $mode;
	}
		
	//set disp_type to either "form" or "success"
	function displayVar($varname, $disp_type = false, $args = array()) {
		if ($disp_type == false) {
			if (!$this->disp_mode) $disp_mode = "form";
			else $disp_type = $this->disp_mode;
		}
		switch ($this->vars[$varname]["type"]) {
			
			case "textbox": $this->printTextbox($varname, $this->vars[$varname], $disp_type);
				break;
			case "textarea": $this->printTextarea($varname, $this->vars[$varname], $disp_type);
                                break;
			case "hidden": $this->printHidden($varname, $this->vars[$varname], $disp_type);
                                break;
			case "hiddenarray": $this->printHiddenArray($varname, $this->vars[$varname], $disp_type);
				break;
			case "file": $this->printFile($varname, $this->vars[$varname], $disp_type);
                                break;
			case "password": $this->printPassword($varname, $this->vars[$varname], $disp_type);
                                break;
			case "submit": $this->printSubmit($varname, $this->vars[$varname], $disp_type);
                                break;
			case "select": $this->printSelect($varname, $this->vars[$varname], $disp_type);
                                break;
			case "checkbox": $this->printCheckbox($varname, $this->vars[$varname], $disp_type);
                                break;
			case "checkbox_array": $this->printCheckbxArray($varname, $this->vars[$varname], $disp_type);
                                break;
			case "radio": $this->printRadio($varname, $this->vars[$varname], $disp_type);
                                break;
			case "date_month_year": $this->printDateMonthYear($varname, $this->vars[$varname], $disp_type);
                                break;
			case "counter": $this->printCounter($varname, $this->vars[$varname], $disp_type);
				break;
			default: return;
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
			$func = $attr['get_choices_array_func'];
			$a = $attr['get_choices_array_func_args'];

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
				if($_REQUEST[$v][$c['value']] != "Y") {
					$_REQUEST[$v][$c['value']] = "N";
				}
			}
		} elseif ($attr[type] == "date_month_year") {
			if(array_key_exists("check_func", $attr) && $attr['check_func'] != "none") {
				$func = $attr['check_func'];
				$vmonth = $v . "_month";
				$vyear = $v . "_year";
				global $$vmonth;
				global $$vyear;
			
				$_REQUEST[$vmonth] = $$vmonth;
				$_REQUEST[$vyear] = $$vyear;
				$ret = false;
				$a = $attr['check_func_args'];

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
					array_push($this->emessages, $attr['error_message'] . $ret);
					array_push($this->elocators, $v);
				}
			}
		} elseif($attr[type] == "file") {
			if(array_key_exists("check_func", $atr) && $attr['check_func'] != "none") {
                                $func = $attr['check_func'];
                                $farr_name = $v . "_file_array";
                                global $$v;
                                global $$farr_name;

                                $ret = false;
                                $a = $attr['check_func_args'];

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
                                        array_push($this->emessages, $attr['error_message'] . $ret);
                                        array_push($this->elocators, $v);
                                }	
	
			}
		} else {
			if(array_key_exists("check_func", $attr) && $attr['check_func'] != "none") {
				$func = $attr['check_func'];
				$ret = false;
				
				if(!is_array($attr['check_func_args'])) {
					$ret = $check->$func($_REQUEST[$v]);
				} else {
					$a = $attr['check_func_args'];
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
                                        array_push($this->emessages, $attr['error_message'] . $ret);
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
				if(	(	($obj_type == false && !array_key_exists("obj_type", $attr))
					||	($obj_type != false && $attr['obj_type'] == $obj_type))
					&& $attr['setget'] != "none"
			          ) {
					if($attr['type'] != "date_month_year" && $attr['type'] != "file") {
						$func = "set" . $attr['setget'];
						global $$v;
						$obj->$func($$v);
					} elseif($attr['type'] == "file") {
						$func = "set" . $attr['setget'];
						$farr_name = $v . "_file_array";
						global $$v;
						global $$farr_name;
						$file_array = $$farr_name;
						$obj->$func($$v);
					} else {
						$func = "set" . $attr['setget'];
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
                                if(     (       ($obj_type == false && !array_key_exists("obj_type", $attr))
                                        ||      ($obj_type != false && $attr['obj_type'] == $obj_type))
                                        && $attr['setget'] != "none"
                                  ) {
                                        if($attr['type'] != "date_month_year" && $attr['type'] != "file") {
                                                $func = "get" . $attr[setget];
                                                global $$v;
                                                $obj->$func($$v);
						$_REQUEST[$v] = $$v;
                                        } elseif($attr['type'] == "file") {
                                                $func = "get" . $attr['setget'];
                                                $farr_varname = $v . "_file_array";
                                                global $$v;
                                                global $$farr_varname;
                                                $file_array = array("name"=>$obj->$func());
                                                $$farr_varname = $file_array;
						$$v = $file_array['name'];
                                        } else {
                                                $func = "get" . $attr['setget'];
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

	function printTextbox($v, $attr, $disp_type = "form") {
		global $$v;
		$_REQUEST[$v] = $$v;
		$lvar = stripslashes($$v);
		if(empty($lvar) && !is_numeric($lvar)){$lvar=$attr['default_val'];}
		if(Check::notInt($attr['box_size'])){
			$attr['box_size'] = 82;
		}
		if($disp_type == "form"){
			?><input type=text size=<?php echo $attr['box_size']?> maxlength=255 name="<?php echo $v?>" value="<?php echo htmlspecialchars($lvar)?>"><?php
		} else {
			echo $lvar;
		}
	}

        function printPassword($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                $lvar = stripslashes($$v);
		if(empty($lvar)){$lvar=$attr['default_val'];}
                if(Check::notInt($attr['box_size'])){
                        $attr['box_size'] = 82;
                }
                if($disp_type == "form"){
                        ?><input type=password size=<?php echo $attr['box_size']?> maxlength=255 name="<?php echo $v?>" value=""><?php
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
				?><input type=file name="<?php echo $v?>" value="<?php echo $file_array['origname']?>"><?php
                	} else {
                        	?><input type=file name="<?php echo $v?>" value="<?php echo $file_array['name']?>"><?php
			}
		} else {
			?><a href="<?php echo ($attr['filedir_webpath'] . "/" . $file_array['name'])?>"><?php echo $file_array['name']?></a><?php
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
		        ?><textarea rows="<?php echo $attr['rows']?>" cols="<?php echo $attr['cols']?>" wrap=hard name="<?php echo $v?>"><?php echo $lvar?></textarea><?php
                } else {
                        echo $lvar;
                }
        }

        function printHidden($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                $lvar = stripslashes($$v);

		if(empty($lvar)){$lvar=$attr['value'];}

                if($disp_type == "form"){
                        ?><input type=hidden name="<?php echo $v?>" value="<?php echo htmlspecialchars($lvar)?>"><?php
                } else {
                        //echo $lvar;
                }
        }

        function printHiddenArray($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = implode(',',$$v);
                $lvar = stripslashes(implode(',',$$v));

                if(empty($lvar)){$lvar=$attr['value'];}

                if($disp_type == "form"){
			
                        ?><input type=hidden name="<?php echo $v?>" value="<?php echo htmlspecialchars($lvar)?>"><?php
                } else {
                        //echo $lvar;
                }
        }

        function printSubmit($v, $attr, $disp_type = "form") {
                global $$v;
                $_REQUEST[$v] = $$v;
                if($disp_type == "form") {
					echo '<input type=submit name="'.$v.'" value="'.$attr['value'].'">';
                }
        }

        function printSelect($v, $attr, $disp_type = "form") {
			global $$v;
			$_REQUEST[$v] = $$v;
			if(strlen($attr['choices_array_var']) > 0) {
				$vchoices = $attr['choices_array_var'];
			} else {
				$vchoices = $v . "_choices";
			}

			global $$vchoices;
			$choices = $$vchoices;

			if($disp_type == "form"){
					echo '<select name="'.$v.'"';
					if($attr[reloading]){
						echo ' onchange="this.form.submit()"';
					}
					echo '>';
				//echo "<pre>" . var_dump($choices) . "</pre>";
				foreach($choices as $c){
					echo '<option value="'.$c[value].'"';
					if($_REQUEST[$v] == $c[value]){ echo " SELECTED";}
					echo '> '.$c[text];
				}
				echo '</select>';
			} else {
				foreach($choices as $c) {
					if($_REQUEST[$v] == $c[value]) {
						if($args[lowercase] == true){ echo strtolower($c[text]);}
						else {echo $c[text];}
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
				foreach($choices as $c) {
						echo '<input type="radio" name="'.$v.'" value="'.$c[value].'"';
						if($_REQUEST[$v] == $c[value]) echo " CHECKED";
						echo '> '.$c[text].'<br/>';
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
//var_dump($_REQUEST);
                $_REQUEST[$v] = $$v;
                $actual_var = $_REQUEST[$v];
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
                
		if($disp_type == "form"){
            		echo '<input type=checkbox value="Y" name="';
			//echo '<input type=checkbox name="';
			echo $actual_name;
			echo '"'; 
			if($actual_var == "Y"){ echo "CHECKED";} 
			echo '> '; 
			echo $attr[on_text];
		} else {
			if($actual_var == "Y") {
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
				?><br/><?php
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
                        ?><input type=hidden name="<?php echo $v?>" value="<?php echo htmlspecialchars($attr[value])?>"><?php
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
}

?>
