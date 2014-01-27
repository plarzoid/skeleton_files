<?php

include_once('db_users.php');

class Session {

	public static function init () {
		session_start();
	}

	public static function sessionuserid() {
		return $_SESSION[sessionuserid];
	}

	public static function isLoggedIn() {
		return $_SESSION[is_logged_in];
	}

	public static function isNotLoggedIn() {
		return !$_SESSION[is_logged_in];
	}

	public static function isAdmin() {
            if(isset($_SESSION[is_admin])) return $_SESSION[is_admin];

	    if(Session::isLoggedIn()){
		$u = new Users();
		$u = $u->getbyId($_SESSION[sessionuserid]);

		if($u){
		    if($u[0][admin]) {
			return true;
		    } 
		}
	    }
	    return false;
	}

	public static function isNotAdmin() {
		return !Session::isAdmin();
	}

        public static function getUsername() {
                if(Session::isLoggedIn()){
                        $db = new Users();
                        $u = $db->getById($_SESSION[sessionuserid]);
                        
                        //Strip array wrapper
                        if(is_array($u)) $u = $u[0];

                        if($u){
                		return $u[username];
			} else {
                		return "Failed to find user ".$_SESSION[sessionuserid]." in database!";
			}
		} else {
			return false;
		}
        }	

	public static function getUserID() {
		if(Session::isLoggedIn()){
			return $_SESSION[sessionuserid];
		}
		return false;
	}

	public static function isAuthorized($level) {
		//Firstly, everyone is authorized to see public pages
                if(!strcmp($level, "PUBLIC")){return true;}//remember, strcmp returns 0 on match :p

		if(Session::isLoggedIn()){
			//before we go any further, admins are always authorized
			if(Session::isAdmin()){return true;}

			$u = new User();
			$u = $u->getById($_SESSION[sessionuserid]);
			
			if($u){
			    if($u->getAuthLevel() >= $level) {
				return true;
			    }
			}
		}
		return false;
	}
	
	public static function authenticate($uname, $upass) {
                $db = new Users();
                $u = $db->getByUsername($uname);

                //strip wrapper
                if(is_array($u)) $u = $u[0];

		if ($u){
			if(!strcmp($upass, $u[password])){
				Session::login($u);
				return $u;
			}
		}
		return false;
	}

	public static function login($u) {
		$_SESSION[sessionuserid] = $u[id];
		$_SESSION[is_logged_in] = true;
		$_SESSION[is_admin] = $u[admin];

                $db = new Users();
                $db->updateUsersById($u[id], array("last_login"=>date("Y-m-d H:i:s", time())));
        }

	public static function logout() {
		unset($_SESSION[sessionuserid]);
		unset($_SESSION[is_logged_in]);
		unset($_SESSION[is_admin]);
	}

}

?>
