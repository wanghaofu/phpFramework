<?php
class CMSware
{
	function cms_nodelist( $type, $NodeID, $tplname, $ignoreNodeID = "" )
	{
		global $iWPC;
		global $db;
		global $table;
		global $SYS_ENV;
		switch ( $type )
		{
		case "sub" :
			$sql = "SELECT NodeID FROM {$table->site} WHERE ParentID='{$NodeID}' ";
			$result = $db->Execute( $sql );
			while ( !$result->EOF )
			{
				$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
				if ( $NodeInfo[notExist] )
				{
					echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
					return FALSE;
				}
				$URL = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
				$return[] = array(
					"Title" => $NInfo[Name],
					"URL" => $URL
				);
				$result->MoveNext( );
			}
			break;
		case "set" :
			$NodeIDs = explode( ",", $NodeID );
			foreach ( $NodeIDs as $key => $var )
			{
				$NInfo = $iWPC->loadNodeInfo( $var );
				if ( $NodeInfo[notExist] )
				{
					echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
					return FALSE;
				}
				$URL = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
				$return[] = array(
					"Title" => $NInfo[Name],
					"URL" => $URL
				);
			}
			break;
		}
		$template = new kTemplate( );
		$template->caching = FALSE;
		$template->template_dir = $SYS_ENV[templatePath]."/ssi/";
		$template->compile_dir = SYS_PATH."sysdata/templates_c/";
		$template->assign( "List", $return );
		$template->display( $tplname );
	}

