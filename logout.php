<?php

session_start ();
session_destroy ();
$_SESSION ['user'] = array ();
header ( 'location: login.php' );

?>