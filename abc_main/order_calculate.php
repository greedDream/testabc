<?php
    include("./connMysql.php");
    if (!@mysql_select_db("testabc_main")) die("資料庫選擇失敗!");//讀ABC所有的公司ID
    mysql_query("set names 'utf8'");
    $temp=mysql_query("SELECT MAX(`year`) FROM `state`");
        $result_temp=mysql_fetch_array($temp);
        $year=$result_temp[0];
        $temp=mysql_query("SELECT MAX(`month`) FROM `state` WHERE `year`=$year;");
        $result_temp=mysql_fetch_array($temp);
        $month=$result_temp[0]-1;
        if($month==0){
            $month=12;
            $year-=1;
        }
    $para=mysql_query("SELECT * FROM `parameter_description`;");
    
    while($para_result=mysql_fetch_array($para)){
        if($para_result['name']=='price_ratio_A')
            $price_ratio_A=$para_result['value'];
        else if($para_result['name']=='quality_ratio_A')
            $quality_ratio_A=$para_result['value'];
        else if($para_result['name']=='company_image_ratio_A')
            $company_image_ratio_A=$para_result['value'];
        else if($para_result['name']=='customer_satisfaction_ratio_A')
            $customer_satisfaction_ratio_A=$para_result['value'];
        else if($para_result['name']=='price_ratio_B')
            $price_ratio_B=$para_result['value'];
        else if($para_result['name']=='quality_ratio_B')
            $quality_ratio_B=$para_result['value'];
        else if($para_result['name']=='company_image_ratio_B')
            $company_image_ratio_B=$para_result['value'];
        else if($para_result['name']=='customer_satisfaction_ratio_B')
            $customer_satisfaction_ratio_B=$para_result['value'];
    }

    $temp=mysql_query("SELECT DISTINCT(`order_no`) FROM `order_accept` WHERE `year`=$year AND `month`=$month;");
    $temp_1=mysql_fetch_array($temp);
    if(!empty($temp_1)){
        $temp=mysql_query("SELECT DISTINCT(`order_no`) FROM `order_accept` WHERE `year`=$year AND `month`=$month;");
        while($temp_1=mysql_fetch_array($temp)){
            $result_temp=mysql_query("SELECT * FROM `order_accept` WHERE `order_no`='$temp_1[0]'");
            $correspond=array('1'=>'0.92','2'=>'0.87','3'=>'0.79','4'=>'0.66','5'=>'0');
            $max=0;
            $company_name="";
            $order_no="";
            $customer_name="";
            $rank=0;
			$temp_rank=0;
            $quantity_global=0;
            while($result=mysql_fetch_array($result_temp)){
                $order_no=$result['order_no'];
                $company=$result['cid'];
                $arr=array();
                $quality=0;
                $quality_temp=0;
                $satisfaction_tmp=0;
                $service=0;
                $quantity_temp=$result['quantity'];
                $quantity_global=$result['quantity'];
                $type=explode("_",$order_no);
                $temp_1=mysql_query("SELECT `company_image` FROM `state` WHERE `cid`='$company' AND `year`=$year AND `month`=$month");
                $result_1=mysql_fetch_array($temp_1); // company_image => value

                $temp_1=mysql_query("SELECT `satisfaction` FROM `customer_satisfaction` WHERE `cid`='$company' AND `customer`='{$result['customer']}';");
                $result_tmp=mysql_fetch_array($temp_1); // satisfaction => value , according to customer`s name...
                if($result_tmp[0]!=NULL)
                    $satisfaction_tmp=$result_tmp[0];
                else
                    $satisfaction_tmp=0;
                $name=$result['customer'];
                $temp_1=mysql_query("SELECT `emphasis` FROM `customer_state` WHERE `name`='$name'");
                $result_2=mysql_fetch_array($temp_1);
                list($quality_focus,$service_focus)=split(':',$result_2[0]);

                $temp_rank=$result['quality'];
                $temp_1=mysql_query("SELECT `batch`,`quality` FROM `product_quality` WHERE `cid`='$company' AND `product`='$type[1]' AND `rank`<=$temp_rank ORDER BY `rank` DESC;");
                while($result_3=mysql_fetch_array($temp_1)){
                    print_r($result_3);
                    if($result_3['batch']>=$quantity_temp){//產品較多
                        array_push($arr,($quantity_temp/$quantity_global).':'.$result_3['quality']);
                        break;
                    }
                    else{
                        array_push($arr,($result_3['batch']/$quantity_global).':'.$result_3['quality']);
                        $quantity_temp-=$result_3['batch'];
                    }
                }
                print_r($arr);
                for($i=0;$i<sizeof($arr);$i++){
                    list($temp_1,$temp_2)=split(":",$arr[$i]);
                    echo "temp_1".$temp_1;
                    echo "temp_2".$temp_2;
                    $quality+=$temp_1*$temp_2;
                }
                $quality_temp=$quality;
                $quality=$quality*100*$quality_focus;
                $service=(6-$result['service']);//20120419修正
						$temp_correct=mysql_query("SELECT MIN(`price`) FROM `order_accept` WHERE `order_no`='$order_no'");
						$min_price=mysql_fetch_array($temp_correct);
						$temp_correct=mysql_query("SELECT MIN(`service`) FROM `order_accept` WHERE `order_no`='$order_no'");
						$min_service=mysql_fetch_array($temp_correct);
						$max_service=(6-$min_service[0]);
						$temp_correct=mysql_query("SELECT MAX(`company_image`) FROM `state` WHERE `year`=$year AND `month`=$month");
						$max_image=mysql_fetch_array($temp_correct);
                if($type[1]=="A")
                    $max_temp=((40001-$result['price'])/(40001-$min_price[0]))*100*$price_ratio_A+$quality*$quality_ratio_A+$result_1['company_image']/$max_image[0]*100*$company_image_ratio_A+(0.6*$service/$max_service*100*$service_focus+0.4*$satisfaction_tmp)*$customer_satisfaction_ratio_A;
                else if($type[1]=="B")
                    $max_temp=((20001-$result['price'])/(20001-$min_price[0]))*100*$price_ratio_B+$quality*$quality_ratio_B+$result_1['company_image']/$max_image[0]*100*$company_image_ratio_B+(0.6*$service/$max_service*100*$service_focus+0.4*$satisfaction_tmp)*$customer_satisfaction_ratio_B;
                if($max_temp>$max){
                    $max=$max_temp;
                    $company_name=$company;
                    $customer_name=$result['customer'];
					$rank=$temp_rank;
                }
            }
            echo "BID RESULT => ".$company_name."CUSTOMER => ".$customer_name;
            mysql_query("UPDATE `order_accept` SET `accept` = 1 WHERE `cid`='$company_name' AND `order_no` = '$order_no' AND `year`=$year AND `month`=$month;") or die(mysql_error());
            mysql_query("DELETE FROM `order_detail` WHERE `order_no`='$order_no';");
            $customer_temp=mysql_query("SELECT * FROM `customer_satisfaction` WHERE `cid`='$company_name' AND `customer`='$customer_name'");
            $customer_result=mysql_fetch_array($customer_temp);
            if(empty($customer_result))
                mysql_query("INSERT INTO `customer_satisfaction` VALUES ('$customer_name','$company_name',0);");
            $customer_temp=mysql_query("SELECT * FROM `customer_satisfaction` WHERE `cid`='$company_name' AND `customer`='$customer_name'");
            $customer_result=mysql_fetch_array($customer_temp);
            $customer_result=$customer_result['satisfaction'];
            $temp_final=mysql_query("SELECT * FROM `product_quality` WHERE `cid`='$company_name' AND `product`='$type[1]' AND `rank`<=$rank ORDER BY `rank` DESC;");
			echo "SELECT * FROM `product_quality` WHERE `cid`='$company_name' AND `product`='$type[1]' AND `rank`<=$rank ORDER BY `rank` DESC;<br/>";

            while($result_final=mysql_fetch_array($temp_final)){
                $index=$result_final['index'];
				$type1=$result_final['product'];
                $COGS=0;
				if($result_final['month'] == 1){
					$temp_year = $result['year']-1;
					$temp_month = 12;
				}
				else{
					$temp_year = $result['year'];
					$temp_month = $result['month']-1;
				}
                $COGS_type='product_'.$type1.'_COGS';
                $now_COGS = mysql_query("SELECT `$COGS_type` FROM `production_cost` WHERE `cid`='{$result_final['cid']}' AND `year`='$temp_year' AND `month` ='$temp_month'");
				$COGS_arr = mysql_fetch_array($now_COGS);
				
                if($result_final['batch']<=$quantity_global){
                    mysql_query("DELETE FROM `product_quality` WHERE `index`=$index;") or die(mysql_error());
                    $quantity_global-=$result_final['batch'];
					echo "SELECT * FROM `production_cost` WHERE `cid`='{$result_final['cid']}' AND `year`='$temp_year' AND `month`='$temp_month'<br/>";
                    $cost_result=mysql_query("SELECT * FROM `production_cost` WHERE `cid`='{$result_final['cid']}' AND `year`='$temp_year' AND `month`='$temp_month'");
                    $cost=mysql_fetch_array($cost_result);
                    $type1=$result_final['product'];
                    $total_cost=$cost['product_'.$type1.'_material_total']+$cost['product_'.$type1.'_direct_labor']+$cost['product_'.$type1.'_overhead'];
                    $history_result=mysql_query("SELECT `batch` FROM `product_history` WHERE `index` = $index");
                    $history_product=mysql_fetch_array($history_result);
                    $COGS=$total_cost*$result_final['batch']/$history_product[0]+$COGS_arr[$COGS_type];
                }
                else{
                    $remain=$result_final['batch']-$quantity_global;
                    //echo "remain".$remain;
                    mysql_query("UPDATE `product_quality` SET `batch` = $remain WHERE `index` = $index;") or die(mysql_error());
					
                    $cost_result=mysql_query("SELECT * FROM `production_cost` WHERE `cid`='{$result_final['cid']}' AND `year`='$temp_year' AND `month`='$temp_month'");
                    $cost=mysql_fetch_array($cost_result);
                    $type1=$result_final['product'];
                    $total_cost=$cost['product_'.$type1.'_material_total']+$cost['product_'.$type1.'_direct_labor']+$cost['product_'.$type1.'_overhead'];
                    $history_result=mysql_query("SELECT `batch` FROM `product_history` WHERE `index` = $index");
                    $history_product=mysql_fetch_array($history_result);
                    $COGS=$total_cost*$quantity_global/$history_product[0]+$COGS_arr[$COGS_type];
                    $quantity_global=0;
                }
				mysql_query("UPDATE `production_cost` SET `$COGS_type` = $COGS  WHERE `cid`={$result_final['cid']} AND `year`=$temp_year AND `month`=$temp_month");
                if($quantity_global==0)
                    break;
            }//end of while
           
            //顧客滿意度計算
            if($rank==1)
                $customer_result+=7*($quality_temp-$correspond[$rank]);
            else if($rank==2)
                $customer_result+=5*($quality_temp-$correspond[$rank]);
            else if($rank==3)
                $customer_result+=3*($quality_temp-$correspond[$rank]);
            else if($rank==4)
                $customer_result+=1.1*($quality_temp-$correspond[$rank]);
            else if($rank==5)
                $customer_result+=0.5*($quality_temp-$correspond[$rank]);
			//產品差異化→產品提升→顧客滿意度↑
			$last=0;
            $now=($year-1)*12+$month; 
            $result_temp=mysql_query("SELECT year,month FROM `order_accept` WHERE `cid` = '$company_name' AND `customer`='$customer_name' AND `order_no` Like '%$type[1]'");
            while($result=mysql_fetch_array($result_temp)){
            	$last_temp=($result[0]-1)*12+$result[1];   
            	if($last_temp>$last&&$last_temp!=$now){
            		$last=$last_temp;
            	}
            }            
            $last_year=$last/12+1;    
            $last_month=$last%12;
            if($last_month==0){
            	$last_year-=1;
            	$last_month=12;
            }     
            if($type[1]=="A"){
            	$result = mysql_query("SELECT SUM(decision1),SUM(decision2),SUM(decision3) FROM `donate` WHERE `cid` = '$company_name' AND (`year`>'$last_year' OR (`year`='$last_year' AND `month`>'$last_month'))");
            	$temp=mysql_fetch_array($result);      
            	$differentiation_A=$temp[0]+$temp[1]+$temp[2];  //總共有幾顆星(要排除上次已計算過的)
           		/*$result = mysql_query("SELECT SUM(decision1),SUM(decision2),SUM(decision3) FROM `donate` WHERE `cid` = '$company_name' AND `year`='$last_year' AND `month`>$last_month");
            	$temp=mysql_fetch_array($result);
            	$differentiation_A+=$temp[0]+$temp[1]+$temp[2];  //總共有幾顆星(要加上上次當年度當月以後的)*/
            	$customer_result+=0.01*$differentiation_A+$last_month*100;
            }
            else if($type[1]=="B"){   //暫時用原本的table
            	$result = mysql_query("SELECT SUM(decision1),SUM(decision2),SUM(decision3) FROM `share` WHERE `cid` = '$company_name' AND `year`>'$last_year'");
            	$temp=mysql_fetch_array($result);
            	$differentiation_B=$temp[0]+$temp[1]+$temp[2];
            	$result = mysql_query("SELECT SUM(decision1),SUM(decision2),SUM(decision3) FROM `share` WHERE `cid` = '$company_name' AND `year`='$last_year' AND `month`>'$last_month'");
            	$temp=mysql_fetch_array($result);
            	$differentiation_B+=$temp[0]+$temp[1]+$temp[2];
            	$customer_result+=0.01*$differentiation_B;
            }			
            mysql_query("UPDATE `customer_satisfaction` SET `satisfaction` = $customer_result WHERE `cid` = '$company_name' AND `customer`='$customer_name'");

        }
    }
    $temp=mysql_query("SELECT `customer`,`cid` FROM `order_accept` WHERE `accept`=0 AND $year AND `month`=$month");
    while($result=  mysql_fetch_array($temp)){
        $temp_result=mysql_query("SELECT `satisfaction` FROM `customer_satisfaction` WHERE `customer`='{$result['customer']}' AND `cid` ='{$result['cid']}';");
        $result_temp=mysql_fetch_array($temp_result);
        $num=$result_temp[0]-0.3;
        mysql_query("UPDATE `customer_satisfaction` SET `satisfaction` = $num WHERE `cid` = '{$result['cid']}' AND `customer`='{$result['customer']}';");
    }
?>
