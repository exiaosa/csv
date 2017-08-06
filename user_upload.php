<?php
error_reporting(E_ERROR);


//DB config
/**
$servername = "localhost";
$username = "root";
$password = "abc";
$db = "csv";
**/

$servername = "";
$username = "";
$password = "";
$db = "";

$fileName = "";
$tableName="users";


/**
 * Function is to initialize the script
 * @param $argv -- arguments from the commend line
*/
function init($argv){

	getCommandLines($argv);

}

/**
 * Function is to deal with the argvs
 * @param $argv -- arguments from the commend line
*/
function getCommandLines($argv){
	global $servername;
	global $username;
	global $password;
	global $db;
	
	foreach($argv as $i => $str){
		if ($i == 0) continue;
		
		if(substr( $str, 0, 6 ) === "--file"){
			global $fileName;

			$start = strpos($str, '[');
			$end = strpos($str, ']', $start + 1);
			$length = $end - $start;
			$result = substr($str, $start + 1, $length - 1);
				
			if(strpos($result, '.') !== false) {
				die("[ERROR]: This is invalid file. Please do not include any extension.");
				
				
			}else{
				$fileName = $result;
				
			}
		
		}
		
		if(substr( $str, 0, 16 ) === "--create_table") continue;
		
		
		if(substr( $str, 0, 9 ) === "--dry_run") continue;
			
		
		if(substr( $str, 0, 6 ) === "--help"){
			echo    "--file [csv file name] - this is the name of the CSV to be parsed \n".
					"--create_table - this will cause the MySQL users table to be built \n".
					"--dry_run - this will be used with the file directive in the instance that we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered. \n".
					"-u - MySQL username \n".
					"-p - MySQL password \n".
					"-h - MySQL host \n".
					"-d - MySQL database name \n".
					"--help - which will output the above list of directives with details \n";		
		}
		
		if($str === "-h"){
			$servername = $argv[$i+1];
			
		}
		
		if($str === "-d"){
			$db = $argv[$i+1];
			
		}
		
		if($str === "-u"){
			$username = $argv[$i+1];
			
		}
		
		if($str === "-p"){
			$password = $argv[$i+1];
		}
	}
	
	if(in_array("--dry_run",$argv)){
		if($fileName == ""){
			die("[ERROR]: please set file name as well.\n");
		}else{
			getFile('dry_run');
		}
	}
	
	if(in_array("--create_table",$argv)){
		if($fileName === ""){
			die("[ERROR]: please set file name as well.\n");
		}else{
			if(in_array("-p",$argv) || in_array("-u",$argv) || in_array("-d",$argv) || in_array("-h",$argv)){
				die("[ERROR]: please set up database config.\n");
			}
			
			connectDB();
			getFile('create_table');
		}
	}
}


/**
 * Function is to connect to the database
*/
function connectDB(){
	global $servername;
	global $username;
	global $password;
	global $db;
	global $conn;
	
	$conn = mysqli_connect($servername, $username, $password, $db);
	
	if(!$conn){
		die("[ERROR]: Database connect error :". mysqli_connect_errno() . PHP_EOL);
	}else{
		echo "Success to connect to MYSQL! \n";
	}
}


/**
 * Function is to print out the csv data
 * @param $handle -- the opened csv file
*/
function doDryRun($handle){
	$row = 0;
	
	echo "\nData from csv file: \n";
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {	
		$num = count($data);
		$row++;	

		for ($c=0; $c < $num; $c++) {
			echo trim($data[$c])."       ";
		}
		echo  "\n";
	
	}
	echo "Total rows:".$row;
}


/**
 * Function is to create the DB table 
 * @param $handle -- the opened csv file 
*/
function doCreateTable($handle){
	global $tableName;
	global $fileName;

	global $conn;
	
	
	$fields = "";
	$fieldsInsert = "";
	
	//Get the first row of data as sql
	if(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);
		$fieldsInsert .= '(';
		for ($c=0; $c < $num; $c++) {
			$trim_data= trim($data[$c]);
			$fieldsInsert .=($c==0) ? '' : ', ';
			$fieldsInsert .="`".$trim_data."`"; 
			$fields .="`".$trim_data."` varchar(1000) DEFAULT NULL,";					
		}
	$fieldsInsert .= ')';
	}
	
	//Drop table if it exists
	$query = "SELECT NAME FROM '".$tableName."'";
	$result = mysqli_query($conn, $query);			
	if(empty($result)) {
		mysqli_query($conn,'DROP TABLE IF EXISTS `'.$tableName.'`') or die(mysql_error());
	}
	
	//create table
	$sql = "CREATE TABLE `".$tableName."` (
        `".$tableName."Id` int(100) unsigned NOT NULL AUTO_INCREMENT,
        ".$fields."
        PRIMARY KEY (`".$tableName."Id`),
		UNIQUE (`EMAIL`)
    ) ";
	
	$retval = mysqli_query($conn,$sql);
	
	//insert data to table
	if(! $retval ){
		die("[ERROR]: Could not create table: " . mysql_error());
	}else{
		while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data);
			$fieldsInsertvalues="";
			//get field values of each row
			for ($c=0; $c < $num; $c++) {
				
				if($c !== 2){
					//Capitalized the first letter 
					$data[$c] = ucwords($data[$c]);
					
					$data[$c] = mysqli_real_escape_string($conn, $data[$c]);
					$fieldsInsertvalues .=($c==0) ? '(' : ', ';
					$fieldsInsertvalues .="'".trim($data[$c])."'";
					
				}else{
					//Validate the email format
					if (filter_var($data[$c], FILTER_VALIDATE_EMAIL)) {
						//set email as lower case
						$data[$c] = strtolower($data[$c]);
						$fieldsInsertvalues .=($c==0) ? '(' : ', ';
						$fieldsInsertvalues .="'".trim($data[$c])."'";
					}else{
						$output = "This (".trim($data[$c]).") email address is considered invalid.\n";
						fwrite(STDOUT, $output);
					}
				}

			}
			$fieldsInsertvalues .= ')';
			//insert the values to table
			$sql = "INSERT INTO ".$tableName." ".$fieldsInsert."  VALUES  ".$fieldsInsertvalues;
			mysqli_query($conn,$sql);   
				
		}
		
		"Table is created!\n";
		//close sql	
		mysqli_close($conn);
	}
}


/**
 * Function is to open the csv file 
*/
function getFile($action){
	global $fileName;
	
	if(filesize($fileName.".csv") > 0){	
		if (($handle = fopen($fileName.".csv", "r")) !== FALSE) {	
			
			if($action === 'create_table'){
				doCreateTable($handle);
			}else if($action === 'dry_run'){
				doDryRun($handle);
			}

			fclose($handle);
			
		}else{
			die("[ERROR]: Fail to open the file!");
		}
	}else{
		die("[ERROR]: This is an empty file!");
	}

}

init($argv);

?>