	function cms_nav( $ignoreNodeID = "" )
	{
		global $NodeID;
		global $iWPC;
		$NodeInfo = $iWPC->loadNodeInfo( $NodeID );
		if ( $NodeInfo[notExist] )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
			return FALSE;
		}
		$NodeArray = unserialize( $NodeInfo[Nav] );
		$ignoreNodeIDs = explode( ",", $ignoreNodeID );
		foreach ( $NodeArray as $key => $var )
		{
			if ( in_array( $var[NodeID], $ignoreNodeIDs ) )
			{
				continue;
			}
			$NInfo = $iWPC->loadNodeInfo( $var[NodeID] );
			$URL = CMSware::cms_getnodeurl( $NInfo );
			if ( $key == 0 )
			{
				$Navigation = "<a href='{$URL}' >{$var[Name]}</a>";
			}
			else
			{
				$Navigation .= "&nbsp;&gt;&nbsp;<a href='{$URL}' >{$var[Name]}</a>";
			}
		}
		echo $Navigation;
	}

	function cms_getHtmlURL( $publishFileName, $NodeInfo )
	{
		global $SYS_ENV;
		if ( $NodeInfo['PublishMode'] == 1 )
		{
			$patt = "/{PSN-URL:([0-9]+)}([\\S]*)/is";
			$publishFileName = str_replace( "{NodeID}", $NodeInfo['NodeID'], $publishFileName );
			foreach ( $NodeInfo as $key => $var )
			{
				$publishFileName = str_replace( "{".$key."}", $var, $publishFileName );
			}
			if ( preg_match( "/\\{(.*)\\}/isU", $publishFileName, $match ) )
			{
				eval( "\$fun_string = {$match['1']};" );
				$publishFileName = str_replace( $match[0], $fun_string, $publishFileName );
			}
			if ( preg_match( $patt, $NodeInfo[ContentURL], $matches ) )
			{
				$PSNID = $matches[1];
				$publish_path = $matches[2];
				$psnInfo = psn_admin::getpsninfo( $PSNID );
				$url = $psnInfo[URL].$publish_path."/".$publishFileName;
			}
			else
			{
				$url = $NodeInfo[ContentURL]."/".$publishFileName;
			}
		}
		else if ( $NodeInfo['PublishMode'] == 2 || $NodeInfo['PublishMode'] == 3 )
		{
			$url = str_replace( "{NodeID}", $NodeInfo['NodeID'], $NodeInfo['IndexPortalURL'] );
			$url = str_replace( "{Page}", 0, $url );
		}
		$url = formatpublishfile( $url );
		return $url;
	}

	function cms_getNodeUrl( $NodeInfo )
	{
		global $SYS_ENV;
		$patt = "/{PSN-URL:([0-9]+)}([\\S]*)/is";
		if ( preg_match( $patt, $NodeInfo[ContentURL], $matches ) )
		{
			$PSNID = $matches[1];
			$publish_path = $matches[2];
			$psnInfo = psn_admin::getpsninfo( $PSNID );
			$url = $psnInfo[URL].$publish_path."/".$NodeInfo[IndexName];
		}
		else
		{
			$url = $NodeInfo[ContentURL]."/".$NodeInfo[IndexName];
		}
		$url = formatpublishfile( $url );
		return $url;
	}

	function cms_content( $IndexID, $tplname )
	{
		global $db;
		global $SYS_ENV;
		global $table;
		global $iWPC;
		global $db_config;
		global $cmsware;
		$NodeID = publishAdmin::getindexinfo( $IndexID, $field = "NodeID" );
		$NodeInfo = $iWPC->loadNodeInfo( $NodeID );
		if ( $NodeInfo[notExist] )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
			return FALSE;
		}
		$More = CMSware::cms_getnodeurl( $NodeInfo );
		$table_name = $db_config['table_pre'].$db_config['table_content_pre']."_".$NodeInfo[TableID];
		$sql_query = "SELECT i2.NodeID,i2.ContentID,i2.State,i2.URL,i.IndexID,i.PublishDate,i.Type,c.* FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i2.ContentID =c.ContentID  AND i2.State=1 AND i.State!=-1 AND i2.Type!=3 AND i.IndexID='{$IndexID}'";
		$result = $db->getRow( $sql_query );
		$template = new kTemplate( );
		$template->caching = FALSE;
		$template->template_dir = $SYS_ENV[templatePath]."/ssi/";
		$template->compile_dir = SYS_PATH."sysdata/templates_c/";
		$template->assign( "Content", $result );
		$template->assign( "More", $More );
		$template->display( $tplname );
	}

	function cms_list( $type, $nodeid, $num, $substr, $tableid, $tplname = "list.default.html", $articleID )
	{
		global $db;
		global $SYS_ENV;
		global $table;
		global $iWPC;
		global $db_config;
		global $cmsware;
		$PageMode = FALSE;
		if ( empty( $nodeid ) )
		{
			$list_where = "";
		}
		else if ( preg_match( "/^[0-9]+,[0-9]+/", $nodeid ) )
		{
			$list_where .= " AND i.NodeID IN({$nodeid})";
		}
		else if ( preg_match( "/^all-[0-9]+/", $nodeid ) )
		{
			$nodeid = str_replace( "all-", "", $nodeid );
			$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
			if ( $NodeInfo[notExist] )
			{
				echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
				return FALSE;
			}
			$More = cms_getnodeurl( $NodeInfo );
			$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
			$list_where .= " AND i.NodeID IN({$nodeid})";
		}
		else
		{
			$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
			if ( $NodeInfo[notExist] )
			{
				echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
				return FALSE;
			}
			$More = CMSware::cms_getnodeurl( $NodeInfo );
			$list_where = " AND i.NodeID='{$nodeid}'";
		}
		if ( empty( $articleID ) )
		{
			$list_assign = "";
		}
		else
		{
			$list_assign = " AND i.ContentID='{$articleID}'";
		}
		if ( empty( $num ) )
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
						exit( "<font color =red >Fatal Error!</font> ��ĵ����﷨��д����,�뷵���޸�!" );
					}
				}
			}
		}
		if ( !empty( $orderby ) )
		{
			$list_orderby = " ORDER BY i.Top DESC,i.{$orderby} ";
		}
		else
		{
			$list_orderby = " ORDER BY i.Top DESC,i.PublishDate ";
		}
		if ( !empty( $order ) )
		{
			$list_order = " {$order} ";
		}
		else
		{
			$list_order = " DESC ";
		}
		if ( empty( $tableid ) && !empty( $NodeInfo ) )
		{
			$tableid = $NodeInfo[TableID];
		}
		$table_name = $db_config['table_pre'].$db_config['table_content_pre']."_".$tableid;
		if ( $PageMode )
		{
			$sql_num = "SELECT Count(*) as TotalNum  FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i.IndexID =c.IndexID  AND i2.State=1 AND i.State!=-1  AND i2.Type!=3 {$list_where} {$list_assign} ";
			$result = $db->getRow( $sql_num );
			$TotalNum = $result[TotalNum];
			$TotalPage = ceil( $result[TotalNum] / $offset );
			$SYS_ENV[tpl_pagelist][run] = "yes";
			if ( empty( $SYS_ENV[tpl_pagelist][page] ) )
			{
				$SYS_ENV[tpl_pagelist][page] = 0;
			}
			$start = $SYS_ENV[tpl_pagelist][page] * $offset;
			$SYS_ENV[tpl_pagelist][page] = $SYS_ENV[tpl_pagelist][page] + 1;
			if ( $TotalNum <= $start + $offset )
			{
				$SYS_ENV[tpl_pagelist][run] = "no";
			}
			$list_limit = "Limit {$start},{$offset}";
			$list_page = list_page( $TotalPage, $SYS_ENV[tpl_pagelist][page], $SYS_ENV[tpl_pagelist][filename] );
			$cmsware['page'] = array(
				"TotalNum" => $TotalNum,
				"TotalPage" => $TotalPage,
				"CurrentPage" => $SYS_ENV[tpl_pagelist][page],
				"PageList" => $list_page
			);
		}
		$sql_query = "SELECT i2.NodeID,i2.ContentID,i2.State,i2.URL,i.IndexID,i.PublishDate,i.Type,c.* FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i2.ContentID =c.ContentID  AND i2.State=1 AND i.State!=-1 AND i2.Type!=3 {$list_where} {$list_assign}  {$list_orderby} {$list_order} {$list_limit}";
		$result = $db->Execute( $sql_query );
		while ( !$result->EOF )
		{
			$data[] = $result->fields;
			$result->MoveNext( );
		}
		switch ( $type )
		{
		case "node" :
			break;
		case "new" :
			break;
		case "hot" :
			break;
		case "comment" :
			break;
		}
		$template = new kTemplate( );
		$template->caching = FALSE;
		$template->template_dir = $SYS_ENV[templatePath]."/ssi/";
		$template->compile_dir = SYS_PATH."sysdata/templates_c/";
		$template->assign( "List", $data );
		$template->assign( "More", $More );
		$template->display( $tplname );
	}

	function cms_photolist( $type, $nodeid, $pixel, $num, $td, $substr, $tableid, $tplname = "photo_list.default.html", $articleID )
	{
		global $db;
		global $SYS_ENV;
		global $table;
		global $iWPC;
		global $db_config;
		global $cmsware;
		if ( empty( $nodeid ) )
		{
			$list_where = "";
		}
		else if ( preg_match( "/^[0-9]+,[0-9]+/", $nodeid ) )
		{
			$list_where .= " AND i.NodeID IN({$nodeid})";
		}
		else if ( preg_match( "/^all-[0-9]+/", $nodeid ) )
		{
			$nodeid = str_replace( "all-", "", $nodeid );
			$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
			if ( $NodeInfo[notExist] )
			{
				echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
				return FALSE;
			}
			$More = cms_getnodeurl( $NodeInfo );
			$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
			$list_where .= " AND i.NodeID IN({$nodeid})";
		}
		else
		{
			$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
			if ( $NodeInfo[notExist] )
			{
				echo "<B>Error:</B>The NodeID <font color=#FF0000></font> you have set does not exist!";
				return FALSE;
			}
			$More = CMSware::cms_getnodeurl( $NodeInfo );
			$list_where = " AND i.NodeID='{$nodeid}'";
		}
		if ( empty( $articleID ) )
		{
			$list_assign = "";
		}
		else
		{
			$list_assign = " AND i.ContentID='{$articleID}'";
		}
		if ( empty( $num ) )
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
						exit( "<font color =red >Fatal Error!</font> ��ĵ����﷨��д����,�뷵���޸�!" );
					}
				}
			}
		}
		if ( !empty( $orderby ) )
		{
			$list_orderby = " ORDER BY i.Top DESC,i.{$orderby} ";
		}
		else
		{
			$list_orderby = " ORDER BY i.Top DESC,i.PublishDate ";
		}
		if ( !empty( $order ) )
		{
			$list_order = " {$order} ";
		}
		else
		{
			$list_order = " DESC ";
		}
		list( $dstW, $dstH ) = explode( "*", $pixel );
		if ( empty( $tableid ) && !empty( $NodeInfo ) )
		{
			$tableid = $NodeInfo[TableID];
		}
		$table_name = $db_config['table_pre'].$db_config['table_content_pre']."_".$tableid;
		$sql = "SELECT i.*,c.* FROM {$table->content_index} i LEFT JOIN {$table_name} c ON i.IndexID =c.IndexID LEFT JOIN {$table->site} n  ON n.NodeID=i.NodeID where (UNIX_TIMESTAMP() >= PublishDate) {$list_where}  AND i.State=1 {$list_orderby} {$list_order} {$list_limit}";
		$result = $db->Execute( $sql );
		$i = 0;
		while ( !$result->EOF )
		{
			$data[$i] = $result->fields;
			$data[$i][PhotoData] = "src=\"{$data[$i][Photo]}\" width={$dstW} height={$dstH}";
			++$i;
			$result->MoveNext( );
		}
		switch ( $type )
		{
		case "node" :
			break;
		case "new" :
			break;
		case "hot" :
			break;
		case "comment" :
			break;
		}
		$template = new kTemplate( );
		$template->assign( "td", $td );
		$template->template_dir = $SYS_ENV[templatePath]."/ssi/";
		$template->compile_dir = SYS_PATH."sysdata/templates_c/";
		$template->assign( "List", $data );
		$template->assign( "More", $More );
		$template->display( $tplname );
	}

}

