<html xmlns="http://www.w3.org/1999/xhtml">
<?php
	include("../connMysql.php");
   	if (!@mysql_select_db("testabc_login")) die("資料庫選擇失敗!");
    mysql_query("set names 'utf8'");
	$C_name=mysql_query("SELECT DISTINCT(`CompanyID`) FROM account ORDER BY `CompanyID` ASC");
	mysql_select_db("testabc_main");
	error_reporting(0);
	
	$temp=mysql_query("SELECT MAX(`year`) FROM `state`");
    $result_temp=mysql_fetch_array($temp);
    $year=$result_temp[0];
    $temp=mysql_query("SELECT MAX(`month`) FROM `state` WHERE `year`=$year;");
    $result_temp=mysql_fetch_array($temp);
    $month=$result_temp[0];
	$month_for_report=$month+($year-1)*12;
?>		
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../css/tableyellow.css" type="text/css" />
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
	var year=<?php echo $year?>;
	if(year==1){
		$('#rank1').hide();	
		$('#rank2').hide(); 
	 }});
</script>
<title>競賽排名</title>
</head>
<body>
<?php
	$i=1;
	$result = mysql_query("SELECT `stock_price` FROM `stock` WHERE `year`=$year AND `month`=$month ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){ 
		$stock_price[$i]=$temp[0];//當期股價
		$i++;}
	$i=1;
	$result = mysql_query("SELECT `stock_price` FROM `stock` WHERE `year` = $year AND `month` = 1 ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$last_stock_price[$i]=$temp[0];//上一期股價
		$i++;}
	$i=1;
	while($company=mysql_fetch_array($C_name)){//每間公司ㄉ名稱~
		$CN=$company['CompanyID'];
		$result = mysql_query("SELECT SUM(price) FROM `equity` WHERE `cid` = '$CN' AND `month` = $month_for_report-1 ORDER BY `cid` ASC");
		$temp=mysql_fetch_array($result);
		$IC[$i]=$temp[0];//權益總額
		$i++;}	
	$i=1;
	$result = mysql_query("SELECT price FROM `long-term_liabilities` WHERE `name` = '212' AND `month` = $month_for_report-1 ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$IC[$i]+=$temp[0];//應付票據
		$i++;}	
	$i=1;
	$result = mysql_query("SELECT price FROM `current_liabilities` WHERE `name` = '211' AND `month` = $month_for_report-1 ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$IC[$i]+=$temp[0];//短期借款
		$i++;
	}
	mysql_select_db("testabc_login");//讀所有的公司名稱~
	$C_name=mysql_query("SELECT DISTINCT(`CompanyID`) FROM account ORDER BY `CompanyID` ASC");
	mysql_select_db("testabc_main");
	$length=mysql_num_rows($C_name);
	while($company=mysql_fetch_array($C_name)){//每間公司ㄉ名稱
		$CN=$company['CompanyID'];
		$result = mysql_query("SELECT SUM(price) FROM `equity` WHERE `cid` = '$CN' AND `month` = $month_for_report-2 ORDER BY `cid` ASC");
		$temp=mysql_fetch_array($result);
		$last_IC[$i]=$temp[0];//上期權益總額
		$i++;}
	$i=1;
	$result = mysql_query("SELECT price FROM `long-term_liabilities` WHERE `name` = '212' AND `month` = $month_for_report-2 ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$last_IC[$i]+=$temp[0];//上期應付票據
		$i++;}
	$i=1;
	$result = mysql_query("SELECT price FROM `current_liabilities` WHERE `name` = '211' AND `month` = $month_for_report-2 ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$last_IC[$i]+=$temp[0];//上期短期借款
		$i++;}
	$i=1;
	$result = mysql_query("SELECT `WACC` FROM `stock` WHERE `year`=$year AND `month` = $month ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$WACC[$i]=$temp[0]*100;
		$i++;}	
	$result = mysql_query("SELECT netin FROM `cash` WHERE `year`=$year AND `month` = $month ORDER BY `cid` ASC");
	$i=1;
	while($temp=mysql_fetch_array($result)){
		$ROIC[$i]=$temp[0];//本期淨利
		$i++;}
	$i=1;
	$result = mysql_query("SELECT price FROM `income_taxes` WHERE `month` = $month_for_report-1 ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$ROIC[$i]-=$temp[0];//淨利+所得稅=繼續營業部門稅前純益
		$i++;}
	$i=1;
	$result = mysql_query("SELECT dividend FROM `fund_raising` WHERE `year`=$year AND `month` = $month ORDER BY `cid` ASC");
	while($temp=mysql_fetch_array($result)){
		$Dividend[$i]-=$temp[0];//+Dividend
		$i++;}
	for($i=1;$i<=$length;$i++){
		$ROI[$i]=($stock_price[$i]-$last_stock_price[$i]+$Dividend[$i])/$last_stock_price[$i]*100;
		if($IC[$i]==0&&$last_IC[$i]==0)//避免分母為零
			$IC[$i]=1;
		$EVA[$i]=100000000+($ROIC[$i]/(($IC[$i]+$last_IC[$i])/2)*100-$WACC[$i])*($IC[$i]+$last_IC[$i])/2; //-------------------------------------------------------------------------------------
	}
	//新增KPI部分，資料表的account是公司名，session是回合數~~
	$i=1;
	$result = mysql_query("SELECT * FROM `kpi_info` WHERE `session` = $month_for_report-1 ORDER BY `account` ASC");
	while($temp=mysql_fetch_array($result)){
		for($kpi_now=2, $kpi_predict=3;$kpi_now<=60, $kpi_predict<=61;$kpi_now+=2, $kpi_predict+=2){
			if($temp[$kpi_predict]!=0){
				if($temp[$kpi_now]/$temp[$kpi_predict]>1.2)
					$KPI[$i]+=0;
				else
					$KPI[$i]+=$temp[$kpi_now]/$temp[$kpi_predict];
			}
		}
		$i++;
	}
