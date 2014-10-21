<?php

// $baseLv = '';
// $materialLv = '';

// $baseLv = 

// $constNum = 

// $correctA = "$materiallLv/$baseLv*1.7";
// $setting = stra::ac('setting');
// $correctA = setting['combining_formula_a']

// $correctB = ""


『Base(Lv)』
『素材(Lv)』

『补正值A』= [素材(Lv)] / [Base(Lv)] * [定数(1.7)]
『补正值B』=①①如果【Base（Lv）】比【定数（2）】高的话，就代入【定数（0）】。
			①②如果【Base（Lv）】为【定数（1）】的话，[素材（Lv）]-（[Base（Lv）]/[Base（Lv）]）
【补正值C】=[补正值B]/[素材（LV）]+[补正值B]

【无随机补正增加的经验值（%）】=①如果（[补正值A]-[补正值C]）未满【定数（0.1）】的话，则为（[补正值A]-[补正值C]）的值保留到小数点后一位。
							 ②如果（[补正值A]-[补正值C]）大于【定数（0.1）】的话，则为（[补正值A]-[补正值C]）的值
【有随机补正的增加经验值】=[无随机补正增加的经验值]x[随机值（0.9-1.1）]


$baseInfo ;
$materInfo;

$ba = $metailInfo['level'] / 


 $ac =$ba-bc;
if( $ac < 0.1 )
{
	 $exp = sprintf('.1f',$ac);
}elseif($ac > 0.1 )
{
	$exp = $ac;
}
$exp = ($p-b2)
?>