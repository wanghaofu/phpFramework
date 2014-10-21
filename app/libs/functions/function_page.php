<?php
/*
* ------------------------------------------------------------------------------------------------------
* 文章列表调用自动分页函数，用户可修改定义样式
* list_page($pagenum,$currentpage,$sendVar)
* ------------------------------------------------------------------------------------------------------
**/
function list_page($PageNum,$CurrentPage,$Url, $lang = '&#x4E0A;&#x4E00;&#x9875;,&#x4E0B;&#x4E00;&#x9875;,&#x524D;10&#x9875;,&#x4E0B;10&#x9875;') {
	list($lang_previous, $lang_next, $lang_previous10, $lang_next10) = explode(',', $lang);
	$page = "";
	$header = floor($CurrentPage/10);
	$start = $header*10;
	for($i= $start;$i<=$start + 9;$i++){
		if($i+1 > $PageNum) break;
		if($i == 0) {
			$link = $Url;
		} else
		$link = preg_replace("/\.([A-Za-z0-9]+)$/isU","_$i.\\1",$Url);
		$j = $i+1;
		if($CurrentPage-1 ==$i){
			$page.= "[{$j}]";
		}else{
			$page.= "<a href='{$link}'>{$j}</a>";
		}
	}

	if ($CurrentPage < $PageNum) {
		$link1= preg_replace("/\.([A-Za-z0-9]+)$/isU","_".$CurrentPage.".\\1",$Url);
		$page= $page."<a href='".$link1."' >".$lang_next."</a>";
	}

	if($CurrentPage > 1) { //前一页
		if(($CurrentPage-1) == 1)
		$link1 = $Url;
		else
		$link1= preg_replace("/\.([A-Za-z0-9]+)$/isU","_".($CurrentPage-2).".\\1",$Url);
		$page= "<a href='".$link1."' >".$lang_previous."</a>".$page;
	}

	if((($CurrentPage+10)) <= $PageNum && (($CurrentPage-10) <= 0)) {
		$i =  $start + 10;
		$link = preg_replace("/\.([A-Za-z0-9]+)$/isU","_$i.\\1",$Url);
		$page= $page."<a href='".$link."' >".$lang_next10."</a>";
	} elseif(($CurrentPage-10) >= 0 && ($CurrentPage+10) >= $PageNum) {
		$i =  $start - 10;
		if($i == 0)
		$i='';
		else
		$i="_$i";
		$link = preg_replace("/\.([A-Za-z0-9]+)$/isU","$i.\\1",$Url);
		$page= "<a href='".$link."' >".$lang_previous10."</a>".$page;

	}elseif((($CurrentPage-10) > 0) && (($CurrentPage+10) < $PageNum)) {
		$i =  $start - 10;
		if($i == 0)
		$i='';
		else
		$i="_$i";
		$link = preg_replace("/\.([A-Za-z0-9]+)$/isU","$i.\\1",$Url);
		$i =  $start + 10;
		$link1 = preg_replace("/\.([A-Za-z0-9]+)$/isU","_$i.\\1",$Url);


		$page= "<a href='".$link."' >".$lang_previous10."</a>".$page."<a href='".$link1."' >".$lang_next10."</a>";

	}
	return $page;

}
/**
 * 生成导航分页
 * @param int $pagenum 总页数
 * @param int $currentpage  当前页
 * @param int $sendVar 传入的URL
 *        -  php程序分页  index.php?action=list&page={page} 生成 index.php?action=list&page=, index.php?action=list&page=1, index.php?action=list&page=2
 *        -  静态分页     index{symbol}{page}.html 生成 index.html, index_1.html, index_2.html
 * @param string $symbol page标
 * @param string $code 分页的函数
 */