function CMS_COUNT( $params )
{
	global $db;
	global $SYS_ENV;
	global $table;
	global $iWPC;
	global $db_config;
	global $cmsware;
	global $PageInfo;
	global $CONTENT_MODEL_INFO;
	global $DSN_INFO;
	$PageMode = FALSE;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	$query = str_replace( "#TABLE_HEADER#", $db_config['table_pre'], $query );
	if ( empty( $nodeid ) )
	{
		$list_where = "";
	}
	else if ( preg_match( "/^[0-9]+,[0-9]+/", $nodeid ) )
	{
		$list_where .= "  i.NodeID IN({$nodeid})";
		$nodeidArray = explode( ",", $nodeid );
		$NodeInfo = $iWPC->loadNodeInfo( $nodeidArray[0] );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeidArray[0]}</font> you have set does not exist!";
			return FALSE;
		}
	}
	else if ( preg_match( "/^all-[0-9]+/", $nodeid ) )
	{
		$nodeid = str_replace( "all-", "", $nodeid );
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
		$list_where .= "   i.NodeID IN({$nodeid})";
	}
	else if ( $nodeid == "self" )
	{
		$nodeid = $GLOBALS[IN][NodeID];
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$list_where = "  i.NodeID='{$nodeid}'";
	}
	else
	{
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$list_where = "   i.NodeID='{$nodeid}'";
	}
	if ( isset( $nodeguid ) )
	{
		if ( preg_match( "/^[^,]+,[^,]+/", $nodeguid ) )
		{
			foreach ( explode( ",", $nodeguid ) as $key => $var )
			{
				$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$var}'", 2 );
				if ( $key == 0 )
				{
					$nodeids = $resultGUID['NodeID'];
				}
				else
				{
					$nodeids .= ",".$resultGUID['NodeID'];
				}
			}
			$list_where = " i.NodeID IN({$nodeids})";
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The NodeGUID <font color=#FF0000>{$var}</font> you have set does not exist!";
				return FALSE;
			}
		}
		else if ( preg_match( "/^all-[^,]+/", $nodeguid ) )
		{
			$nodeguid = str_replace( "all-", "", $nodeguid );
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", 2 );
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The NodeGUID <font color=#FF0000>{$nodeguid}</font> you have set does not exist!";
				return FALSE;
			}
			$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
			$list_where .= "  i.NodeID IN({$nodeid})";
		}
		else
		{
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", 2 );
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			$list_where = "  i.NodeID='".$resultGUID['NodeID']."'";
		}
	}
	if ( !empty( $where ) )
	{
		$where = "AND ".$where;
	}
	else
	{
		$where = "";
	}
	if ( empty( $tableid ) && !empty( $NodeInfo ) )
	{
		$tableid = $NodeInfo[TableID];
	}
	$table_name = $db_config['table_pre'].$db_config['table_publish_pre']."_".$tableid;
	$table_count = $db_config['table_pre']."plugin_base_count";
	$list_where1 = empty( $list_where ) ? "" : " AND ".$list_where;
	if ( empty( $query ) )
	{
		$sql = "SELECT {$function} as TotalNum  FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (i.PublishDate <= UNIX_TIMESTAMP() ) AND i.ParentIndexID=i2.IndexID AND i.IndexID =c.IndexID  AND i2.State=1 AND i.State!=-1 AND i2.Type!=3 {$list_where1} {$where} ";
	}
	else
	{
		$sql = $query;
	}
	$result = $db->getRow( $sql );
	return $result[TotalNum];
}

function CMS_SQL( $params )
{
	global $db;
	global $SYS_ENV;
	global $table;
	global $iWPC;
	global $db_config;
	global $cmsware;
	global $PageInfo;
	global $CONTENT_MODEL_INFO;
	global $DSN_INFO;
	$PageMode = FALSE;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	$query = str_replace( "#TABLE_HEADER#", $db_config['table_pre'], $query );
	if ( empty( $num ) )
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
	$sql_query =& $query;
	if ( $PageMode )
	{
		if ( preg_match( "/select (.*) from/isU", $sql_query, $matches ) )
		{
			$sql_num = str_replace( $matches[1], "Count(*) as TotalNum", $sql_query );
			$result = $db->getRow( $sql_num, 2 );
			$TotalNum = $result[TotalNum];
		}
		else
		{
			$TotalNum = 10;
		}
		$TotalPage = ceil( $TotalNum / $offset );
		$SYS_ENV[tpl_pagelist][run] = "yes";
		if ( empty( $SYS_ENV[tpl_pagelist][page] ) )
		{
			$SYS_ENV[tpl_pagelist][page] = 0;
		}
		$start = $SYS_ENV[tpl_pagelist][page] * $offset;
		$SYS_ENV[tpl_pagelist][page] = $SYS_ENV[tpl_pagelist][page] + 1;
		if ( $TotalNum <= $start + $offset )
		{
			$SYS_ENV[tpl_pagelist][run] = "no";
		}
		$list_limit = "Limit {$start},{$offset}";
		$list_page = list_page( $TotalPage, $SYS_ENV[tpl_pagelist][page], $SYS_ENV[tpl_pagelist][filename] );
		$cmsware['page'] = array(
			"TotalNum" => $TotalNum,
			"TotalPage" => $TotalPage,
			"CurrentPage" => $SYS_ENV[tpl_pagelist][page],
			"PageList" => $list_page,
			"PageNum" => $offset,
			"URL" => $SYS_ENV[tpl_pagelist][filename]
		);
		$PageInfo = $cmsware['page'];
	}
	$sql_query = $sql_query." ".$list_limit;
	$result = $db->Execute( $sql_query, $cache );
	while ( !$result->EOF )
	{
		if ( isset( $result->fields[NodeID] ) )
		{
			$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
			$result->fields[NodeInfo] = $NInfo;
			$result->fields[NodeName] = $NInfo[Name];
			$result->fields[NodeURL] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
		}
		$data[] = $result->fields;
		$result->MoveNext( );
	}
	return $data;
}

