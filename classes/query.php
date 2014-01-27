<?php

/***************************************
*
*   Query class
*
*   Part of the Data Abstraction Layer
*
****************************************/

class Query{

/***********************
    Variables
***********************/

var $mysql_user = "ironarena";
var $mysql_host = "localhost";
var $mysql_password = "iwantskullz";

var $mysql_database = "iron_arena";

var $connection = null; //database object holder

static $instance;



/************************
    Constructor
************************/

private function __construct(){
    //make the connection

    $this->connection = new PDO("mysql:host=$this->mysql_host;dbname=$this->mysql_database",
                                $this->mysql_user, $this->mysql_password);
    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}

public static function getInstance(){
    if(self::$instance == null){self::$instance = new Query();}
    return self::$instance;
}



/***********************
    Destructor
***********************/

public function __destruct(){}


/***********************
    Query

    Returns data in an array, or false if an error occurred

***********************/

public function query($sql, $values){
    $pdo_query = $this->connection->prepare($sql);
    $pdo_query->execute($values);

    return $pdo_query->fetchAll();
}

/***********************
    Update

    Returns true if success, false if an error occurred

***********************/

public function update($sql, $values){
    $pdo_update = $this->connection->prepare($sql);
    $pdo_update->execute($values);

    return $pdo_update->rowCount();
}

public function updateGroup($sql, $value_sets){
    $pdo_update = $this->connection->prepare($sql);

    foreach($value_sets as $k=>$set){
        $pdo_update->execute($set);
        if($pdo_update->rowCount() <= 0){
            return false;
        }
    }

    return true;
}

/***********************
    Insert

    returns the ID of the inserted row, or false if an error occurred

***********************/

public function insert($sql, $values){
    $pdo_insert = $this->connection->prepare($sql);
    $pdo_insert->execute($values);
    
    return $this->connection->lastInsertId();
}

public function insertGroup($sql, $value_sets){
    $pdo_insert = $this->connection->prepare($sql);
    
    $new_ids = array();

    foreach($value_sets as $k=>$set){
        $pdo_insert->execute($set);
        $new_ids[] = $pdo_insert->lastInsertId();
    }

    return $new_ids;
}


/**********************
    Delete

    Just run a query instead

**********************/

public function delete($sql, $values){
    return $this->update($sql, $values);
}

}//class declaration

?>