?>
<table cellspacing='0' class="ytable">
<thead>
<tr>
    <th width = 150 align="center" valign="middle" scope="co1">公司名稱</th>
    <th width = 150>經營失敗時間</th>
    <th scope="co1" width = 80>股價</th>
    <th width = 150>投資人報酬率</th>
    <th width = 80>投入資本(IC)</th>
    <th width = 150>投入資本報酬率(ROICt)</th>
    <th width = 150>加權平均資本成本(WACC)</th>
    <th width = 150>經濟附加價值</th>
</thead>
<tbody>
<?php
	mysql_select_db("testabc_login");//讀所有的公司名稱
	$CN=mysql_query("SELECT COUNT(DISTINCT(`CompanyID`)) FROM account ORDER BY `CompanyID` ASC");
	$company_num=mysql_fetch_array($CN);
	$C_name=mysql_query("SELECT DISTINCT(`CompanyID`) FROM account ORDER BY `CompanyID` ASC");
	mysql_select_db("testabc_main");
	
	$i=1;
	while($company=mysql_fetch_array($C_name)){//每間公司ㄉ名稱
		if ($flag % 2 == 1)
        echo "<tr class='odd'>";
		echo "<td>".$company['CompanyID']."</td>";
		$break=0;
		$result = mysql_query("SELECT * FROM `stock` WHERE `cid`='$CN' ORDER BY `year`, `month` ASC");
		while(($temp=mysql_fetch_array($result))&&$break==0){
			if($temp['stock']>2000000){
				$stock[$i]=$temp['stock'];
				$year_over[$i]=$temp['year'];
				$month_over[$i]=$temp['month'];
				$break=1;
			}
			else $stock[$i]=$temp['stock'];
		}	
		$sql_allbr=mysql_query("SELECT	SUM(`bankrupt`) FROM `cash` WHERE `cid`='".$company['CompanyID']."'");
		$bankrupt=mysql_fetch_array($sql_allbr); //總倒閉次數
		$result = mysql_query("SELECT MAX(`year`) FROM `cash` WHERE `cid`='".$company['CompanyID']."' AND `bankrupt`=1");
		$lastyear = mysql_fetch_array($result);//最後一次倒閉是第幾年
		$result = mysql_query("SELECT MAX(`month`) FROM `cash` WHERE `cid`='".$company['CompanyID']."' AND `bankrupt`=1 AND `year`='$lastyear[0]'");
		$lastmonth = mysql_fetch_array($result);//最後一次倒閉是第幾月
		if($bankrupt[0]>0){
			echo "<td>第".$lastyear[0]." 年".$lastmonth[0]." 月 (".$bankrupt[0].")</td>";}
		else
			echo "<td>繼續經營</td>";

		//股價
		$print1[$i]=$stock_price[$i];
		echo "<td>".number_format($print1[$i],2)."</td>";
		    
		//投資人報酬率
		if($last_stock_price[$i]==0)//避免分母為零
		$last_stock_price[$i]=1;
		$print2[$i]=$ROI[$i];
		echo "<td>".number_format($print2[$i],2)."%</td>";
	
		//投入資本(IC)
		$print3[$i]=$IC[$i];
		echo "<td>".number_format($print3[$i])."</td>";
		
		//投入資本報酬率(ROICt)
		if($IC[$i]==0&&$last_IC[$i]==0)//避免分母為零
		$IC[$i]=1;
		$print4[$i]=$ROIC[$i]/(($IC[$i]+$last_IC[$i])/2)*100;
		echo "<td>".number_format($print4[$i],2)."%</td>";
		
		//加權平均資本成本(WACC)
		$print5[$i]=$WACC[$i];
		echo "<td>".number_format($print5[$i],2)."%</td>";
		
		//經濟附加價值
		$print6[$i]=$EVA[$i];
<<<<<<< HEAD
		echo "<td>".number_format($print6[$i],2)."</td>";
=======
		echo "<td>".number_format($print6[$i],0)."</td>";
>>>>>>> origin/test
		echo "</tr>";
		$flag++;
		$i++;
	}
	echo "</tbody>";
	echo "<tfoot style='text-align:center'><tr>";
    echo "<th>同業平均數據</th>";
	echo "<td></td>";
	echo "<td>".number_format(array_sum($print1)/$length,2)."</td>";
	echo "<td>".number_format(array_sum($print2)/$length,2)."%</td>";
	echo "<td>".number_format(array_sum($print3)/$length)."</td>";
	echo "<td>".number_format(array_sum($print4)/$length,2)."%</td>";
	echo "<td>".number_format(array_sum($print5)/$length,2)."%</td>";
