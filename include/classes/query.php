<?php

/***************************************************************************
*
*	Query Class for handling direct database interactions
*
***************************************************************************/

class Query{

var $mysql_user="novaopen";
var $mysql_host="localhost";
var $mysql_password="c0nv3nt!0n";

var $mysql_database="NoVaOpen";

var $connection=NULL;//database connection object

/***************
*	Constuctor
***************/
public function __construct(){

	//build the failure message wile it's convenient
	$die_str = "MySQL connection failed!<br/>'$this->mysql_host'@'$this->mysql_user', '$this->mysql_password'";

	//attempt to establish the connection
	$this->connection = mysql_connect($this->mysql_host, $this->mysql_user, $this->mysql_password) or die($die_str);

	//select the desired database
	mysql_select_db($this->mysql_database) or die("MySQL database ($this->mysql_database) selection failed!");
}

/***************
*   Destructor
***************/
public function __destruct(){
	mysql_close($this->connection);//see if this works this time
}


/***************
*   query(string) retrieves data from the database, or returns false if nothing found
***************/
public function query($sql){
	$result = mysql_query($sql) or die(mysql_error());

	//if the database returns empty (false), kick it back now
	if(is_bool($result)){return $result;}

	//fetch the individual rows from the query
	$row = mysql_fetch_row($result);
	while($row){
		$ret[]=$row;
		$row = mysql_fetch_row($result);
	}

	if(is_array($ret)){
		return $ret;
	}

	//if everything else fails, pass a false back
	return false;
}


/***************
*   update(string) changes data in the database, returns true/false based on success of the operation
***************/
public function update($sql){
	$result = mysql_query($sql) or die(mysql_error());

	//return the result if it's a boolean
	if(is_bool($result)){return $result;}

	//return false if everything else fails
	return false;
}


/***************
*   insert(string) puts data in the database, returns the new row id or false based on success of the operation
***************/
public function insert($sql){
	$result = mysql_query($sql) or die(mysql_error());

	//if a boolean, fetch the row id, or return false
	if(is_bool($result)){
		if($result){
			return mysql_insert_id();
		} else {
			return $result;//false
		}
	}

	//return false is all else fails
	return false;
}


}//close the class
?>