function Content_Page($pagenum,$currentpage,$sendVar,$symbol = '_', $code = '&#x4E0A;&#x4E00;&#x9875;,&#x4E0B;&#x4E00;&#x9875;,&#x524D;10&#x9875;,&#x4E0B;10&#x9875;') {
	$currentpage--;
	if($pagenum == '')
	return false;
	$header = floor($currentpage/10);
	$pagenum--;
	$start = $header*10;
	$code = explode(',', $code);
	for($i= $start;$i<=$start + 9;$i++){

		if($i == 0) {
			$link = str_replace("{symbol}", '', $sendVar);
			$link = str_replace("{page}", '', $link);

		} else {
			$link = str_replace("{symbol}", $symbol, $sendVar);
			$link = str_replace("{page}", $i, $link);
		}
		if($currentpage==$i){
			$page.= "<a href='".$link."' class='pageview-current' >".($i+1)."</a></span>";
//			$page.= "[".($i+1)."]";
			
//			$page.= "[".($i+1)."]";
		}else{

			$page.= "<a href='".$link."'>".($i+1)."</a>";
		}
		if($i==$pagenum) break;
	}
	if ($currentpage + 1 <= $pagenum) {
		$link1 = str_replace("{symbol}", $symbol, $sendVar);
		$link1 = str_replace("{page}", $currentpage+1, $link1);
		$page= $page."<a href='".$link1."' >{$code[1]}</a>";
	}
	if($currentpage > 0) {
		if(($currentpage-1) == 0) {
			$link1 = str_replace("{symbol}", '', $sendVar);
			$link1 = str_replace("{page}", '', $link1);
		} else {
			$link1 = str_replace("{symbol}", $symbol, $sendVar);
			$link1 = str_replace("{page}", $currentpage-1, $link1);
		}
		$page= "<a href='".$link1."' >{$code[0]}</a>".$page;
	}

	if((($currentpage+9)) < $pagenum && (($currentpage-9) <= 0)) {
		$i =  $start + 10;

		$link1 = str_replace("{symbol}", $symbol, $sendVar);
		$link1 = str_replace("{page}", $i, $link1);
		$page= $page."<a href='".$link1."' >{$code[3]}</a>";
	}elseif(($currentpage-9) >= 0 && ($currentpage+9) >= $pagenum) {
		$i =  $start - 10;
		//$i = $i<=0 ? 0 : $i;
		if($i < 0) {
		} elseif($i > 0) {
			$link = str_replace("{symbol}", $symbol, $sendVar);
			$link = str_replace("{page}", $i, $link);
			$page= "<a href='".$link."' >{$code[2]}</a>".$page;

		} elseif($i ==  0) {
			$link = str_replace("{symbol}", '', $sendVar);
			$link = str_replace("{page}", '', $link);
			$page= "<a href='".$link."' >{$code[2]}</a>".$page;

		}
	}elseif((($currentpage-9) > 0) && (($currentpage+9) < $pagenum)) {
		$i =  $start - 10;
		if($i == 0){
			$link = str_replace("{symbol}", '', $sendVar);
			$link = str_replace("{page}", '', $link);

		}else {
			$link = str_replace("{symbol}", $symbol, $sendVar);
			$link = str_replace("{page}", $i, $link);

		}
		$i =  $start + 10;
		$link1 = str_replace("{symbol}", $symbol, $sendVar);
		$link1 = str_replace("{page}", $i, $link1);
		$page= "<a href='".$link."' >{$code[2]}</a>".$page."<a href='".$link1."' >{$code[3]}</a>";
	}
	return $page;

}



function pagelist($pagenum,$currentpage,$sendVar)
{
	$pagenum = intval($pagenum);
	$currentpage = intval($currentpage);
	if($pagenum <= 0)
	return false;

	$header = floor($currentpage/10);
	$start = $header*10;
	if($start==0) {
		$start =1;
	}
	for($i= $start;$i<=$start + 9;$i++){
		$link = $sendVar."&Page=".$i;
		if($currentpage==$i){
			$page.= "<span id=\"current\">[".$i."]</span>";
		}else{
			$page.= "<a href='".$link."'>[".$i."]</a>";
		}
		if($i==$pagenum) break;
	}
	if ($currentpage < $pagenum) {
		$link1= $sendVar."&Page=".($currentpage+1);
		$page= $page."<a href='".$link1."' id='pn'>&#x4E0B;&#x4E00;&#x9875;</a>";
	}
	if($currentpage > 1) {
		if(($currentpage-1) <= 0)
		$link1 = $sendVar;
		else
		$link1= $sendVar."&Page=".($currentpage-1);
		$page= "<a href='".$link1."' id='pn'>&#x4E0A;&#x4E00;&#x9875;</a>".$page;
	}

	if((($currentpage+9)) <= $pagenum && (($currentpage-9) <= 0)) {
		$i =  $start + 9;
		$link = $sendVar."&Page=".$i;
		$page= $page."<a href='".$link."' id='pn'>&#x4E0B;10&#x9875;</a>";
	}elseif(($currentpage-9) >= 0 && ($currentpage+9) >= $pagenum) {
		$i =  $start - 9;
		if($i <= 0)
		$i='';
		$link =  $sendVar."&Page=".$i;
		$page= "<a href='".$link."'id='pn' >&#x524D;10&#x9875;</a>".$page;

	}elseif((($currentpage-9) > 0) && (($currentpage+9) < $pagenum)) {
		$i =  $start - 9;
		if($i <= 0)
		$i='';
		$link = $sendVar."&Page=".$i;
		$i =  $start + 10;
		$link1 = $sendVar."&Page=".$i;
		$page= "<a href='".$link."' id='pn' >&#x524D;10&#x9875;</a>".$page."<a href='".$link1."' id='pn' >&#x4E0B;10&#x9875;</a>";
	}
	if($currentpage>1) {
		$page = "<a href='".$sendVar."' id='fl'>[&#x9996;&#x9875;]</a>".$page;
	} else {
		$page = "<span id='fl'>[&#x9996;&#x9875;]</span>".$page;

	}

	if($currentpage!=$pagenum) {
		$page .= "<a href='".$sendVar."&Page=".$pagenum."' id='fl'>[&#x5C3E;&#x9875;]</a>";
	} else {
		$page .= "<span id='fl'>[&#x5C3E;&#x9875;]</span>";
	}

	return $page;

}



