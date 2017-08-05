<?php
error_reporting(E_ERROR);

//DB config
$servername = "localhost";
$username = "root";
$password = "abc";
$db = "csv";

$fileName = "users";
$tableName="users";

//Connect to DB
$conn = mysqli_connect($servername, $username, $password, $db);
if(!$conn){
	die("Database connect error : ". mysqli_connect_errno() . PHP_EOL);
}else{
	echo "Success to connect to MYSQL! \n";
}

//
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
		die('Could not create table: ' . mysql_error());
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
				
			//close sql	
			mysqli_close($conn);
		}
		
		"Table is created!\n";
	}
}


/**
 * Function is to open the csv file 
*/
function getFile(){
	global $fileName;
	
	if(filesize($fileName.".csv") > 0){	
		if (($handle = fopen($fileName.".csv", "r")) !== FALSE) {	
			
			//doDryRun($handle);
			doCreateTable($handle);

			fclose($handle);
			
		}else{
			echo "Fail to open the file!";
		}
	}else{
		echo "This is an empty file!";
	}

}

getFile();


?>