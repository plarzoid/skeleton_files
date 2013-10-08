#!/usr/bin/php
<?php
//turn off notices
error_reporting(E_ERROR & ~E_NOTICE);

//Test Zone

//END Test Zone

/*************************************************

Check for the right number of inputs

*************************************************/

$inputs = $argv;
if(count($inputs) != 3){
	echo "Usage: create_classes.php <sql script> <output directory>\n";
	return;
}


/************************************************

Check accessability of SQL file

************************************************/
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

Check accessability of Class directory

************************************************/
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

Start parsing the file

****************************************************/

//disable column detection, until we've encountered a create table line
$table_opened=false;

$line = fgets($sql_fptr);

while($line){

/************************************************

Get the table name

************************************************/
if(is_numeric(strpos(strToLower($line), 'create table'))){

	$matches=array();
	preg_match("~\s([^\s]+)\s?\(~", $line, $matches);

	$table_name = end($matches);
	$table_Fn_name = ucfirst(strToLower($table_name));

	echo "\n======================================================================\n";
	echo "\n";
	echo "Found Table: '$table_name'\n";
	echo "Proper Table name: '$table_Fn_name'\n";

	//create the empty columns array
	$columns = array();

	//Create the empty keys array
	$keys = array("primary"=>"", "foreign"=>array());

	//enable column detection
	$table_opened=true;
}

/************************************************

Get the columns of the table

************************************************/
if($table_opened && 
	(!is_numeric(strpos(strToLower($line), 'create table'))) && 
	(!is_numeric(strpos(strToLower($line), 'primary key'))) &&
	//(!is_numeric(strpos(strToLower($line), 'foreign key'))) && 
	(!is_numeric(strpos(strToLower($line), 'unique key')))){
	
	$column_parts = explode(" ", trim($line));
	if(count($column_parts) > 1){
		
		$name = trim($column_parts[0]);
		$varname = "\$".strToLower($name);
		$pdoname = ":".strtolower($name);
		$type = preg_split("~[\(\)]~", trim($column_parts[1]));
		
		$column_entry = array("name"=>$name, "varname"=>$varname, "type"=>$type[0], "pdoname"=>$pdoname);

		if(preg_match("~varchar~", strtolower($type[0])) || preg_match("~text~", strtolower($type[0]))){
			$column_entry[validateFn] = "if((strlen($varname) == 0) && !is_null($varname)){echo \"Invalid variable: $name\";}";
		} else {
			$column_entry[validateFn] = "if(filter_var($varname, ";

			switch(strtolower($type[0])){
				case "tinyint":
				case "year":
				case "int":
					$column_entry[validateFn].= "FILTER_VALIDATE_INT";
					break;
				case "float":
				case "decimal":
				case "datetime":
					$column_entry[validateFn].= "FILTER_VALIDATE_FLOAT";
					break;
				case "boolean":
				case "bit":
					$column_entry[validateFn].= "FILTER_VALIDATE_BOOLEAN";
					break;
			}

			$column_entry[validateFn].= ") != $varname){echo \"Invalid variable: $name\";}";
		}

		$columns[]=$column_entry;

		echo "Found Column: ".$column_entry[name]."\n";
	}
}


/************************************************

Get the keys

************************************************/

if($table_opened && is_numeric(strpos(strToLower($line),'primary key'))){

	$key_detection_pattern="~primary key \(([^\s]+)\)~";
	preg_match($key_detection_pattern, strToLower($line), $matches);

	$keys["primary"] = strToUpper(end($matches));

	foreach($columns as $key=>$column_array){
		if(!strcmp($columns[$key]["name"], $keys["primary"])){
			$columns[$key]["primary_key"]=true;
			$primary_key = $columns[$key];
			echo "Found Primary Key: ".$keys["primary"]."\n";
		}
	}
}


/************************************************

Detect end of table, write file

************************************************/

if($table_opened && preg_match("~\);~", $line)){

    //Create and open the file
	$file = $class_dir.strtoLower($table_name).".php";
	$class_fptr = fopen($file, 'w');

	//check for successful open
	if($class_fptr == false){echo "Failed to open file: $file!"; return;}

	echo "\nOpening file: $file\n";

/************************************************

Generate the function names for the columns

************************************************/

foreach($columns as $k=>$c){
	$name = explode("_", strToLower($c[name]));
	$new_name = "";

	foreach($name as $chunk){
		$new_name.=ucfirst($chunk);
	}

	$columns[$k][function_name]=$new_name;
}
	

/************************************************

Write the class file

************************************************/

$class_header= '<?php

/**************************************************
*
*	'.$table_Fn_name.' Class
*
***************************************************/

require_once("query.php");

class '.$table_Fn_name.' {

var $db=NULL;
var $table="'.$table_name.'";
var $primary_key="'.$primary_key[name].'";

/***************************************************

Constructor & Destructor

***************************************************/

public function __construct(){
	$this->db = Query::getInstance();
}

public function __destruct(){}

';

echo "Writing class header...\n";
fputs($class_fptr, $class_header);


/***************************
* Write the create function
***************************/
$createFn = '
/**************************************************

Create Function

**************************************************/
';

$createFn.= "public function create(";

foreach($columns as $k=>$c){
	if(!$c[primary_key] && !preg_match("~modified~", $c[varname])){
		$createFn.=$c[varname];
		if($k != end(array_keys($columns))){$createFn.=", ";}
	}
}

$createFn.="){\n";
$createFn.="\n\t//Validate the inputs\n";

foreach($columns as $c){
	 if(!$c[primary_key] && (strlen($c[validateFn]) > 0) && !preg_match("~modified~", $c[varname])){
	 	$createFn.="\t".$c[validateFn]."\n";
	}
}

$createFn.="\n";

$createFn_columns ="\t\$sql = \"INSERT INTO \$this->table (";
$createFn_values  =")\";\n\t\$sql.= \" VALUES (";
$createFn_end 	  =")\";\n";
$createFn_array	  ="\n\t\$values = array(";

foreach($columns as $k=>$c){
    if(!$c[primary_key] && !preg_match("~modified~", $c[varname])){
	
		//columns
		$createFn_columns.=$c[name];
		
		//values
		if(preg_match("~created_date~", $c[varname])){
			$createFn_values.="NOW()";
		} else {
			$createFn_values.=$c[pdoname];
		}
		
		//the pdo associated array
		if(!preg_match("~created_date~", $c[varname])){
			$createFn_array.="\n\t\t\"".$c[pdoname]."\"=>\"".$c[varname]."\"";
		}

		//handling the commas
		if($k != end(array_keys($columns))){
			$createFn_columns.=", ";
			$createFn_values .=", ";
			$createFn_array  .=", ";
		}
	}
}

$createFn.=$createFn_columns.$createFn_values.$createFn_end;

$createFn.=$createFn_array."\n\t);\n";

$createFn.="\n\n";

$createFn.="\treturn \$this->db->insert(\$sql, \$values);";
$createFn.="\n}\n\n";

echo "Writing create function...\n";
fputs($class_fptr, $createFn);

/**************************
Update function
**************************/
$updateFn_header = '
/**************************************************

Update Function

**************************************************/
';
$updateFn_call = "public function updateBy$primary_key[name]($primary_key[varname], ";
$updateFn_validations = "\t//Input Validation\n";
$updateFn_sql = "\t//Build the SQL Query\n\t\$sql = \"UPDATE \$this->table SET \";\n";
$updateFn_values = "\t//Build the values array\n\t\$values = array(\n";

//Pull off the create columns, so we can accurately detect the end of the array of columns
$create_columns=array();
foreach($columns as $k=>$c){
	if(preg_match("~created~", $c[varname])){
		$create_columns[] = $columns[$k];
		unset($columns[$k]);
	}
}

foreach($columns as $k=>$c){
    if($c[primary_key]){
		$updateFn_values.="\t\t\"$c[pdoname]\"=>\"$c[varname]\",\n";
	} else {
	    $updateFn_call.=$c[varname];
		$updateFn_validations.="\t".$c[validateFn]."\n";
		$updateFn_sql.="\t\$sql.= \"$c[name]=$c[pdoname]";
		$updateFn_values.="\t\t\"$c[pdoname]\"=>\"$c[varname]\"";

	    if($k != end(array_keys($columns))){
			$updateFn_call.=", ";
			$updateFn_sql.=", \";\n";
			$updateFn_values.=",\n";
		} else {
			$updateFn_call.= "){\n\n";
			$updateFn_validations.="\n";
			$updateFn_sql.=" \";\n\t\$sql.= \"WHERE \$this->primary_key=$primary_key[pdoname]\";\n\n";
			$updateFn_values.="\n\t);\n\n";
		}
    }
}

//Patch the create columns back on
$columns = array_merge($columns, $create_columns);

$updateFn = $updateFn_header.$updateFn_call.$updateFn_validations.$updateFn_sql.$updateFn_values;

$updateFn.= "\treturn \$this->db->update(\$sql, \$values);\n";
$updateFn.= "}\n\n";

echo "Writing Update Function...\n";
fputs($class_fptr, $updateFn);

/**************************
* Delete function
**************************/
$deleteFn = '
/**************************************************

Delete Function

**************************************************/
';
$deleteFn.= "public function deleteBy$primary_key[name]($primary_key[varname]){\n";
$deleteFn.= "\t//Input validation\n";
$deleteFn.= "\tif(!is_numeric($primary_key[varname])){return false;}\n\n";
$deleteFn.= "\t//Build the query\n";
$deleteFn.= "\t\$sql = \"DELETE FROM \$this->table WHERE \$this->primary_key=$primary_key[pdoname]\";\n\n";
$deleteFn.= "\t\$values = array(\"$primary_key[pdoname]\"=>\"$primary_key[varname]\");\n\n";
$deleteFn.= "\t//Return the result\n";
$deleteFn.= "\treturn \$this->db->update(\$sql, \$values);\n";
$deleteFn.= "}\n\n";

echo "Writing delete function...\n";
fputs($class_fptr, $deleteFn);

/**************************
Get All function
**************************/
$getFn = '
/**************************************************

Query ALL THE THINGS

**************************************************/
';

$getFn.= "public function getAll(){\n";
$getFn.= "\treturn \$this->db->query(\"SELECT * FROM \$this->table\", NULL);\n";
$getFn.= "}\n\n";

echo "Writing Get All function...\n";
fputs($class_fptr, $getFn);

/**************************
* Individual 'queryByColumn' functions
**************************/
$columnFnHeader = '
/**************************************************

Query By Column Function(s)

**************************************************/
';
fputs($class_fptr, $columnFnHeader);

foreach($columns as $column){
	$columnFn="";
	$columnFn.= "public function queryBy".$column[function_name]."($column[varname]){\n";
	$columnFn.= "\t//Validate the inputs\n";
	if(strlen($column[validateFn])){$columnFn.= "\t".$column[validateFn]."\n\n";}
	$columnFn.= "\t//Build the query\n";
	$columnFn.= "\t\$sql = \"SELECT * FROM \$this->table WHERE $column[name]=$column[pdoname]\";\n";
	$columnFn.= "\t\$values = array(\"$column[pdoname]\"=>\"$column[varname]\");\n\n";
	$columnFn.= "\t//Return the result\n";
	$columnFn.= "\treturn \$this->db->query(\$sql, \$values);\n";
	$columnFn.= "}\n\n";

	echo "Writing query by column function for ".$column[name]."...\n";
	fputs($class_fptr, $columnFn);
}

/**************************
*  Write the class footer
**************************/
$class_footer= "}//close class\n\n"."?".">\n";//break this up so we can keep syntax highlighting working in vim...

echo "Writing class footer...\n";
fputs($class_fptr, $class_footer);
flush();

echo "Closing file\n";
fclose($class_fptr);

//turn off column detection
$table_opened=false;

} //close the end of table and write file detection clause

//get the next line, for the next iteration of the loop
$line = fgets($sql_fptr);

}//close the fgets loop

/************************************************

Close the files

************************************************/
echo "\nClosing SQL file...\n";
fclose($sql_fptr);

?>
