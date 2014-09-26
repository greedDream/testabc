<?php

session_start();
$cid = $_SESSION['cid'];
$month = $_SESSION['month'];
$year=$_SESSION['year'];
$compare = ($year - 1) * 12 + $month;
$hire_count = 0;
$fire_count = 0;
$thismonth_hire = 0;
$thismonth_fire = 0;
$connect = mysql_connect("localhost", "root", "53g4ek7abc") or die(mysql_error());
mysql_select_db("testabc_main", $connect);
    mysql_query("set names 'utf8'");

if (!strcmp($_GET['type'], "finan")) {
    $result = mysql_query("SELECT SUM(`hire_count`),SUM(`fire_count`) FROM `current_people` WHERE `department` = 'finance' AND `cid` = $cid ;", $connect);
	$thismonth_hire = mysql_query("SELECT `hire_count` FROM `current_people` WHERE `department` = 'finance' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);
	$thismonth_fire = mysql_query("SELECT `fire_count` FROM `current_people` WHERE `department` = 'finance' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);    
    $num = $result[0] - $result[1];
    echo $num . "|" . $thismonth_hire . "|" . $thismonth_fire;
	
} else if (!strcmp($_GET['type'], "equip")) {
    $result = mysql_query("SELECT SUM(`hire_count`),SUM(`fire_count`) FROM `current_people` WHERE `department` = 'equip' AND `cid` = $cid ;", $connect);
	$thismonth_hire = mysql_query("SELECT `hire_count` FROM `current_people` WHERE `department` = 'equip' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);
	$thismonth_fire = mysql_query("SELECT `fire_count` FROM `current_people` WHERE `department` = 'equip' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);    
    $num = $result[0] - $result[1];
    echo $num . "|" . $thismonth_hire . "|" . $thismonth_fire;
	
} else if (!strcmp($_GET['type'], "sale")) {
    $result = mysql_query("SELECT SUM(`hire_count`),SUM(`fire_count`) FROM `current_people` WHERE `department` = 'sale' AND `cid` = $cid ;", $connect);
	$thismonth_hire = mysql_query("SELECT `hire_count` FROM `current_people` WHERE `department` = 'sale' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);
	$thismonth_fire = mysql_query("SELECT `fire_count` FROM `current_people` WHERE `department` = 'sale' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);    
    $num = $result[0] - $result[1];
    echo $num . "|" . $thismonth_hire . "|" . $thismonth_fire;
	
} else if (!strcmp($_GET['type'], "human")) {
    $result = mysql_query("SELECT SUM(`hire_count`),SUM(`fire_count`) FROM `current_people` WHERE `department` = 'human' AND `cid` = $cid ;", $connect);
	$thismonth_hire = mysql_query("SELECT `hire_count` FROM `current_people` WHERE `department` = 'human' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);
	$thismonth_fire = mysql_query("SELECT `fire_count` FROM `current_people` WHERE `department` = 'human' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);    
    $num = $result[0] - $result[1];
    echo $num . "|" . $thismonth_hire . "|" . $thismonth_fire;
	
} else if (!strcmp($_GET['type'], "research")) {
    $result = mysql_query("SELECT SUM(`hire_count`),SUM(`fire_count`) FROM `current_people` WHERE `department` = 'research' AND `cid` = $cid ;", $connect);
	$thismonth_hire = mysql_query("SELECT `hire_count` FROM `current_people` WHERE `department` = 'research' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);
	$thismonth_fire = mysql_query("SELECT `fire_count` FROM `current_people` WHERE `department` = 'research' AND `cid` = $cid; AND `year`=$year AND `month`=$month", $connect);    
    $num = $result[0] - $result[1];
    echo $num . "|" . $thismonth_hire . "|" . $thismonth_fire;
	
} else if (!strcmp($_GET['type'], "hire_submit")) {
    $temp = mysql_query("SELECT * FROM `current_people` WHERE `year`=$year AND `month`=$month");
    $result = mysql_fetch_array($temp);
    if (empty($result)) {
        mysql_query("INSERT INTO `current_people` VALUES ($year,$month,'$cid','finance',0,0,0,0);", $connect);
        mysql_query("INSERT INTO `current_people` VALUES ($year,$month,'$cid','equip',0,0,0,0);", $connect);
        mysql_query("INSERT INTO `current_people` VALUES ($year,$month,'$cid','sale',0,0,0,0);", $connect);
        mysql_query("INSERT INTO `current_people` VALUES ($year,$month,'$cid','human',0,0,0,0);", $connect);
        mysql_query("INSERT INTO `current_people` VALUES ($year,$month,'$cid','research',0,0,0,0);", $connect);
    }
    mysql_query("UPDATE `current_people` SET `hire_count`={$_GET['f']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='finance'", $connect);
    mysql_query("UPDATE `current_people` SET `hire_count`={$_GET['e']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='equip'", $connect);
    mysql_query("UPDATE `current_people` SET `hire_count`={$_GET['s']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='sale'", $connect);
    mysql_query("UPDATE `current_people` SET `hire_count`={$_GET['h']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='human'", $connect);
    mysql_query("UPDATE `current_people` SET `hire_count`={$_GET['r']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='research'", $connect);
} else if (!strcmp($_GET['type'], "fire_submit")) {
    mysql_query("UPDATE `current_people` SET `fire_count`={$_GET['f']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='finance'", $connect);
    mysql_query("UPDATE `current_people` SET `fire_count`={$_GET['e']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='equip'", $connect);
    mysql_query("UPDATE `current_people` SET `fire_count`={$_GET['s']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='sale'", $connect);
    mysql_query("UPDATE `current_people` SET `fire_count`={$_GET['h']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='human'", $connect);
    mysql_query("UPDATE `current_people` SET `fire_count`={$_GET['r']} WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='research'", $connect);
} else if (!strcmp($_GET['type'], "hire_money")) {
	$correspondence=mysql_query("SELECT * FROM correspondence WHERE `name`='current_people'",$connect);
	$correspond=mysql_fetch_array($correspondence);
	echo $correspond['money'];
} else if (!strcmp($_GET['type'], "fire_money")) {
	$correspondence=mysql_query("SELECT * FROM correspondence WHERE `name`='current_people'",$connect);
	$correspond=mysql_fetch_array($correspondence);
	$correspondence=mysql_query("SELECT * FROM correspondence WHERE `name`='current_people_2'",$connect);
	$correspond2=mysql_fetch_array($correspondence);
	echo $correspond['money2'].":".$correspond['money3'].":".$correspond2['money'].":".$correspond2['money2'].":".$correspond2['money3'];
}
	$result = mysql_query("SELECT `efficiency` FROM `current_people` WHERE `year`=$year AND `month`=$month AND `cid`='$cid' AND `department`='sale'",$connect);
	$temp = mysql_fetch_array($result);
	$sale_limit=$num*$temp[0]/250;
	if($sale_limit>=1250)
		$s_limit=1;
	else if($sale_limit>=1000)
		$s_limit=2;
	else if($sale_limit>=750)
		$s_limit=3;
	else if($sale_limit>=500)
		$s_limit=4;
	else
		$s_limit=5;
	echo $s_limit;
}

?>
