<?
if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

// =================================
// 生成页数连接
// =================================

class pages
{
	var $tpl; // 模板类
	var $currentPage; // 当前页
	var $recordCount; // 记录总数
	var $pageSize; // 页容量	
	var $url; // 链接地址
	var $urlSuffix; // 地址后缀
	var $pageCount; // 页数
	var $urlChar; // url 变量分隔符
	var $hideDetails; // 隐藏分页详细信息

	var $linkCount = 0; // 邻接链接数
	var $pageVarName = 'page'; // 页数变量
	var $textFirstPage = "首页"; // 首页
	var $textLastPage = "尾页"; // 末页
	var $textPrevPage = "上一页"; // 前页
	var $textNextPage = "下一页"; // 后页
	var $textOverleap = "..."; // 省略字串
	var $recordName = "记录"; // 记录名称
	var $recordUnit = "条"; // 记录单位
	var $querySpliterChars; // QueryString 变量分隔符, 默认为 array ( '?', '&' )
	var $queryPageVar; // QueryString 中的页数变量, 默认为 %__PAGE__%
	var $queryEQChar; // 变量赋值符号, 默认为 “=”

	var $pageItems; // 页数链接变量
	var $pageLinks; // 页数链接 HTML
	var $linkType = 'normal'; // 链接类型 ( normal / javascript )
	var $template = 'system/pagelinks.html'; // HTML 模板
	var $templateJs = 'system/pagelinks_js.html'; // Javascript 模板

	function pages ( &$tpl, $linkCount = null, $template = null )
	{
		$this->tpl = $tpl;
		if ( $linkCount ) $this->linkCount = $linkCount;
		if ( $template ) $this->template = $template;
	}

	// 获取页数链接变量
	function init ( $recordCount, $pageSize, $pageVarName = '', $currentPage = 0, $url = '', $querySpliterChars = false, $queryPageVar = false, $queryEQChar = false )
	{
		$this->recordCount = $recordCount;
		$this->pageSize = $pageSize;
		$this->pageVarName = $pageVarName ? $pageVarName : "page";
		$this->currentPage = $currentPage;
		$this->url = $url;
	
		$this->querySpliterChars = $querySpliterChars ? $querySpliterChars : array ( '_', '_' );
		$this->queryPageVar = $queryPageVar ? $queryPageVar : '%__PAGE__%';
		$this->queryEQChar = $queryEQChar ? $queryEQChar : '_';
	}

	// 获取页数链接 HTML
	function makePageLinks ( $recordName = "记录", $recordUnit = "条" )
	{
		$this->pageItems['recordName'] = $recordName;
		$this->pageItems['recordUnit'] = $recordUnit;

		$this->tpl->assign ( array (
			"pageItems" => $this->pageItems
			) );

		if ( $this->linkType == 'javascript' )
		{
			$this->pageLinks = $this->tpl->fetch ( $this->templateJs );
		}
		else
		{
			$this->pageLinks = $this->tpl->fetch ( $this->template );
		}
		return $this->pageLinks;
	}

