<?php
//error_reporting(E_ERROR);

$fileName = "users";

//
/**
 * Function is to print out the csv data
 * @param $handle -- the opened csv file
*/
function doDryRun($handle){
	$row = 0;
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {	
		$num = count($data);
		$row++;	

		for ($c=0; $c < $num; $c++) {
			echo $data[$c]."       ";
		}
		echo  "\n";
	
	}
	echo "Total rows:".$row;
}


/**
 * Function is to create the DB table 
 * 
*/
function doCreateTable(){

}


/**
 * Function is to open the csv file 
*/
function getFile(){
	global $fileName;
	
	if(filesize($fileName.".csv") > 0){	
		if (($handle = fopen($fileName.".csv", "r")) !== FALSE) {	
			
			doDryRun($handle);

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