<<<<<<< HEAD
	echo "<td>".number_format(array_sum($print6)/$length,2)."</td>";
=======
	echo "<td>".number_format(array_sum($print6)/$length)."</td>";
>>>>>>> origin/test
    echo "</tr>";
	echo "</tfoot>";
?>
</table>
<br> <br> <br> 
<?php
	$year=$y;
?>
<table id="rank1" align='center' cellspacing='0' class="ytable" style="display:inline;">
<thead>
<tr>
    <th width = 80 align="center" valign="middle" scope="co1">公司名稱</th>
    <th scope="co1" width = 120>投資人報酬率績效得分數</th>
    <th width = 120>經濟附加價值績效得分數</th>
    <th width = 130>KPI績效得分數</th>
</tr>
</thead>
<tbody>
<?php
	mysql_select_db("testabc_login");//讀ABC所有的公司名稱
	$C_name=mysql_query("SELECT DISTINCT(`CompanyID`) FROM account ORDER BY `CompanyID` ASC");
	mysql_select_db("testabc_main");
	$temp=mysql_query("SELECT MAX(`year`) FROM `state`");
    $result_temp=mysql_fetch_array($temp);
    $year=$result_temp[0];
    $temp=mysql_query("SELECT MAX(`month`) FROM `state` WHERE `year`=$year;");
    $result_temp=mysql_fetch_array($temp);
    $month=$result_temp[0];
	$month_for_report=$month+($year-1)*12;
	$i=1;
	$flag=0;
	while($company=mysql_fetch_array($C_name)){//每間公司名稱
		if ($flag % 2 == 1)
		  echo "<tr class='odd' height=50>";
		else
		  echo "<tr height=50>";
		echo "<td>".$company['CompanyID']."</td>";
		$c[$i]=$company['CompanyID'];

		if($month==1){  
			//投資人報酬率績效得分數
			for($j=1,$score1[$i]=1;$j<=$length;$j++)
				if($ROI[$i]>$ROI[$j])
					$score1[$i]++;	
	
			//經濟附加價值績效得分數
			for($j=1,$score2[$i]=1;$j<=$length;$j++)
				if($EVA[$i]>$EVA[$j])
					$score2[$i]++;

			//KPI績效得分數
			for($j=1,$score3[$i]=1;$j<=$length;$j++)
				if($KPI[$i]>$KPI[$j])
					$score3[$i]++;
		    
			mysql_query("UPDATE `score` SET `score1`=".$score1[$i]." , `score2`=".$score2[$i]." , `score3`=".$score3[$i]." WHERE cid='".$c[$i]."' AND year='$year' AND month='$month'") or die(mysql_error());
		}
		else{
			$sql_score=mysql_query("SELECT * FROM `score` WHERE `cid`='".$c[$i]."' AND `year`=$year AND `month`=($month-1)");
			$score = mysql_fetch_array($sql_score);
		
			//投資人報酬率績效得分數
			for($j=1,$score1[$i]=1;$j<=$length;$j++)
				if($ROI[$i]>$ROI[$j])
					$score1[$i]++;
					$score1[$i]=$score1[$i]+$score['score1']*($month_for_report-1)/$month_for_report;

			//經濟附加價值績效得分數
			for($j=1,$score2[$i]=1;$j<=$length;$j++)
				if($EVA[$i]>$EVA[$j])
					$score2[$i]++;
					$score2[$i]=$score2[$i]+$score['score2']*($month_for_report-1)/$month_for_report;
			
			//KPI績效得分數
			for($j=1,$score3[$i]=1;$j<=$length;$j++)
				if($KPI[$i]>$KPI[$j])
					$score3[$i]++;
					$score3[$i]=$score3[$i]+$score['score3']*($month_for_report-1)/$month_for_report;

			mysql_query("UPDATE `score` SET `score1`=".$score1[$i]." , `score2`=".$score2[$i]." , `score3`=".$score3[$i]." WHERE cid='".$c[$i]."' AND year='$year' AND month='$month'") or die(mysql_error());
		}
	$result = mysql_query("SELECT * FROM `score` where cid='".$c[$i]."' AND year=($year-1) AND month=12");
	$temp = mysql_fetch_array($result);
	echo "<td>".number_format($temp[3],1)."</td>";
	echo "<td>".number_format($temp[4],1)."</td>";
	echo "<td>".number_format($temp[5],1)."</td>";
	echo "</tr>";
    $flag++;
	$i++;
	} 
