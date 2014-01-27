<?php

/**************************************************
*
*    Views Class
*
***************************************************/

require_once("query.php");

class Views {

var $db=NULL;

/***************************************************

Constructor & Destructor

***************************************************/
public function __construct(){
    $this->db = Query::getInstance();
}

public function __destruct(){}


/**************************************************

Query by Function

**************************************************/
public function getAll($view){
    
    $sql = "SELECT * FROM $view";

    return $this->db->query($sql, array());
}



/**************************************************

Query by Function

**************************************************/
public function queryByColumns($view, $columns){

    //Values Array
    $values = array();
    foreach($columns as $column=>$value){
        $values[":".$column]=$value;
    }

    //Generate the query
    $sql = "SELECT * FROM $view WHERE ";
    $keys = array_keys($columns);
    foreach($keys as $column){
        $sql.= "$column=:$column";
        if(strcmp($column, end($keys))){
            $sql.= " AND ";
        }
    }

    return $this->db->query($sql, $values);
}



}//close class

?>
