<?php

include_once 'classes/Template.class.php';

$tpl = new Template ( 'login.html' );

if ((isset ( $_REQUEST ['msg'] )) && ($_REQUEST ['msg'] == 'erro1')) {
	$tpl->MSG = 'Acesso nуo autorizado!';
} elseif ((isset ( $_REQUEST ['msg'] )) && ($_REQUEST ['msg'] == 'erro2')) {
	$tpl->MSG = 'Falha na autenticaчуo!';
} else {
	$tpl->MSG = '';
}

$tpl->show ();

?>