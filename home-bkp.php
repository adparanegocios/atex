<?php

session_start ();
include_once 'classes/Template.class.php';
include_once 'global.php';
include_once 'validacao.php';

$tpl = new Template ( 'home.html' );
include_once 'menu.php';

$tpl->show ();

?>