//用户ajax分页的函数
/**
 * Enter description here...
 *
 * @param unknown_type $pagenum
 * @param unknown_type $currentpage
 * @param unknown_type $sendVar
 * @param unknown_type $obj 载入列表的容器对象
 * @return unknown
 */

function ajaxpagelist($pagenum,$currentpage,$sendVar,$obj='left_list')
{
	$pagenum = intval($pagenum);
	$currentpage = intval($currentpage);
	if($pagenum <= 0)
	return false;

	$header = floor($currentpage/10);
	$start = $header*10;
	if($start==0) {
		$start =1;
	}
	for($i= $start;$i<=$start + 9;$i++){
		$link = $sendVar."&Page=".$i;
		if($currentpage==$i){
			$page.= "<font color=\"#FF0000\">[".$i."]</font>";
		}else{ 
			$page.= "<a href='javascript:;'  onclick=\"loader.get('{$link}','{$obj}');\">[".$i."]</a>";
		}
		if($i==$pagenum) break;
	}
	if ($currentpage < $pagenum) {
		$link1= $sendVar."&Page=".($currentpage+1);
		$page= $page."<a href='javascript:;'  onclick=\"loader.get('{$link1}','{$obj}');\" >&#x4E0B;&#x4E00;&#x9875;</a>";
	}
	if($currentpage > 1) {
		if(($currentpage-1) <= 0)
		$link1 = $sendVar;
		else
		$link1= $sendVar."&Page=".($currentpage-1);
		$page= "<a href='javascript:;'  onclick=\"loader.get('{$link1}','{$obj}');\" >&#x4E0A;&#x4E00;&#x9875;</a>".$page;
	}

	if((($currentpage+9)) <= $pagenum && (($currentpage-9) <= 0)) {
		$i =  $start + 9;
		$link = $sendVar."&Page=".$i;
		$page= $page."<a href='javascript:;'  onclick=\"loader.get('{$link}','{$obj}');\" >&#x4E0B;10&#x9875;</a>";
	}elseif(($currentpage-9) >= 0 && ($currentpage+9) >= $pagenum) {
		$i =  $start - 9;
		if($i <= 0)
		$i='';
		$link =  $sendVar."&Page=".$i;
		$page= "<a href='javascript:;'  onclick=\"loader.get('{$link}','{$obj}');\" >&#x524D;10&#x9875;</a>".$page;

	}elseif((($currentpage-9) > 0) && (($currentpage+9) < $pagenum)) {
		$i =  $start - 9;
		if($i <= 0)
		$i='';
		$link = $sendVar."&Page=".$i;
		$i =  $start + 10;
		$link1 = $sendVar."&Page=".$i;
		$page= "<a href='javascript:;'  onclick=\"loader.get('{$link}','{$obj}');\" >&#x524D;10&#x9875;</a>".$page."<a href='javascript:;'  onclick='loader.get('{$link1}','{$obj}');' >&#x4E0B;10&#x9875;</a>";
	}
	if($currentpage>1) {
		$page = "<a href='javascript:;'  onclick=\"loader.get('{$sendVar}','{$obj}');\">[&#x9996;&#x9875;]</a>".$page;
	} else {
		$page = "[&#x9996;&#x9875;]".$page;

	}

	if($currentpage!=$pagenum) {
		$page .= "<a href='javascript:;'  onclick=\"loader.get('{$sendVar}&Page={$pagenum}','{$obj}');\">[&#x5C3E;&#x9875;]</a>";
	} else {
		$page .= "[&#x5C3E;&#x9875;]";
	}

	return $page;

}








//--------------------------------------------
//以下为系统默认函数,一般情况不需要改动

