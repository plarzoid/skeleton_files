#!/usr/bin/php
<?php

//turn off notices
error_reporting(E_ERROR & ~E_NOTICE);

/*************************************************

Test Zone

*************************************************/
$pattern = "";
$string = "CREATE  TABLE IF NOT EXISTS `asterdial`.`project_channels` (";

echo preg_match($pattern, $string, $matches);
echo "\n";

echo $matches[1]."\n";
//return;

/*************************************************
//END Test Zone
*/


/*************************************************
**************************************************

Command Line Input Checking

*************************************************
*************************************************/

/************************************************
1. Check for the right number of inputs
*/

$inputs = $argv;
if(count($inputs) != 3){
    echo "Usage: create_classes.php <sql script> <output directory>\n";
    return;
}


/***********************************************
2. Check accessability of SQL file
*/
$sql_file = $inputs[1];

if(!file_exists($sql_file)){
    echo "Unable to locate file: $sql_file.\n";
    return;
}

if(!is_readable($sql_file)){
    echo "Unable to read file: $sql_file.\n";
    return;
}

$sql_fptr = fopen($sql_file, "r");


/************************************************
3. Check accessability of Class directory
*/
$class_dir = $inputs[2];

if(!is_dir($class_dir)){
    echo "Directory does not exist: $class_dir.\n";
    return;
} 

if(!is_writable($class_dir)){
    echo "Unable to read file: $class_dir.\n";
    return;
}

echo "Command: $inputs[0]\n";
echo "Time: ".date("Y-m-d H:i:s")."\n\n";

echo "Opening files...\n";


/****************************************************
*****************************************************

Parsing the SQL file

*****************************************************
****************************************************/

//disable column detection, until we've encountered a create table line
$table_opened=false;

$line = " ";

