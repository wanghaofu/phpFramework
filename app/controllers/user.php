<?php
/************************
功能: 俱乐部基本信息
 ************************/

/****** 改变工作路径　******/

chdir ( '..' );

/****** 载入配置文件及共用库 ******/

require_once ('./class/global.php');
require_once ('./class/lib_init_db.php'); // 初始化数据库
require_once ('./class/lib_init_cache.php'); // 初始化缓存
require_once ('./class/lib_init_tpl.php'); // 初始化模板

$tpl = tpl::initKtpl();
//stra::$cache->flush('config');
echo realpath($ss);
echo '<pre>'; 
de(__FILE__);
de(realpath(__FILE__));
de(dirname(realpath(__FILE__)));
de(basename(__FILE__));
echo '</pre>';
de(stra::ac('config'));
$user = new User ( 1 );
// // $mission = stra::ac('mission');
// // de($user);
$mission = stra::uac('user',1);
de($mission);
// $xx = stra::ac('config');


// $b = new Battle(1);;
// $b->updateBattle( 1 );


$userName ='wangtao45feddffsxdf2sdf3dfdsdfffsdfff';
$puuid = $userName;
//ss
// $idx = new Index();
// $puuid = $userName;
$uuid = Index::getUuid($puuid);
 if( empty( $uuid ) )
 {
		$uuid = Index::initPuuid($puuid);
 }

$pUuid = Index::getPuuid(49);
de($pUuid);
// $idx->initTable();


//$user =new User(12);
//$data = array('userid'=>341340,'username'=>'dxdsftest');


//$user->getUserINfo()
//{
//	$this->cache->getData('user');
//	$this->cache->get('user');
//}
//example1 


//$user->db->addData('userid','2334);
//$user->db->addData('username','testsdfsd');
//$user->db->dataInsert('user');


//example1.1 


//$user->db->addData('userid','2334);
//$user->db->addData('username','testsdfsd');
//$user->db->dataInsert('user',$where);


//example2 
// $user->db->set('username','wdfgdfangtao');
// $user->db->set('icon','icon+1',false);
// $user->db->dataUpdate('user',$data);



//example3


// $u = stra::uac ( 'user' );

$tplVars['mission'] = stra::$cache->getData('mission');

// de ( $user );
// de ( $u );


//de($user);
//$db = stra::db('snk');
//$res = $db->getRow("select * from prop");
//de($res);
//de($db);
//de($user->db);
//$user->db->select('user');


//$res = $user->db->get('user');


//$z = $user->db->getRows("select * from user where username='test'");
//$z = $db->get("prop");


//$x = $user->db->exec("update user set username='test' where uuid=1");
//$sql = $user->db->getRow("select from xx insert into  yy update  user set name = \"王涛update %\$、、\\sdf\ssd\\'ss\ s\"#@''sd' sdf' sdfgd^&*!<>[}}{';'P	 x中文字体x from name insert xx ' where uuid in (select * from user where name='xx from xx xx=\" update xx insert 'x ' from ' \" name=\xx\' )");
//$user->db->nodeQuery("insert into  user where uuid in (select * from user where active=1)");
//de($sql);
//de($user);
//de($user->cache->getData('user'));


//de($user->db);
//de($gUser);
//de($db);
//de($gCache);
//de($gCache->getData('config'));


//$s =$db->getRow('select * from config');


//$tpl = tpl::initKtpl();
//de($tpl);
// $tpl = tpl::initSmarty ();

//require_once ( './fun/op_html.php' );
//$g_tpl = $gCache->getData('config');
if (empty ( $IN ['act'] ))
	$IN ['act'] = '';
if (empty ( $IN ['cate_id'] ))
	$IN ['cate_id'] = '';
if (empty ( $IN ['privacy'] ))
	$IN ['privacy'] = '';