function CMS_LIST( $params )
{
	global $db;
	global $SYS_ENV;
	global $table;
	global $iWPC;
	global $db_config;
	global $cmsware;
	global $PageInfo;
	global $CONTENT_MODEL_INFO;
	global $DSN_INFO;
	$PageMode = FALSE;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	$isIgnore = FALSE;
	if ( !empty( $ignore ) )
	{
		$ignoreNodeIds = explode( ",", $ignore );
		$isIgnore = TRUE;
	}
	if ( empty( $nodeid ) )
	{
		$list_where = "";
	}
	else if ( preg_match( "/^[0-9]+,[0-9]+/", $nodeid ) )
	{
		$list_where .= "  i.NodeID IN({$nodeid})";
		$nodeidArray = explode( ",", $nodeid );
		$NodeInfo = $iWPC->loadNodeInfo( $nodeidArray[0] );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeidArray[0]}</font> you have set does not exist!";
			return FALSE;
		}
	}
	else if ( preg_match( "/^all-[0-9]+/", $nodeid ) )
	{
		$nodeid = str_replace( "all-", "", $nodeid );
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		foreach ( explode( "%", $NodeInfo[SubNodeID] ) as $key => $var )
		{
			if ( $isIgnore )
			{
				if ( in_array( $var, $ignoreNodeIds ) )
				{
					continue;
				}
				else
				{
					$nodeid .= ",".$var;
				}
				$nodeid = substr( $nodeid, 1 );
			}
			else if ( $key == 0 )
			{
				$nodeid = $var;
			}
			else
			{
				$nodeid .= ",".$var;
			}
		}
		if ( substr( $nodeid, 0, 1 ) == "," )
		{
			$nodeid = substr( $nodeid, 1 );
		}
		if ( substr( $nodeid, -1 ) == "," )
		{
			$nodeid = substr( $nodeid, 0, -1 );
		}
		$list_where .= "   i.NodeID IN({$nodeid})";
	}
	else if ( $nodeid == "self" )
	{
		$nodeid = $GLOBALS[IN][NodeID];
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$list_where = "  i.NodeID='{$nodeid}'";
	}
	else
	{
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$list_where = "   i.NodeID='{$nodeid}'";
	}
	if ( isset( $nodeguid ) )
	{
		if ( preg_match( "/^[^,]+,[^,]+/", $nodeguid ) )
		{
			foreach ( explode( ",", $nodeguid ) as $key => $var )
			{
				$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$var}'", 2 );
				if ( $key == 0 )
				{
					$nodeids = $resultGUID['NodeID'];
				}
				else
				{
					$nodeids .= ",".$resultGUID['NodeID'];
				}
			}
			$list_where = " i.NodeID IN({$nodeids})";
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The NodeGUID <font color=#FF0000>{$var}</font> you have set does not exist!";
				return FALSE;
			}
		}
		else if ( preg_match( "/^all-[^,]+/", $nodeguid ) )
		{
			$nodeguid = str_replace( "all-", "", $nodeguid );
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", 2 );
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The NodeGUID <font color=#FF0000>{$nodeguid}</font> you have set does not exist!";
				return FALSE;
			}
			$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
			$list_where .= "  i.NodeID IN({$nodeid})";
		}
		else
		{
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", 2 );
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			$list_where = "  i.NodeID='".$resultGUID['NodeID']."'";
		}
	}
	if ( empty( $num ) )
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
	$table_count_field = array( "Hits_Total", "Hits_Today", "Hits_Week", "Hits_Month", "Hits_Date", "CommentNum" );
	$INCLUDE_COUNT_TABLE = TRUE;
	$ORDER_BY_COUNT = FALSE;
	$orderby_field = preg_replace( "/[\\s]+DESC[\\s]*/isU", "", $orderby );
	$orderby_field = preg_replace( "/[\\s]+ASC[\\s]*/isU", "", $orderby_field );
	if ( empty( $orderby ) )
	{
		$list_orderby = " ORDER BY i.Top DESC,i.Sort DESC,i.PublishDate DESC";
	}
	else if ( $orderby == "PublishDate" )
	{
		$list_orderby = " ORDER BY i.Top DESC,i.Sort DESC,i.PublishDate DESC";
	}
	else if ( in_array( $orderby, $table_count_field ) )
	{
		$INCLUDE_COUNT_TABLE = TRUE;
		$ORDER_BY_COUNT = TRUE;
		$list_orderby = " ORDER BY {table_count}{$orderby} DESC";
	}
	else if ( in_array( $orderby_field, $table_count_field ) )
	{
		$INCLUDE_COUNT_TABLE = TRUE;
		$ORDER_BY_COUNT = TRUE;
		$list_orderby = " ORDER BY {table_count}{$orderby} ";
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
	if ( empty( $tableid ) && !empty( $NodeInfo ) )
	{
		$tableid = $NodeInfo[TableID];
	}
	$table_name = $db_config['table_pre'].$db_config['table_publish_pre']."_".$tableid;
	$table_count = $db_config['table_pre']."plugin_base_count";
	if ( !empty( $returnkey ) )
	{
		foreach ( explode( ",", $returnkey ) as $key => $var )
		{
			if ( $key == 0 )
			{
				$c_return = "c.".$var;
			}
			else
			{
				$c_return .= ",c.".$var;
			}
		}
	}
	else
	{
		$c_return .= "c.*";
	}
	if ( $PageMode )
	{
		$list_where1 = empty( $list_where ) ? "" : " AND ".$list_where;
		$sql_num = "SELECT DISTINCT Count(*) as TotalNum  FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (i.PublishDate <= UNIX_TIMESTAMP() ) AND i.ParentIndexID=i2.IndexID AND i2.IndexID =c.IndexID  AND i2.State=1 AND i.State!=-1 AND i2.Type!=3 {$list_where1} {$where} ";
		$result = $db->getRow( $sql_num, 2 );
		$TotalNum = $result[TotalNum];
		$TotalPage = ceil( $result[TotalNum] / $offset );
		$SYS_ENV[tpl_pagelist][run] = "yes";
		if ( empty( $SYS_ENV[tpl_pagelist][page] ) )
		{
			$SYS_ENV[tpl_pagelist][page] = 0;
		}
		$start = $SYS_ENV[tpl_pagelist][page] * $offset;
		$SYS_ENV[tpl_pagelist][page] = $SYS_ENV[tpl_pagelist][page] + 1;
		if ( $TotalNum <= $start + $offset )
		{
			$SYS_ENV[tpl_pagelist][run] = "no";
		}
		$list_limit = "Limit {$start},{$offset}";
		$list_page = list_page( $TotalPage, $SYS_ENV[tpl_pagelist][page], $SYS_ENV[tpl_pagelist][filename] );
		$cmsware['page'] = array(
			"TotalNum" => $TotalNum,
			"TotalPage" => $TotalPage,
			"CurrentPage" => $SYS_ENV[tpl_pagelist][page],
			"PageList" => $list_page,
			"PageNum" => $offset,
			"URL" => $SYS_ENV[tpl_pagelist][filename]
		);
		$PageInfo = $cmsware['page'];
	}
	if ( $INCLUDE_COUNT_TABLE )
	{
		if ( $ORDER_BY_COUNT )
		{
			if ( empty( $where ) )
			{
				$list_where = empty( $list_where ) ? "" : "where".$list_where;
				$list_where = str_replace( "i.", "", $list_where );
				$sql_query = "SELECT * From {$table_count} {$list_where}  {$list_orderby} {$list_limit}";
				$sql_query = str_replace( "{table_count}", "", $sql_query );
				$result = $db->Execute( $sql_query );
				while ( !$result->EOF )
				{
					$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
					$result->fields[NodeInfo] = $NInfo;
					$result->fields[NodeName] = $NInfo[Name];
					$result->fields[NodeURL] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
					$tmpResult = $db->getRow( "SELECT i.URL,i.PublishDate,i.Type,i.Sort,i.Pink, {$c_return}  FROM {$table->content_index} i ,{$table_name} c  where  i.IndexID='".$result->fields['IndexID']."' AND c.ContentID=i.ContentID" );
					$data[] = array_merge( $tmpResult, $result->fields );
					$result->MoveNext( );
				}
				return $data;
			}
			else
			{
				$list_where = empty( $list_where ) ? "" : " AND ".$list_where;
				$sql_query = "SELECT i.NodeID,i.ContentID,i.State,i.URL,i.IndexID,i.PublishDate,i.Type,i.Sort,i.Pink,  {$c_return} ,co.Hits_Total, co.Hits_Today, co.Hits_Week, co.Hits_Month, co.Hits_Date, co.CommentNum From {$table_count} co, {$table->content_index} i,{$table_name} c  where co.IndexID=i.IndexID AND c.IndexID=i.IndexID  {$list_where} {$where}  {$list_orderby} {$list_limit}";
				$sql_query = str_replace( "{table_count}", "co.", $sql_query );
			}
		}
		else
		{
			$list_where = empty( $list_where ) ? "" : " AND ".$list_where;
			$sql_query = "SELECT i2.NodeID,i2.ContentID,i2.State,i2.URL,i.IndexID,i.PublishDate,i.Type,i.Sort,i.Pink,co.*, {$c_return} FROM {$table->content_index} i,{$table->content_index} i2 , {$table_name} c , {$table_count} co  where c.IndexID=i2.IndexID AND co.IndexID=i2.IndexID AND (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i2.State=1 AND i.State=1 AND i2.Type!=3 {$list_where} {$where}  {$list_orderby} {$list_limit}";
		}
	}
	else
	{
		$list_where = empty( $list_where ) ? "" : " AND ".$list_where;
		$sql_query = "SELECT i2.NodeID,i2.ContentID,i2.State,i2.URL,i.IndexID,i.PublishDate,i.Type,i.Sort,i.Pink,co.*, {$c_return} FROM {$table->content_index} i,{$table->content_index} i2 {$table_name} c , {$table_count} co  where c.IndexID=i2.IndexID AND co.IndexID=i2.IndexID AND (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i2.State=1 AND i.State=1 AND i2.Type!=3 {$list_where} {$where}  {$list_orderby} {$list_limit}";
	}
	if ( !empty( $debug ) )
	{
		echo $sql_num;
		echo "<HR>".$sql_query."<HR>";
	}
	$result = $db->Execute( $sql_query, $cache );
	while ( !$result->EOF )
	{
		$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
		$result->fields[NodeInfo] = $NInfo;
		$result->fields[NodeName] = $NInfo[Name];
		$result->fields[NodeURL] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
		$data[] = $result->fields;
		$result->MoveNext( );
	}
	return $data;
}

