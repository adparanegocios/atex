<?php

if (! isset ( $_SESSION ['user'] )) {
	header ( 'location: '.PATH.'logout.php' );
}

?>