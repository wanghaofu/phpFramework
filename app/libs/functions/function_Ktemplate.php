<?php
//example
/*
<CMS action="LIST"　return="fieldInfo" table="card_info" where="card_id=
{$cardInfo.card_id}" keyField="field_name" returnkey="field_name,field_china_name" />


<CMS action="LIST" return="list" table="{$table}"  Num="page-20"　
keyfield="{$cardInfo.key_field}" returnkey="{$returnstr}" />


<CMS action="ONE" return="ParentNodeInfo" table="node_info" where="node_id={$nodeInfo.parent_node_id}" />*/

function CMS_LIST( $params )
{
	global $db;
	//	global $SYS_ENV;
	global $PageInfo;
	global $CONTENT_MODEL_INFO;
	global $DSN_INFO;
	$PageMode = FALSE;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$Page = empty( $_GET['Page'] ) ? 0 : $_GET['Page'];
	$SYS_ENV['tpl_pagelist']['page'] = intval($Page);
	

	if ( $act ){
//		$SYS_ENV['tpl_pagelist']['filename'] = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}?act=$act";
		$SYS_ENV['tpl_pagelist']['filename'] = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?act=$act";
	}else{
//		$SYS_ENV['tpl_pagelist']['filename'] = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}?act=list";
		$SYS_ENV['tpl_pagelist']['filename'] = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?act=list";
	}


	$table_name = $table;  //获取表名
	if ( empty( $table ) )  die('parames is error!');
	$cache = empty( $cache ) ? 0 : 2;

	if ( empty( $num ) )   //提取函数限定
	{
		$list_limit = " ";
	}
	else
	{
		if ( preg_match( "/^[0-9]+,[0-9]+\$/", $num ) )
		{
			list( $start, $offset ) = explode( ",", $num );
			$list_limit = " Limit {$start},{$offset} ";
		}
		else
		{
			if ( preg_match( "/^page-[0-9]+\$/", $num ) )
			{
				$offset = str_replace( "page-", "", $num );
				$offset = ( integer )$offset;
				$PageMode = TRUE;
			}
			else
			{
				if ( preg_match( "/^[0-9]+\$/", $num ) )
				{
					$list_limit = " Limit 0,{$num} ";
				}
				else
				{
					exit( "<font color =red >Fatal Error!</font> attribute 'num' invalid" );
				}
			}
		}
	}

	$orderby_field = preg_replace( "/[\\s]+DESC[\\s]*/isU", "", $orderby );  //排序方法
	$orderby_field = preg_replace( "/[\\s]+ASC[\\s]*/isU", "", $orderby_field );  //排序方法
	if ( empty( $orderby ) )
	{
		$list_orderby = " ";
	}

	else
	{
		$list_orderby = " ORDER BY {$orderby} ";
	}
	if ( !empty( $where ) )
	{
		$where = "AND ".$where;
	}
	else
	{
		$where = "";
	}
	if ( !empty( $returnkey ) ) //返回字段
	{
		foreach ( explode( ",", $returnkey ) as $key => $var )
		{
			if ( $key == 0 )
			{
				$c_return = $var;
			}
			else
			{
				$c_return .= ",".$var;
			}
		}
	}
	else
	{
		$c_return .= "*";
	}
	if ( $PageMode )  //分页模式
	{
		$sql_num = "SELECT DISTINCT Count(*) as TotalNum  FROM `{$table_name}`  where 1 {$where} ";
		$result = $db->getRow( $sql_num );
		$TotalNum = $result['TotalNum'];
		$TotalPage = ceil( $result[TotalNum] / $offset );

		$SYS_ENV['tpl_pagelist']['run'] = "yes";   //特定配置需重新测试
		if ( empty( $SYS_ENV[tpl_pagelist][page] ) )
		{
			$SYS_ENV[tpl_pagelist][page] = 0;
		}

		if (  $SYS_ENV['tpl_pagelist']['page']==1 || empty($SYS_ENV['tpl_pagelist']['page']) ){
			$start =0;
		} else {
			$start = $SYS_ENV['tpl_pagelist']['page'] * $offset-$offset;
		}
		$SYS_ENV['tpl_pagelist']['page'] = $SYS_ENV['tpl_pagelist']['page'] ;
		if ( $TotalNum <= $start + $offset )
		{
			$SYS_ENV['tpl_pagelist']['run'] = "no";
		}
		$list_limit = "Limit {$start},{$offset}";


		//	分页信息
		//		if ( !$pagetype ){
		//			$Page = new Page($TotalNum ,$offset);
		//			$gmList = $db->select("{$table_name}", $where, $orderBy , $limit = $Page->pageCounts, $offset = $Page->rowStart, $fields = '*', $groupBy = '') ;
		//			$list_page = $Page->makePageLink();
		//			$sql_query = "SELECT {$c_return} FROM `{$table_name}`  where 1  {$where} {$list_orderby} limit {$Page->rowStart},{$Page->pageCounts}";
		//		} else {
		// 自定义分y

		//		}
		if( $pageobj ){
			//ajax module
			$sql_query = "SELECT {$c_return} FROM `{$table_name}`  where 1  {$where} {$list_orderby} {$list_limit}";
			
			$list_page = ajaxpagelist( $TotalPage, $SYS_ENV['tpl_pagelist']['page'], $SYS_ENV['tpl_pagelist']['filename'] ,$pageobj ); //从新设定动态
		}
		else{
			$sql_query = "SELECT {$c_return} FROM `{$table_name}`  where 1  {$where} {$list_orderby} {$list_limit}";
			$list_page = pagelist( $TotalPage, $SYS_ENV['tpl_pagelist']['page'], $SYS_ENV['tpl_pagelist']['filename'] ); //从新设定动态
		}

		$cms['page'] = array(
		"TotalNum" => $TotalNum,
		"TotalPage" => $TotalPage,
		"CurrentPage" => $SYS_ENV[tpl_pagelist][page],
		"PageList" => "<div class='menu'>{$list_page}</div>",
		"PageNum" => $offset,
		"URL" => $SYS_ENV[tpl_pagelist][filename]
		);
		$PageInfo = $cms['page'];
	} else {
		$sql_query = "SELECT {$c_return} FROM `{$table_name}`  where 1  {$where} {$list_orderby} {$list_limit}";
	}
	if ( !empty( $debug ) )
	{
		echo $sql_num;
		echo "<HR>".$sql_query."<HR>";
	}
	$data = $db->getRows( $sql_query );
	if ( $keyfield && $data ){
		foreach( $data as $key=>$value ){
			$value['key_field'] =  $value[$keyfield];
			$tempvar[$value[$keyfield]]= $value;
		}
		$data = $tempvar;
	}

	return $data;
}