function CMS_CONTENT( $params )
{
	global $db;
	global $SYS_ENV;
	global $table;
	global $iWPC;
	global $db_config;
	global $cmsware;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	if ( !empty( $returnkey ) )
	{
		foreach ( explode( ",", $returnkey ) as $key => $var )
		{
			if ( $key == 0 )
			{
				$c_return = "c.".$var;
			}
			else
			{
				$c_return .= ",c.".$var;
			}
		}
	}
	else
	{
		$c_return .= "c.*";
	}
	$pos = strpos( $indexid, "," );
	if ( empty( $indexid ) )
	{
		return FALSE;
	}
	else if ( $pos !== FALSE )
	{
		$IndexIDs = explode( ",", $indexid );
		foreach ( $IndexIDs as $key => $var )
		{
			if ( empty( $var ) )
			{
				continue;
			}
			$NodeID = publishAdmin::getindexinfo( $var, $field = "NodeID" );
			$NodeInfo = $iWPC->loadNodeInfo( $NodeID );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The IndexID <font color=#FF0000>{$indexid}</font> you have set does not exist!";
			}
			$table_name = $db_config['table_pre'].$db_config['table_publish_pre']."_".$NodeInfo[TableID];
			$sql_query = "SELECT i2.NodeID,i2.ContentID,i2.State,i2.URL,i.IndexID,i.PublishDate,i.Type,i.Sort,i.Pink, {$c_return} FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i2.IndexID =c.IndexID  AND i2.State=1 AND i.State!=-1 AND i2.Type!=3 AND i.IndexID='{$var}'";
			$result[$var] = $db->getRow( $sql_query, $cache );
			$result[$var][NodeInfo] = $NodeInfo;
			$result[$var][NodeName] = $NodeInfo[Name];
			$result[$var][NodeURL] = CMSware::cms_gethtmlurl( $NodeInfo[IndexName], $NodeInfo );
		}
	}
	else
	{
		$NodeID = publishAdmin::getindexinfo( $indexid, $field = "NodeID" );
		$NodeInfo = $iWPC->loadNodeInfo( $NodeID );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The IndexID <font color=#FF0000>{$indexid}</font> you have set does not exist!";
			return FALSE;
		}
		$table_name = $db_config['table_pre'].$db_config['table_publish_pre']."_".$NodeInfo[TableID];
		$sql_query = "SELECT i2.NodeID,i2.ContentID,i2.State,i.URL,i.IndexID,i.PublishDate,i.Type,i.Sort,i.Pink, {$c_return} FROM {$table->content_index} i,{$table->content_index} i2 ,{$table_name} c where (UNIX_TIMESTAMP() >= i.PublishDate) AND i.ParentIndexID=i2.IndexID AND i2.IndexID =c.IndexID  AND i2.State=1 AND i.State!=-1 AND i2.Type!=3 AND i.IndexID='{$indexid}'";
		$returnResult = $db->getRow( $sql_query, $cache );
		$returnResult[NodeInfo] = $NodeInfo;
		$returnResult[NodeName] = $NodeInfo[Name];
		$returnResult[NodeURL] = CMSware::cms_gethtmlurl( $NodeInfo[IndexName], $NodeInfo );
		if ( $loopmode == "1" )
		{
			$result = array( );
			$result[] = $returnResult;
		}
		else
		{
			$result = $returnResult;
		}
	}
	return $result;
}

