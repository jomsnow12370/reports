<?php 
//datetime
date_default_timezone_set("Asia/Manila");
$datenow = date("Y-m-d");
$timenow = date("h:i A");
$datetimenow = $datenow . ' ' . $timenow;


function getLeaderType($type){
	$leaderType = "";
switch ($type) {
	case 1:
		$leaderType =  "Ward Leader";
		break;

		case 2:
		$leaderType = "Barangay Coordinator";
		break;

			case 3:
		$leaderType = "District Coordinator";
		break;

			case 4:
		$leaderType = "Municipal Coordinator";
		break;
	
	default:
		$leaderType = "N/A";
		break;
		
}
return $leaderType;
}
//post
function p($var)
{

	$var2 = str_replace(['ñ', 'Ñ'],"?", $_POST["$var"]);
	return $var2;
}

function enye($var)
{
	$var2 = utf8_decode(str_replace("?","Ñ",$var));
	return $var2;
}
//post
function p2($var)
{
	$var2 = str_replace("'", "`", $_POST["$var"]);
	return $var2;
}

function p3($var)
{
	$var2 = str_replace("'", "`",$var);
	return $var2;
}

function replaceidk($var)
{
	$var2 = str_replace("`", "'",$var);
	return $var2;
}
//post
function q($var)
{
	$var2 = str_replace("Ñ", "N", $var);
	return $var2;
}
//redirect
function redirect($x, $y)
{
	$url = $x;
	echo '<META HTTP-EQUIV=REFRESH CONTENT="'. $y . '; '.$url.'">';
}
//get contact info
		function get_array($query){
			include('conn.php');
			$arr = array();
			$r = $mq($c, $query);
			while($rw = $mf($r)){
				array_push($arr, $rw);
			}
			return $arr;
		}

//get single value from query
		function get_value($query){
			include('conn.php');

			$r = $mq($c, $query);
			$rw = $mf($r);
			return $rw;
		}

		function getVotingPreference($cong, $gov, $vgov){
			if($cong == "Laynes (Survey 2025)" && $gov == "BossTe(Survey 2025)" && $vgov == "Fernandez(Survey 2025)"){
				return "Vote Straight";
			}
			if($cong == "UndecidedCong(Survey 2025)" && $gov == "UndecidedGov(Survey 2025)" && $vgov == "UndecidedVGov(Survey 2025)"){
				return "Undecided";
			}
		}