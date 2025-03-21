<?php
//local
/*$pc_ip =  $_SERVER['REMOTE_ADDR'];
$server_ip =  "11.0.0.2";*/

//$connect = mysqli_connect("localhost", "root", "", "v_list");
$c = mysqli_connect("localhost", "root", "", "v_list");

/*$c_server = mysqli_connect("$server_ip", "root", "", "v_list");
$c_mypc = mysqli_connect("$pc_ip", "root", "", "v_list");*/

// Check connection
mysqli_set_charset($c, "utf8");
//mysqli_set_charset($connect, "utf8");
//https://github.com/jomsnow12370/reports.git


$mq = 'mysqli_query';
$mf = 'mysqli_fetch_array';
?>