function CMS_NODELIST( $params )
{
	global $iWPC;
	global $db;
	global $table;
	global $SYS_ENV;
	global $db_config;
	global $cmsware;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	$return = array( );
	$isIgnore = FALSE;
	if ( !empty( $ignore ) )
	{
		$ignoreNodeIds = explode( ",", $ignore );
		$isIgnore = TRUE;
	}
	if ( !empty( $orderby ) )
	{
		$list_orderby = " ORDER BY {$orderby} ";
	}
	else
	{
		$list_orderby = " Order by NodeSort DESC ";
	}
	switch ( $type )
	{
	case "son" :
	case "sub" :
		if ( $nodeid == "" && $nodeguid == "" )
		{
			$nodeid = $GLOBALS[IN][NodeID];
		}
		else if ( $nodeguid != "" )
		{
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
			$nodeid = $resultGUID['NodeID'];
		}
		$sql = "SELECT NodeID FROM {$table->site} WHERE ParentID='{$nodeid}' and Disabled=0 {$list_orderby} ";
		$result = $db->Execute( $sql, $cache );
		while ( !$result->EOF )
		{
			if ( $isIgnore && in_array( $result->fields[NodeID], $ignoreNodeIds ) )
			{
				$result->MoveNext( );
				continue;
			}
			$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
			$NInfo['URL'] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
			$NInfo['NodeURL'] = $NInfo['URL'];
			$NInfo['NodeName'] = $NInfo[Name];
			$NInfo['Title'] = $NInfo[Name];
			$return[] = $NInfo;
			$result->MoveNext( );
		}
		break;
	case "parent" :
		if ( $nodeid == "" && $nodeguid == "" )
		{
			$nodeid = $GLOBALS[IN][NodeID];
		}
		else if ( $nodeguid != "" )
		{
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
			$nodeid = $resultGUID['NodeID'];
		}
		$ThisNodeInfo = $db->getRow( "SELECT ParentID FROM {$table->site} WHERE NodeID='{$nodeid}' and Disabled=0 ", $cache );
		$ParentNodeInfo = $db->getRow( "SELECT ParentID FROM {$table->site} WHERE NodeID='".$ThisNodeInfo['ParentID']."' and Disabled=0 ", $cache );
		$sql = "SELECT NodeID FROM {$table->site} WHERE ParentID='".$ParentNodeInfo['ParentID']."' and Disabled=0 {$list_orderby} ";
		$result = $db->Execute( $sql, $cache );
		while ( !$result->EOF )
		{
			if ( $isIgnore && in_array( $result->fields[NodeID], $ignoreNodeIds ) )
			{
				$result->MoveNext( );
				continue;
			}
			$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
			$NInfo['URL'] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
			$NInfo['NodeURL'] = $NInfo['URL'];
			$NInfo['NodeName'] = $NInfo[Name];
			$NInfo['Title'] = $NInfo[Name];
			$return[] = $NInfo;
			$result->MoveNext( );
		}
		break;
	case "brother" :
		if ( $nodeid == "" && $nodeguid == "" )
		{
			$nodeid = $GLOBALS[IN][NodeID];
		}
		else if ( $nodeguid != "" )
		{
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
			$nodeid = $resultGUID['NodeID'];
		}
		$ThisNodeInfo = $db->getRow( "SELECT ParentID FROM {$table->site} WHERE NodeID='{$nodeid}' and Disabled=0 ", $cache );
		$sql = "SELECT NodeID FROM {$table->site} WHERE ParentID='".$ThisNodeInfo['ParentID']."' and Disabled=0 {$list_orderby} ";
		$result = $db->Execute( $sql, $cache );
		while ( !$result->EOF )
		{
			if ( $isIgnore && in_array( $result->fields[NodeID], $ignoreNodeIds ) )
			{
				$result->MoveNext( );
				continue;
			}
			$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
			$NInfo['URL'] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
			$NInfo['NodeURL'] = $NInfo['URL'];
			$NInfo['NodeName'] = $NInfo[Name];
			$NInfo['Title'] = $NInfo[Name];
			$return[] = $NInfo;
			$result->MoveNext( );
		}
		break;
		break;
	case "set" :
		if ( $nodeid != "" )
		{
			$NodeIDs = explode( ",", $nodeid );
			foreach ( $NodeIDs as $key => $var )
			{
				$NInfo = $iWPC->loadNodeInfo( $var );
				$NInfo['URL'] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
				$NInfo['NodeURL'] = $NInfo['URL'];
				$NInfo['NodeName'] = $NInfo[Name];
				$NInfo['Title'] = $NInfo[Name];
				$return[] = $NInfo;
			}
		}
		else if ( $nodeguid != "" )
		{
			foreach ( explode( ",", $nodeguid ) as $key => $var )
			{
				$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$var}'", $cache );
				$NInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
				$NInfo['URL'] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
				$NInfo['NodeURL'] = $NInfo['URL'];
				$NInfo['NodeName'] = $NInfo[Name];
				$NInfo['Title'] = $NInfo[Name];
				$return[] = $NInfo;
			}
		}
		break;
	}
	return $return;
}