?>
<table id="rank2" align='center' cellspacing='0' class="ytable" style="display:inline;">
<thead>
<tr>
	<th width = 130>加權平均得分數</th>
    <th width = 80 colspan="2">競賽名次</th>
</tr>
</thead>
<tbody>
<?php
	mysql_select_db("testabc_login");//讀ABC所有的公司名稱
	$C_name=mysql_query("SELECT DISTINCT(`CompanyID`) FROM account ORDER BY `CompanyID` ASC");
	//積分計算原也想用$C_name，但會造成無法顯示問題，故追加$cid
	$cid = mysql_query("SELECT DISTINCT(`CompanyID`) FROM account ORDER BY `CompanyID` ASC");
    $comp = mysql_fetch_array($cid);
	mysql_select_db("testabc_main");
<<<<<<< HEAD
	//echo $c[$i];
=======
>>>>>>> origin/test
	//所以積分先進行計算

	if($_GET['nowPage']){$pages=$_GET['nowPage'];}else{$pages=1;}

	for($i=1;$i<=$length;$i++){
<<<<<<< HEAD
	//while($comp_ID=mysql_fetch_array($cid)){
=======
>>>>>>> origin/test
		$sql_score=mysql_query("SELECT * FROM `score` WHERE `cid`='C0".$i."' AND `year`=$pages AND `month`=12");
		$score = mysql_fetch_array($sql_score);
		$result=mysql_query("SELECT	SUM(`bankrupt`) FROM `cash` WHERE `cid`='C0".$i."' AND `year`=$pages");
		$temp = mysql_fetch_array($result);
<<<<<<< HEAD
		//echo $temp[0];
		$score_average[$i]=2*$score[3]/5+2*$score[4]/5+$score[5]/5-($temp[0]*100);
		//echo $score_average[0];
		//$i++;
	//}
=======
		$score_average[$i]=2*$score[3]/5+2*$score[4]/5+$score[5]/5-($temp[0]*1000);
>>>>>>> origin/test
	}
	
	$flag=0;
	for($i=1;$i<=$length;$i++){
		if ($flag % 2 == 1)
		  echo "<tr class='odd' height='50'>";
		else
		  echo "<tr height=50>";
		  
			//加權平均得分數
			echo "<td>".number_format($score_average[$i],1)."</td>";
		    
			//競賽名次
			for($j=1,$rank[$i]=1;$j<=$length;$j++){
				if($score_average[$i]>$score_average[$j]){
				}
				elseif($score_average[$i]<$score_average[$j])
					$rank[$i]++;
			}
			echo "<td>".$rank[$i]."</td>";
			
			//競賽錦標
			if($rank[$i]==1)
				echo "<td><img width='25px' height='25px' src='../images/Crown.png'>冠軍</td>";
			elseif($rank[$i]==2)
				echo "<td><img width='25px' height='25px' src='../images/Crown1.png'>亞軍</td>";
			elseif($rank[$i]==3)
				echo "<td><img width='25px' height='25px' src='../images/Crown2.png'>季軍</td>";
			else echo "<td style='color:#FF0000;'>請加油!</td>";
	$flag++;
	echo "</tr>";
}
?>
</tbody>
</table>
<?php
if ($year!=1){
<<<<<<< HEAD
	echo "<h2 style='color:#008844;'>  ＊ 請注意：倒閉將會倒扣當年積分100分唷!<h2>";
=======
	echo "<h2 style='color:#008844;'>  ＊ 請注意：倒閉將會倒扣當年積分1000分唷!<h2>";
>>>>>>> origin/test
	$temp=mysql_query("SELECT MAX(`year`) FROM `state`");
    $result_temp=mysql_fetch_array($temp);
    $year=$result_temp[0];
	if(!isset($_GET['nowPage'])){
		$pages = $year-1;
	}
	else{
		$pages=$_GET['nowPage'];
	}
	echo "第";
	for($y=1;$y<=$year-1;$y++){
		echo '<a href="rankchange.php?nowPage='.$y.'">'.$y.'</a>'."&nbsp;";
	}
	echo "年";
}
else{
	echo "<h2 style='color:#FF0000;'>需期滿一年才能產生競賽排名! </h2>";
	}
<<<<<<< HEAD

=======
>>>>>>> origin/test
?>
</body> 
</html>