<?php
echo $ipadr = $_SERVER['REMOTE_ADDR'];
echo '<br>';
echo $host3 = $_SERVER['HTTP_USER_AGENT'];
echo '<br>';
echo $host2 = $_SERVER['HTTP_HOST'];
echo '<br>';
echo $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
echo '<br>';
echo $today = date("d.m.y");
echo '<br>';
echo $time = date("H:i:s");
echo '<br>';
echo $_SERVER['LOGON_USER'];
echo '<br>';
echo "<pre>".print_r ($_SERVER)."</pre> ";
echo '<br>';
echo "<pre>".print_r ($_ENV)."</pre> ";
?> 
