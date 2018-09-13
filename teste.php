<?php

echo "Restrito <hr />";

$users = array (
					'ailton.magno' => 'JvgVUGdU',
					'suely.lima' => 'JvgVUGdU',
					'thiago.israel' => 'da9UCjMQfnM=',
					'samira.costa' => 'ZKtLDSAD',
					'natasha.correa' => 'eatSBSEKey5jQSss',
					'etiene.oliveira' => 'JvoUVGFS',
					'anne.evlyn' => 'dg==',
					'rosa.silva' => 'JPsWV2RR',
					'erika.pompeu' => 'crhPDzMBe24lEXJ37A=='
			   );
$obj = new COM ( "Helper.clsHelper" );
//$output = $obj->Encrypt ( 'rba' ); // Call the "hello()" method

echo '<h1>Recuperacao de senha:</h1> <br><br>';
foreach ( $users as $user => $senha ) {
	$output = $obj->Decrypt ( $senha );
	echo "$user : $output<hr>"; // Displays Hello World! (so this comes from the dll!)
}

/*
$ontem = strtotime ( 'now - 1 day' );
$anteontem = strtotime ( 'now - 2 day' );
$um_mes_atras = strtotime ( 'now - 1 month' );
$um_ano_a_frente = strtotime ( 'now + 1 year' );

echo "Ontem:" . date ( 'd/m/Y', $ontem ) . "<br />";
echo "Anteontem:".date ( 'd/m/Y', $anteontem )."<br />";
echo "Um m� atr�s:".date ( 'd/m/Y', $um_mes_atras )."<br />";
echo "Um ano a frente:".date ( 'd/m/Y', $um_ano_a_frente )."<br />";
*/

?> 