switch ($IN ['act']) {
	//图片独立列表
	case 'img_list' :
		//		include_once ( './modules/fo_cache.php' ); //加载缓存中心类
		$tplvar ['cate_id'] = $IN ['cate_id'];
		//sys::tpl->xx=22
		$tplname = "/album/img_list.html";
		break;
	case 'vouch_image' :
		$imageId = $IN ['image_id'];
		$IN ['vouch'] = 1;
		if (! $imageId) {
			show_message ( "非法操作！" );
		}
		$where = "image_id={$imageId}";
		
		if ($idata->dataModify ( 'images', $where )) {
			show_message ( "推荐成功！" );
		} else {
			show_message ( "错误！" );
		}
	case 'cance_vouch_image' :
		$imageId = $IN ['image_id'];
		$IN ['vouch'] = 0;
		if (! $imageId) {
			show_message ( "非法操作！" );
		}
		$where = "image_id={$imageId}";
		
		if ($idata->dataModify ( 'images', $where )) {
			show_message ( "取消推荐成功！" );
		} else {
			show_message ( "错误！" );
		}
	
	case 'sign_content' :
		//		$tplvar['album']= $albumObj->getImages( $IN['album_id']);
		$tplvar ['album_id'] = $IN ['album_id'];
		$tplvar ['privacy'] = $IN ['privacy'];
		$tplvar ['image_id'] = $IN ['image_id'];
		if (! $IN ['album_id']) {
			show_message ( '不存在的专辑！' );
		}
		
		$tplname = "/album/sign_content.html";
		break;
	case 'content' :
		$tplvar ['album_id'] = $IN ['album_id'];
		$tplvar ['privacy'] = $IN ['privacy'];
		
		if (! $IN ['album_id']) {
			show_message ( '不存在的专辑！' );
		}
		require_once ('./modules/counter');
		hit_count ( 'album', $IN ['album_id'] );
		$tplname = "/album/content.html";
		if (! $userInfo) {
			show_cache ( $tplname, 3600 );
		}
		break;
	case 'add_view' :
		$tplvar ['cate_id'] = $IN ['cate_id'];
		$tplname = '/album/add.html';
		break;
	case 'add_action' :
		$IN ['user_id'] = $gUserId;
		$idata->dataAdd ( 'album' );
		$albumId = $idata->db_insert_id;
		if ($albumId) {
			//			$foCache->setStatus('list');
			show_message ( '添加专题成功', "dialog.open('modules/album?act=add_image&cate_id={$IN['cate_id']}&album_id={$albumId}')" );
		} else {
			show_message ( '失败！' );
		}
		
		break;
	case 'add_image' :
		$albumId = $IN ['album_id'];
		$tplvar ['album_info'] = $albumObj->getAlbumInfoByAlbumId ( $albumId );
		$tplvar ['cate_id'] = $IN ['cate_id'];
		$tplvar ['album_id'] = $albumId;
		$tplname = '/album/flash.html';
		break;
	case 'delete_album' :
		if (! $albumId = $IN ['album_id']) {
			show_message ( '错误！' );
		}
		$imageInfo = $db->getRows ( "select * from images where album_id = $albumId " );
		$albumInfo = $db->getRow ( "select * from album where album_id={$albumId}" );
		if ($imageInfo) {
			foreach ( $imageInfo as $key => $img ) {
				if (file_exists ( $img ['filepath'] )) {
					unlink ( $img ['filepath'] );
				}
				if ($img ['image_id']) {
					$idata->dataDel ( 'images', 'image_id', $img ['image_id'] );
				}
			}
		}
		if ($albumInfo ['file_path']) {
			if (file_exists ( $albumInfo ['file_path'] )) {
				unlink ( $albumInfo ['file_path'] );
			}
		}
		$res = $idata->dataDel ( 'album', 'album_id', $albumId );
		break;
	case 'delete_image' :
		$imageId = $IN ['image_id'];
		$imageInfo = $db->getRow ( "select * from images where image_id = {$imageId} " );
		$albumInfo = $db->getRow ( "select album_id from images where image_id={$imageId}" );
		if ($imageInfo ['image_id']) {
			{
				if (file_exists ( $imageInfo ['file_path'] )) {
					unlink ( $imageInfo ['file_path'] );
				}
			}
		}
		if ($idata->dataDel ( 'images', 'image_id', $imageId )) {
			
			show_message ( '删除成功！' );
		} else {
			show_message ( '删除失败！' );
		}
		break;
	case 'ecit_album_descr_face' :
		$albumId = $IN ['album_id'];
		$tplvar ['album_id'] = $albumId;
		$tplname = "/album/edit_album_descr.html";
		break;
	case 'edit' :
		$albumId = $IN ['album_id'];
		if (! $albumId) {
			show_message ( "错误！" );
		}
		$where = "album_id={$albumId}";
		if ($idata->dataModify ( 'album', $where )) {
			echo '1';
			exit ();
		} else {
			show_message ( "错误！" );
		}
		break;
	case 'eidt_image_descr_face' :
		$imageId = $IN ['image_id'];
		$tplvar ['image_id'] = $imageId;
		$tplname = "/album/edit_descr.html";
		break;
	case 'eidt_image_privacy_face' :
		$imageId = $IN ['image_id'];
		$tplvar ['image_id'] = $imageId;
		$tplname = "/album/edit_privacy.html";
		break;
	case 'eidt_image_descr' :
		$imageId = $IN ['image_id'];
		if (! $imageId) {
			show_message ( "错误！" );
		}
		$where = "image_id={$imageId}";
		
		if ($idata->dataModify ( 'images', $where )) {
			$albumInfo = $db->getRow ( "select album_id from images where image_id={$imageId}" );
			echo $IN ['descr'];
			exit ();
		} else {
			show_message ( "错误！" );
		}
		break;
	case 'eidt_image_privacy' :
		$imageId = $IN ['image_id'];
		if (! $imageId) {
			show_message ( "错误！" );
		}
		$where = "image_id={$imageId}";
		
		if ($idata->dataModify ( 'images', $where )) {
			$albumInfo = $db->getRow ( "select album_id from images where image_id={$imageId}" );
			echo '1';
			exit ();
		} else {
			show_message ( "错误！" );
		}
		break;
	case 'ajax_list' :
		$tplname = "/album/ajax_list.html";
		$tpl->registerCacheFun ( 'JsOutputFormat' );
		$cacheId = 10000;
		$tpl->cache_lifetime = 60;
		$tpl->caching = false;
		$tpl->cache_dir = './sysdata/cache/';
		$db->setCacheDir ( './sysdata/cache/' );
		
		echo "document.write(\"";
		$tpl->is_cached ( $tplname, $cacheId );
		$tpl->display_cache ( $tplname, $cacheId );
		echo "\");";
		exit ();
		break;
	case 'list' :
	default :
		//		include_once ( './modules/fo_cache.php' ); //加载缓存中心类
		$tplvar ['cate_id'] = $IN ['cate_id'];
		$tplval ['privacy'] = $IN ['privacy'];
		//		$tplvar['list']=$albumObj->getAlbumListByCateId();
		//		$tplname = "/album/list.html";
		$tplname = "user/user.html";
}
tpl::show ( $tplname );
?>