while($line){

//Read in a line
$line = fgets($sql_fptr);

/************************************************
1. Detect and capture the name of the table
*/
$create_table_pattern = "~`[a-z]+`\.`([a-z_]+)`~";

if(preg_match("~CREATE~", $line) && preg_match($create_table_pattern, $line, $matches)){

    $table_name = end($matches);
    $table_Fn_name = ucfirst(strToLower($table_name));

    echo "\n======================================================================\n";
    echo "\n";
    echo "Found Table: $table_name\n";
    echo "Table Function name: $table_Fn_name\n\n";

    //create the empty columns array
    $columns = array();

    //Create the empty keys array
    $keys = array("primary"=>"", "foreign"=>array());

    //enable column detection
    $table_opened=true;

    //Skip the rest of the loop
    continue;
}

/************************************************
2. Detect and extract columns of the table
*/
$column_pattern = "~`([a-zA-Z_]+)`\s+([A-Z]+)~";

if($table_opened && !preg_match("~INDEX~", $line) && preg_match($column_pattern, $line, $matches)){

    $name = $matches[1];
    
    $varname = "\$".strToLower($name);
    
    $type = $matches[2];

    switch($type){
        case "INT":
        case "BIGINT":
            $fn = "isInt";
            break;
        case "VARCHAR":
            $fn = "isString";
            break;
        case "TINYINT":
        case "BOOLEAN":
            $fn = "isBool";
            break;
        case "FLOAT":
        case "DOUBLE":
            $fn = "isFloat";
            break;
        default :
            $fn = "notNull";
    }

    $validateFn="if(!Check::$fn($varname)){return false;}";

    $columns[]=array(
        "name"=>$name,
        "varname"=>$varname,
        "type"=>$type,
        "validateFn"=>$validateFn);

    echo "Found Column: $name\n";

    //skip the rest of the loop
    continue;
}


/************************************************
3. Detect and capture the Primary Key
*/
$primary_key_pattern = "~PRIMARY\sKEY\s\(`([a-zA-Z_]+)`\)~";

if($table_opened && preg_match($primary_key_pattern, $line, $matches)){

    $keys["primary"] = strToLower(end($matches));

    foreach($columns as $key=>$column_array){
        if(!strcmp($columns[$key]["name"], $keys["primary"])){
            
            $columns[$key]["primary_key"]=true;
            $primary_key = array(
                "name"=>$columns[$key][name], 
                "varname"=>$columns[$key][varname]);
            
            echo "\nFound Primary Key: ".$keys["primary"]."\n";
            break;
        }
    }
    
    //skip the rest of the loop
    continue;
}


/************************************************
4. Detect end of table
*/

if($table_opened && preg_match("~;$~", $line)){

    //Create and open the file
    $file = $class_dir.strtoLower($table_name).".php";
    $class_fptr = fopen($file, 'w');

    //check for successful open
    if($class_fptr == false){echo "Failed to open file: $file!"; return;}

    echo "\nOpening file: $file\n";


    //Generate the function names for the columns
    foreach($columns as $k=>$c){
        $name = explode("_", strToLower($c[name]));
        $new_name = "";

        foreach($name as $chunk){
            $new_name.=ucfirst($chunk);
        }

        $columns[$k][function_name]=$new_name;
    }
    

/************************************************
5. Write the class file header
*/

$class_header= '<?php

/**************************************************
*
*    '.$table_Fn_name.' Class
*
***************************************************/
require_once("query.php");

class '.$table_Fn_name.' {

var $db=NULL;
var $table="'.$table_name.'";


/***************************************************

Constructor & Destructor

***************************************************/
public function __construct(){
    $this->db = new Query();
}

public function __destruct(){}

';

echo "Writing class header...\n";
fputs($class_fptr, $class_header);


/*************************************************
6. Write the create function
*/
$createFn = '
/**************************************************

Create Function

**************************************************/
';
$createFn.= "public function create$table_Fn_name(";
foreach($columns as $k=>$c){
    //Exclude from create if it's the primary key
    if(!$c[primary_key]){
        $createFn.=$c[varname];
        if($k != end(array_keys($columns))){$createFn.=", ";}
    }
}
$createFn.="){\n";


$createFn.="\n\t//Validate the inputs\n";
foreach($columns as $c){
    if(!$c[primary_key] && (strlen($c[validateFn]) > 0)){
         $createFn.="\t".$c[validateFn]."\n";
    }
}
$createFn.="\n";


$createFn.="\t//Create the values Array\n";
$createFn.="\t\$values = array(\n";
foreach($columns as $k=>$c){
    if($c[primary_key]){continue;}

    $createFn.="\t\t\":".$c[name]."\"=>".$c[varname];
    if($k != end(array_keys($columns))){$createFn.=",\n ";}
}
$createFn.="\n\t);\n\n";


$createFn.="\t//Build the query\n";
$createFn.="\t\$sql = \"INSERT INTO \$this->table (\n";
foreach($columns as $k=>$c){
    if($c[primary_key]){continue;}

    $createFn.="\t\t\t\t".$c[name];
    if($k != end(array_keys($columns))){$createFn.=",\n";}
}
$createFn.="\n";
$createFn.="\t\t\t) VALUES (\n";
foreach($columns as $k=>$c){
    if($c[primary_key]){continue;}

     $createFn.="\t\t\t\t:".$c[name];
     if($k != end(array_keys($columns))){$createFn.=",\n";}
    
}
$createFn.=')";';
$createFn.="\n\n";

$createFn.="\treturn \$this->db->insert(\$sql, \$values);";
$createFn.="\n}\n\n";

echo "Writing create function...\n";
fputs($class_fptr, $createFn);


/**************************
* Delete function
**************************/
$deleteFn = '
/**************************************************

Delete Function

**************************************************/
';
$deleteFn.= "public function delete".$table_Fn_name."(".$primary_key[varname]."){\n\n";

$deleteFn.= "\t//Validate the input\n";
$deleteFn.= "\tif(Check::isInt(".$primary_key[varname].")){return false;}\n\n";

$deleteFn.= "\t//Create the values array\n";
$deleteFn.= "\t\$values = array(\":".$primary_key[name]."\"=>".$primary_key[varname].");\n\n";

$deleteFn.= "\t//Create Query\n";
$deleteFn.= "\t\$sql = \"DELETE FROM \$this->table WHERE ".$primary_key[name]."=:".$primary_key[name]."\";\n\n";
$deleteFn.= "\treturn \$this->db->delete(\$sql, \$values);\n";
$deleteFn.= "}\n\n";

echo "Writing delete function...\n";
fputs($class_fptr, $deleteFn);

/**************************
* Individual 'getBy' functions
**************************/
$columnFnHeader = '
/**************************************************

Query By Column Function(s)

**************************************************/
';
fputs($class_fptr, $columnFnHeader);

$masterColumnFn='private function get'.$table_Fn_name.'ByColumn($column, $value){

	//inputs are pre-verified by the mapping functions below, so we can trust them

	//Values Array
	$values = array(":$column"=>$value);

	//Generate the query
	$sql = "SELECT * FROM $this->table WHERE $column=:$column";
    
	return $this->db->query($sql, $values);
}';
$masterColumnFn.="\n\n";

echo "Writing master column function...\n";
fputs($class_fptr, $masterColumnFn);

foreach($columns as $column){

$columnFn = '
public function get'.$table_Fn_name.'By'.ucfirst(strtolower($column[name])).'('.$column[varname].'){
	
	//Validate Inputs
	'.$column[validateFn].'

	return get'.$table_Fn_name.'ByColumn("'.$column[name].'", '.$column[varname].'.);
}';
$columnFn.="\n\n";

echo "Writing column function for $column[name]...\n";
fputs($class_fptr, $columnFn);


}

/**************************
*  Write the class footer
**************************/
//break this up so we can keep syntax highlighting working in vim...
$class_footer= "}//close class\n\n"."?".">\n";

echo "Writing class footer...\n";
fputs($class_fptr, $class_footer);
flush();

echo "Closing file\n";
fclose($class_fptr);

//turn off column detection
$table_opened=false;

} //close the end of table and write file detection clause

}//close the while($line) loop

/************************************************

Close the files

************************************************/
echo "\nClosing SQL file...\n";
fclose($sql_fptr);

?>