function CMS_NODE( $params )
{
	global $iWPC;
	global $db;
	global $table;
	global $SYS_ENV;
	global $db_config;
	global $cmsware;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	if ( ( empty( $nodeid ) || $nodeid == "self" ) && $nodeguid == "" )
	{
		$nodeid = empty( $GLOBALS[IN][NodeID] ) ? $GLOBALS[NodeID] : $GLOBALS[IN][NodeID];
	}
	else if ( $nodeid == "parent" && $nodeguid == "" )
	{
		$SonNodeID = empty( $GLOBALS[IN][NodeID] ) ? $GLOBALS[NodeID] : $GLOBALS[IN][NodeID];
		$SonNodeInfo = $iWPC->loadNodeInfo( $SonNodeID );
		$nodeid = $SonNodeInfo['ParentID'];
	}
	else if ( $nodeguid != "" )
	{
		$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
		$nodeid = $resultGUID['NodeID'];
	}
	$NInfo = $iWPC->loadNodeInfo( $nodeid );
	$NInfo['URL'] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
	$NInfo['NodeURL'] = $NInfo['URL'];
	$NInfo['NodeName'] = $NInfo[Name];
	$NInfo['Title'] = $NInfo[Name];
	return $NInfo;
}

function CMS_ExtraPublish( $params )
{
	global $iWPC;
	global $db;
	global $table;
	global $SYS_ENV;
	global $db_config;
	global $cmsware;
	global $_BeanFactory;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	if ( ( empty( $nodeid ) || $nodeid == "self" ) && $nodeguid == "" )
	{
		$nodeid = empty( $GLOBALS[IN][NodeID] ) ? $GLOBALS[NodeID] : $GLOBALS[IN][NodeID];
	}
	else if ( $nodeid == "parent" && $nodeguid == "" )
	{
		$SonNodeID = empty( $GLOBALS[IN][NodeID] ) ? $GLOBALS[NodeID] : $GLOBALS[IN][NodeID];
		$SonNodeInfo = $iWPC->loadNodeInfo( $SonNodeID );
		$nodeid = $SonNodeInfo['ParentID'];
	}
	else if ( $nodeguid != "" )
	{
		$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
		$nodeid = $resultGUID['NodeID'];
	}
	if ( !empty( $where ) )
	{
		$where = "AND ".$where;
	}
	else
	{
		$where = "";
	}
	$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
	$ep =& $_BeanFactory->getBean( "extra_publish" );
	$sql = "SELECT t.*, u.uName as LastModifiedUser FROM {$table->extra_publish} t left join {$table->user} u ON   u.uId=t.LastModifiedUserID where t.NodeID='{$nodeid}' {$where} ";
	$result = $db->Execute( $sql );
	while ( !$result->EOF )
	{
		$result->fields['URL'] = $ep->getView( $result->fields['PublishID'] );
		$extraInfo[] = $result->fields;
		$result->MoveNext( );
	}
	return $extraInfo;
}