/**
 *@ignore
 */
//系统默认动态发布调用类(如需要修改动态发布的分页的样式请修改否则不要动)
class DynamicPublish {
	function Page($pagenum,$currentpage,$sendVar)
	{
		$currentpage--;
		if($pagenum == '')
		return false;
		$header = floor($currentpage/10);

		$start = $header*10;

		for($i= $start;$i<=$start + 9;$i++){

			if($i == 0) {
				$link = str_replace("{Page}", 0,$sendVar);

			} else
			$link = str_replace("{Page}", $i,$sendVar);

			$j = $i+1;
			if($currentpage==$i){
				$page.= "<a href='".$link."'>".$j."</a>";
			}else{

				$page.= "<a href='".$link."'>".$j."</a>";
			}
			if($i==$pagenum) break;

		}
		if ($currentpage < $pagenum) {
			$link1= str_replace("{Page}", $currentpage+1 ,$sendVar);
			$page= $page."<a href='".$link1."' >&#x4E0B;&#x4E00;&#x9875;</a>";
		}

		if($currentpage > 0) {
			if(($currentpage-1) == 0)
			$link = str_replace("{Page}", 0,$sendVar);
			else
			$link1= str_replace("{Page}" , $currentpage-1 ,$sendVar);
			$page= "<a href='".$link1."' >&#x4E0A;&#x4E00;&#x9875;</a>".$page;
		}



		if((($currentpage+9)) <= $pagenum && (($currentpage-9) <= 0)) {
			$i =  $start + 10;
			$link = str_replace("{Page}", $i,$sendVar);
			$page= $page."<a href='".$link."' >&#x4E0B;10&#x9875;</a>";
		}elseif(($currentpage-9) >= 0 && ($currentpage+9) >= $pagenum) {
			$i =  $start - 9;
			if($i == 0)
			$i='';
			else
			$i="_$i";
			$link = str_replace("{Page}", $i,$sendVar);
			$page= "<a href='".$link."' >&#x524D;10&#x9875;</a>".$page;

		}elseif((($currentpage-9) > 0) && (($currentpage+9) < $pagenum)) {
			$i =  $start - 10;
			if($i == 0)
			$i='';
			else
			$i="_$i";
			$link = str_replace("{Page}", $i ,$sendVar);
			$i =  $start + 10;
			$link1 = str_replace("{Page}", $i ,$sendVar);
			$page= "<a href='".$link."' >&#x524D;10&#x9875;</a>".$page."<a href='".$link1."' >&#x4E0B;10&#x9875;</a>";
		}
		return $page;

	}
	function IndexPage()
	{
		global $PageInfo,$params,$IN;
		$pagenum = $PageInfo['TotalPage'];
		$currentpage = $PageInfo['CurrentPage'];
		$sendVar = $PageInfo['URL'];
		if($params['nodeid'] == 'self') {
			$NodeID = $GLOBALS['IN']['NodeID'];
		} else {
			$NodeID = $params['nodeid'];
		}
		$currentpage--;
		if($pagenum == '')
		return false;
		$header = floor($currentpage/10);

		$start = $header*10;
		$sendVar = str_replace("{NodeID}", $NodeID,$sendVar);
		for($i= $start;$i<=$start + 9;$i++){

			if($i == 0) {
				$link = str_replace("{Page}", 0,$sendVar);

			} else
			$link = str_replace("{Page}", $i,$sendVar);

			$j = $i+1;
			if($currentpage==$i){
				$page.= "<a href='".$link."'>".$j."</a>";
			}else{

				$page.= "<a href='".$link."'>".$j."</a>";
			}
			if($i==$pagenum) break;

		}
		if ($currentpage < $pagenum) {
			$link1= str_replace("{Page}", $currentpage+1 ,$sendVar);
			$page= $page."<a href='".$link1."' >&#x4E0B;&#x4E00;&#x9875;</a>";
		}

		if($currentpage > 0) {
			if(($currentpage-1) == 0)
			$link = str_replace("{Page}", 0,$sendVar);
			else
			$link1= str_replace("{Page}" , $currentpage-1 ,$sendVar);
			$page= "<a href='".$link1."' >&#x4E0A;&#x4E00;&#x9875;</a>".$page;
		}



		if((($currentpage+9)) <= $pagenum && (($currentpage-9) <= 0)) {
			$i =  $start + 10;
			$link = str_replace("{Page}", $i,$sendVar);
			$page= $page."<a href='".$link."' >&#x4E0B;10&#x9875;</a>";
		}elseif(($currentpage-9) >= 0 && ($currentpage+9) >= $pagenum) {
			$i =  $start - 9;
			if($i == 0)
			$i='';
			else
			$i="_$i";
			$link = str_replace("{Page}", $i,$sendVar);
			$page= "<a href='".$link."' >&#x524D;10&#x9875;</a>".$page;

		}elseif((($currentpage-9) > 0) && (($currentpage+9) < $pagenum)) {
			$i =  $start - 10;
			if($i == 0)
			$i='';
			else
			$i="_$i";
			$link = str_replace("{Page}", $i ,$sendVar);
			$i =  $start + 10;
			$link1 = str_replace("{Page}", $i ,$sendVar);
			$page= "<a href='".$link."' >&#x524D;10&#x9875;</a>".$page."<a href='".$link1."' >&#x4E0B;10&#x9875;</a>";
		}
		return $page;
	}
}