	function makePageItems ()
	{
		// 返回值
		// $pageItems['links'] : 页数链接
		// $pageItems['before'] : 首页 前页
		// $pageItems['after'] : 后页 末页

		// ['links']['type'] : LINK/NOLINK/CURRENTPAGE
		// ['links']['text'] : 文字
		// ['links']['link'] : 链接地址

		// 处理当前页
		if ( $this->currentPage == 0 )
		{
			$this->currentPage = intval ( $_REQUEST[$this->pageVarName] );
		}

		// 处理 url
		if ( $this->url == '' )
		{
			$this->url = $this->makeUrl ( $_REQUEST );
			$this->url .= strpos ( $this->url, $this->querySpliterChars[0] ) > 0 ? $this->querySpliterChars[1] : $this->querySpliterChars[0];
			$this->url .= $this->pageVarName . $this->queryEQChar . $this->queryPageVar;
		}

		$this->pageCount = @ceil ( $this->recordCount / $this->pageSize ); // 计算总页数
		if ( $this->pageCount == 0 )
		{
			$this->pageCount = 1;
		}

		$this->currentPage = min ( $this->pageCount, $this->currentPage );
		$this->currentPage = max ( 1, $this->currentPage );		

		if ( $this->linkCount > 0 && $this->currentPage - $this->linkCount > 1 )
		{
			// 省略 {$this->linkCount} 页以前的页数链接
			$pageItems['links'][0]['type'] = "NOLINK";
			$pageItems['links'][0]['text'] = $this->textOverleap;
		}

		if ( $this->linkCount > 0 )
		{
			$Start = max ( 1, $this->currentPage - $this->linkCount );
			$End = min ( $this->pageCount, $this->currentPage + $this->linkCount );
		}
		else
		{
			$Start = 1;
			$End = $this->pageCount;
		}

		for ( $i = $Start; $i <= $End; $i++ )
		{
			$pageItems['links'][$i]['text'] = $i;
			if ( $this->currentPage == $i )
			{
				$pageItems['links'][$i]['type'] = "CURRENTPAGE";
			}
			else
			{
				$pageItems['links'][$i]['type'] = "LINK";
				$pageItems['links'][$i]['link'] = str_replace ( $this->queryPageVar, $i, $this->url );
			}
		}
		if ( $this->linkCount > 0 && $this->currentPage + $this->linkCount < $this->pageCount )
		{
			// 省略 {$this->linkCount} 页以后的页数链接
			$pageItems['links'][$i]['type'] = "NOLINK";
			$pageItems['links'][$i]['text'] = $this->textOverleap;
		}

		$pageItems['before'][0]['text'] = $this->textFirstPage;
		$pageItems['before'][1]['text'] = $this->textPrevPage;
		$pageItems['after'][0]['text'] = $this->textNextPage;
		$pageItems['after'][1]['text'] = $this->textLastPage;
		$pageItems['before'][0]['link'] = str_replace ( $this->queryPageVar, 1, $this->url );
		$pageItems['before'][1]['link'] = str_replace ( $this->queryPageVar, $this->currentPage - 1, $this->url );
		$pageItems['after'][0]['link'] = str_replace ( $this->queryPageVar, $this->currentPage + 1, $this->url );
		$pageItems['after'][1]['link'] = str_replace ( $this->queryPageVar, $this->pageCount, $this->url );
		
		// 前页链接
		$pageItems['before'][1]['type'] = $this->currentPage > 1 ? "LINK" : "NOLINK";

		// 后页链接
		$pageItems['after'][0]['type'] = $this->currentPage < $this->pageCount ? "LINK" : "NOLINK";

		// 首页链接
		$pageItems['before'][0]['type'] = $this->currentPage - $this->linkCount > 1 ? "LINK" : "NOLINK";

		// 末页链接
		$pageItems['after'][1]['type'] = $this->currentPage + $this->linkCount < $this->pageCount ? "LINK" : "NOLINK";


		$pageItems['recordCount'] = $this->recordCount;
		$pageItems['pageCount'] = $this->pageCount;
		$pageItems['pageSize'] = $this->pageSize;
		$pageItems['currentPage'] = $this->currentPage;
		$pageItems['currentUrl'] = $this->url;
		$pageItems['offset'] = ( $this->currentPage - 1 ) * $this->pageSize;
		$pageItems['recordName'] = $this->recordName;
		$pageItems['recordUnit'] = $this->recordUnit;
		$pageItems['queryPageVar'] = $this->queryPageVar;
		$pageItems['hideDetails'] = $this->hideDetails;

		$this->pageItems = $pageItems;
		return $this->pageItems;
	}

	function makeUrl ( $Data )
	{
		unset ( $Data[$this->pageVarName] );
		while ( list ( $Key, $Val ) = @each ( $Data ) )
		{
			$query[] = $Key . '_' . urlencode ( $Val );
		}
		$url = $_SERVER['SCRIPT_NAME'] . '/' . @join ( '_', $query );
		return $url;
	}
}
?>