function CMS_SEARCH( $params )
{
	global $iWPC;
	global $db;
	global $table;
	global $SYS_ENV;
	global $db_config;
	global $cmsware;
	global $PageInfo;
	$PageMode = FALSE;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	if ( !empty( $returnkey ) )
	{
		foreach ( explode( ",", $returnkey ) as $key => $var )
		{
			if ( $key == 0 )
			{
				$c_return = "c.".$var;
			}
			else
			{
				$c_return .= ",c.".$var;
			}
		}
	}
	else
	{
		$c_return .= "c.*";
	}
	$exact = $exact == 1 ? TRUE : FALSE;
	if ( empty( $nodeid ) )
	{
		$list_where = "";
	}
	else if ( preg_match( "/^[0-9]+,[0-9]+/", $nodeid ) )
	{
		$list_where .= " AND i.NodeID IN({$nodeid})";
		$nodeidArray = explode( ",", $nodeid );
		$NodeInfo = $iWPC->loadNodeInfo( $nodeidArray[0] );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
	}
	else if ( preg_match( "/^all-[0-9]+/", $nodeid ) )
	{
		$nodeid = str_replace( "all-", "", $nodeid );
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
		$list_where .= " AND i.NodeID IN({$nodeid})";
	}
	else if ( $nodeid == "self" )
	{
		$nodeid = $GLOBALS[IN][NodeID];
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$list_where = " AND i.NodeID='{$nodeid}'";
	}
	else
	{
		$NodeInfo = $iWPC->loadNodeInfo( $nodeid );
		if ( !$NodeInfo )
		{
			echo "<B>Error:</B>The NodeID <font color=#FF0000>{$nodeid}</font> you have set does not exist!";
			return FALSE;
		}
		$list_where = " AND i.NodeID='{$nodeid}'";
	}
	if ( isset( $nodeguid ) )
	{
		if ( preg_match( "/^[^,]+,[^,]+/", $nodeguid ) )
		{
			foreach ( explode( ",", $nodeguid ) as $key => $var )
			{
				$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$var}'", $cache );
				if ( $key == 0 )
				{
					$nodeids = $resultGUID['NodeID'];
				}
				else
				{
					$nodeids .= ",".$resultGUID['NodeID'];
				}
				$list_where = " AND i.NodeID IN({$nodeids})";
			}
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The NodeGUID <font color=#FF0000>{$var}</font> you have set does not exist!";
				return FALSE;
			}
		}
		else if ( preg_match( "/^all-[^,]+/", $nodeguid ) )
		{
			$nodeguid = str_replace( "all-", "", $nodeguid );
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			if ( !$NodeInfo )
			{
				echo "<B>Error:</B>The NodeGUID <font color=#FF0000>{$nodeguid}</font> you have set does not exist!";
				return FALSE;
			}
			$nodeid = str_replace( "%", ",", $NodeInfo[SubNodeID] );
			$list_where .= " AND i.NodeID IN({$nodeid})";
		}
		else
		{
			$resultGUID = $db->getRow( "select * from {$table->site} where NodeGUID='{$nodeguid}'", $cache );
			$NodeInfo = $iWPC->loadNodeInfo( $resultGUID['NodeID'] );
			$list_where = " AND i.NodeID='".$resultGUID['NodeID']."'";
		}
	}
	if ( empty( $num ) )
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
	if ( empty( $orderby ) )
	{
		$list_orderby = " ORDER BY i.Top DESC,i.Sort DESC,i.PublishDate DESC";
	}
	else if ( $orderby == "PublishDate" )
	{
		$list_orderby = " ORDER BY i.Top DESC,i.Sort DESC,i.PublishDate DESC";
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
	$field = empty( $field ) ? "Keywords" : $field;
	if ( empty( $keywords ) )
	{
		echo "<!--Error:Please set the Keywords!-->";
		return FALSE;
	}
	else
	{
		$separator = empty( $separator ) ? "," : $separator;
		$separator = str_replace( "��", ",", $separator );
		$keywords = array_unique( explode( $separator, $keywords ) );
		if ( !is_array( $keywords ) )
		{
			return FALSE;
		}
		$i = 0;
		$list_where .= " AND ( ";
		foreach ( $keywords as $key => $var )
		{
			if ( $var == "" )
			{
				continue;
			}
			if ( $exact )
			{
				if ( $i == 0 )
				{
					$list_where .= " c.{$field}='{$var}' ";
				}
				else
				{
					$list_where .= "OR c.{$field}='{$var}' ";
				}
			}
			else if ( $i == 0 )
			{
				$list_where .= " c.{$field}  LIKE '%{$var}%' ";
			}
			else
			{
				$list_where .= "OR c.{$field}  LIKE '%{$var}%' ";
			}
			++$i;
		}
		$list_where .= " ) ";
	}
	if ( !empty( $ignorecontentid ) )
	{
		$list_where .= "AND c.ContentID!= {$ignorecontentid} ";
	}
	if ( empty( $tableid ) && !empty( $NodeInfo ) )
	{
		$tableid = $NodeInfo[TableID];
	}
	$table_name = $db_config['table_pre'].$db_config['table_publish_pre']."_".$tableid;
	if ( $PageMode )
	{
		$sql_num = "SELECT Count(*) as TotalNum  FROM {$table->content_index} i,{$table_name} c where (i.PublishDate <= UNIX_TIMESTAMP() ) AND i.Type!=3 AND i.IndexID =c.IndexID  AND i.State!=-1  {$list_where} {$where} ";
		$result = $db->getRow( $sql_num, 2 );
		$TotalNum = $result[TotalNum];
		$TotalPage = ceil( $result[TotalNum] / $offset );
		$SYS_ENV[tpl_pagelist][run] = "yes";
		if ( empty( $SYS_ENV[tpl_pagelist][page] ) )
		{
			$SYS_ENV[tpl_pagelist][page] = 0;
		}
		$start = $SYS_ENV[tpl_pagelist][page] * $offset;
		$SYS_ENV[tpl_pagelist][page] = $SYS_ENV[tpl_pagelist][page] + 1;
		if ( $TotalNum <= $start + $offset )
		{
			$SYS_ENV[tpl_pagelist][run] = "no";
		}
		$list_limit = "Limit {$start},{$offset}";
		$list_page = list_page( $TotalPage, $SYS_ENV[tpl_pagelist][page], $SYS_ENV[tpl_pagelist][filename] );
		$cmsware['page'] = array(
			"TotalNum" => $TotalNum,
			"TotalPage" => $TotalPage,
			"CurrentPage" => $SYS_ENV[tpl_pagelist][page],
			"PageList" => $list_page,
			"URL" => $SYS_ENV[tpl_pagelist][filename]
		);
		$PageInfo = $cmsware['page'];
	}
	$pub = new publishAdmin( );
	$sql_query = "SELECT i.NodeID,i.ContentID,i.State,i.URL,i.IndexID,i.PublishDate,i.Type,i.Sort,i.Pink, {$c_return}  FROM {$table->content_index} i ,{$table_name} c where (UNIX_TIMESTAMP() >= i.PublishDate)  AND i.IndexID =c.IndexID  AND i.State!=-1 AND i.Type!=3 {$list_where} {$where}  {$list_orderby} {$list_limit}";
	$result = $db->Execute( $sql_query, $cache );
	while ( !$result->EOF )
	{
		$NInfo = $iWPC->loadNodeInfo( $result->fields[NodeID] );
		$pub->publishInfo =& $result->fields;
		$pub->IndexID = $result->fields[ContentID];
		foreach ( $result->fields as $key => $var )
		{
			$result->fields[$key] = $var;
		}
		$result->fields[NodeInfo] = $NInfo;
		$result->fields[NodeName] = $NInfo[Name];
		$result->fields[NodeURL] = CMSware::cms_gethtmlurl( $NInfo[IndexName], $NInfo );
		$data[] = $result->fields;
		$result->MoveNext( );
	}
	return $data;
}

function CMS_COMMENT( $params )
{
	global $iWPC;
	global $db;
	global $table;
	global $SYS_ENV;
	global $db_config;
	global $PageInfo;
	global $cmsware;
	global $BeanFactory;
	extract( $params, EXTR_PREFIX_SAME, "cms_" );
	$cache = empty( $cache ) ? 0 : 2;
	$return = array( );
	$NodeID = publishAdmin::getindexinfo( $indexid, "NodeID" );
	$NodeInfo = $iWPC->loadNodeInfo( $NodeID );
	$start = empty( $start ) ? 0 : $start;
	$num = empty( $num ) ? 10 : $num;
	$hiddenip = empty( $hiddenip ) ? 1 : $hiddenip;
	$orderby = empty( $orderby ) ? "CommentID DESC" : $orderby;
	$table_comment = $db_config['table_pre']."plugin_base_comment";
	$table_count = $db_config['table_pre']."plugin_base_count";
	$sc =& $BeanFactory->getBean( "SettingCache" );
	$commentSetting = $sc->load( "plugin_base_comment" );
	if ( $commentSetting['enableCommentApprove'] == 1 )
	{
		$where = " IndexID={$indexid} AND Approved=1 ";
	}
	else
	{
		$where = " IndexID={$indexid} ";
	}
	$sql = "SELECT * FROM {$table_comment} where {$where} Order by {$orderby} LIMIT {$start},{$num}";
	$recordSet = $db->Execute( $sql, $cache );
	while ( !$recordSet->EOF )
	{
		if ( $hiddenip )
		{
			$pattern = "/^([0-9]+).([0-9]+).([0-9]+).([0-9]+)\$/";
			$replacement = "\\1.\\2.\\3.*";
			$recordSet->fields[Ip] = preg_replace( $pattern, $replacement, $recordSet->fields[Ip] );
		}
		$return[] = $recordSet->fields;
		$recordSet->MoveNext( );
	}
	$recordSet->Close( );
	$result = $db->getRow( "SELECT CommentNum FROM {$table_count}  where IndexID='{$indexid}'", $cache );
	$PageInfo['CommentNum'] = $result['CommentNum'];
	return $return;
}


?>
