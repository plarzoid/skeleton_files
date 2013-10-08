<?php

//include_once('user.php');

class Session {

	function init () {
		session_start();
	}

	function userid() {
		return $_SESSION[userid];
	}

	function isLoggedIn() {
		return $_SESSION[is_logged_in];
	}

	function isNotLoggedIn() {
		return !$_SESSION[is_logged_in];
	}

/*	function isAdmin() {
		if(Session::isLoggedIn()){
			$u = new User();
			$u = $u->findUserbyUserID($_SESSION[userid]);
			if($u){
				if($u->isAdmin()) {
					return true;
				} 
			}
		}
		return false;
	}

	function isNotAdmin() {
		return !Session::isAdmin();
	}

        function getUsername() {
                if(Session::isLoggedIn()){
                        $u = new User();
                        $u = $u->findUserbyUserID($_SESSION[userid]);

                        if($u){
                		return $u->getUsername();
			} else {
		
                		return "Failed to find user in database!";
			}
		} else {
			return false;
		}
        }	

	function isAuthorized($level) {
		//Firstly, everyone is authorized to see public pages
                if(!strcmp($level, "PUBLIC")){return true;}//remember, strcmp returns 0 on match :p

		if(Session::isLoggedIn()){
			//before we go any further, admins are always authorized
			if(Session::isAdmin()){return true;}

			$u = new User();
			$u = $u->findUserbyUserID($_SESSION[userid]);
			
			if($u){

				if($u->getAuthLevel() >= $level) {
					return true;
				}
			
			}
		}
		return false;
	}
	
	function authenticate($uname, $upass) {
		$u = new User();
		$u = $u->findUserByUsernamePassword($uname, $upass);
		if ($u){
			if($u->idValid()){
				Session::login($u);
				return $u;
			}
		}
		return false;
	}

	function login($u) {
		$_SESSION[userid] = $u->getUserId();
		$_SESSION[is_logged_in] = true;
		$_SESSION[is_admin] = $u->isAdmin();
	}

	function logout() {
		unset($_SESSION[userid]);
		unset($_SESSION[is_logged_in]);
		unset($_SESSION[is_admin]);
	}
*/
}

?>