function CMS_ONE( $params )
{
	global $db;
	//	global $SYS_ENV;
	global $PageInfo;
	global $CONTENT_MODEL_INFO;
	global $DSN_INFO;
	$PageMode = FALSE;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );

	$table_name = $table;  //获取表名
	if ( empty( $table ) )  die('parames is error!');
	$cache = empty( $cache ) ? 0 : 2;


	$orderby_field = preg_replace( "/[\\s]+DESC[\\s]*/isU", "", $orderby );  //排序方法
	$orderby_field = preg_replace( "/[\\s]+ASC[\\s]*/isU", "", $orderby_field );  //排序方法
	if ( empty( $orderby ) )
	{
		$list_orderby = " ";
	}

	else
	{
		$list_orderby = " ORDER BY {$orderby} ";
	}
	if ( !empty( $where ) )
	{
		$where = "AND ".$where;
	}
	else
	{
		$where = "";
	}
	if ( !empty( $returnkey ) ) //返回字段
	{
		foreach ( explode( ",", $returnkey ) as $key => $var )
		{
			if ( $key == 0 )
			{
				$c_return = $var;
			}
			else
			{
				$c_return .= ",".$var;
			}
		}
	}
	else
	{
		$c_return .= "*";
	}

	$sql_query = "SELECT {$c_return} FROM `{$table_name}`  where 1  {$where} {$list_orderby} limit 1";
	if ( !empty( $debug ) )
	{
		echo $sql_num;
		echo "<HR>".$sql_query."<HR>";
	}
	$data = $db->getRow( $sql_query );
	return $data;
}

?>