function IndexPage($pagenum,$currentpage,$sendVar)
{
	global $PageInfo,$params,$IN;
	$pagenum --;
	//$pagenum = $PageInfo['TotalPage']-1;
	//$currentpage = $PageInfo['CurrentPage'];
	//$sendVar = $PageInfo['URL'];
	if($params['nodeid'] == 'self' || $params['nodeid'] == '') {
		$NodeID = $GLOBALS['IN']['NodeID'];
	} else {
		$NodeID = $params['nodeid'];
	}
	$currentpage--;
	if($pagenum == '')
	return false;
	$header = floor($currentpage/10);

	$start = $header*10;
	$sendVar = str_replace("{NodeID}", $NodeID,$sendVar);
	$sendVar = str_replace("{fid}", $IN['fid'],$sendVar);  //modify by easyt,2005.10.24,增加fid和tid的处理
	$sendVar = str_replace("{tid}", $IN['tid'],$sendVar);
	$sendVar = str_replace("{Custom1}", $IN['Custom1'],$sendVar);  //modify by easyt,2005.10.24,增加自定义变量处理
	$sendVar = str_replace("{Custom2}", $IN['Custom2'],$sendVar);
	$sendVar = str_replace("{Custom3}", $IN['Custom3'],$sendVar);
	$sendVar = str_replace("{Custom4}", $IN['Custom4'],$sendVar);
	$sendVar = str_replace("{Custom5}", $IN['Custom5'],$sendVar);
	for($i= $start;$i<=$start + 9;$i++){

		if($i == 0) {
			$link = str_replace("{Page}", 0,$sendVar);

		} else
		$link = str_replace("{Page}", $i,$sendVar);

		$j = $i+1;
		if($currentpage==$i){
			$page.= "<FONT  COLOR='#FF0000'>[$j]</FONT>";
		}else{

			$page.= "<a href='".$link."'>".$j."</a>";
		}
		if($i==$pagenum) break;

	}
	if ($currentpage < $pagenum) {
		$link1= str_replace("{Page}", $currentpage+1 ,$sendVar);
		$page= $page."<a href='".$link1."' >&#x4E0B;&#x4E00;&#x9875;</a>";
	}

	if($currentpage > 0) {
		if(($currentpage-1) == 0)
		$link1 = str_replace("{Page}", 0,$sendVar);
		else
		$link1= str_replace("{Page}" , $currentpage-1 ,$sendVar);
		$page= "<a href='".$link1."' >&#x4E0A;&#x4E00;&#x9875;</a>".$page;
	}



	if((($currentpage+9)) <= $pagenum && (($currentpage-9) <= 0)) {
		$i =  $start + 10;
		$link = str_replace("{Page}", $i,$sendVar);
		$page= $page."<a href='".$link."' >&#x4E0B;10&#x9875;</a>";
	}elseif(($currentpage-9) >= 0 && ($currentpage+9) >= $pagenum) {
		$i =  $start - 9;
		if($i == 0)
		$i='';
		else
		$i="_$i";
		$link = str_replace("{Page}", $i,$sendVar);
		$page= "<a href='".$link."' >&#x524D;10&#x9875;</a>".$page;
	}elseif((($currentpage-9) > 0) && (($currentpage+9) < $pagenum)) {
		$i =  $start - 10;
		if($i == 0)
		$i='';
		else
		$i="_$i";
		$link = str_replace("{Page}", $i ,$sendVar);
		$i =  $start + 10;
		$link1 = str_replace("{Page}", $i ,$sendVar);
		$page= "<a href='".$link."' >&#x524D;10&#x9875;</a>".$page."<a href='".$link1."' >&#x4E0B;10&#x9875;</a>";
	}
	return $page;
}
?>