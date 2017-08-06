<?php

for($i=1; $i<=100; $i++){
    $count;
	
    if($i%3 == 0 && $i%5 == 0) {      
        echo "foobar ";
    }
    else if($i%5 == 0) {
        echo "bar ";
    }
    else if($i%3 == 0) {
        echo "foo ";
    }else {
		echo $i." ";
	}
}
?>
