<?php require_once('vault/common.php');

/********************
*********************
To show a specific variable - where "make" is the variable:

$Vault->show('make');


********************/

foreach($Vault->values as $key => $value) {
	echo $key . ' : ';
	$Vault->show($key);
	echo '<br />';
}

$Vault->show('test_missing_key');
?>