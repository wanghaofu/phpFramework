<?php
/**
* @description	商店controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/28
* @modifyTime
				2012/03/28	文件创建	朱勇
*/

/****** 改变工作路径　******/
chdir('../');

/****** 载入配置文件及共用库 ******/
require_once ('./class/global.php');
require_once ('./class/lib_init_db.php'); // 初始化数据库
require_once ('./class/lib_init_cache.php'); // 初始化缓存
require_once ('./class/lib_init_tpl.php'); // 初始化模板

//$tpl = tpl::initKtpl();
$tpl = tpl::initSmarty();

$tplVars['ui_path'] = '../views/static';

$uuid = trim($IN['uuid']);
if ( ! $uuid)
{
	$uuid = 1;
}

switch ($IN['act'])
{
	/*
	* 商店列表
	*/
	case 'list':
		$shop_all = Prop::init('shop')->getAll();
		if($shop_all && is_array($shop_all))
		{
			$shop0 = array();
			$shop1 = array();
			foreach($shop_all as $shop_all_value)
			{
				${shop.$shop_all_value['is_gree']}[] = $shop_all_value;
			}
			unset($shop_all_value);
		}
		
		$tplVars['shop0'] = $shop0;
		$tplVars['shop1'] = $shop1;

		$tplname = 'shop/list.html';

		break;

	/*
	* 购买页面
	*/
	case 'buy_view':
		$shop_id = trim($IN['shop_id']);
		$num = trim($IN['num']);

		$shop = Prop::init('shop')->getOne($shop_id);
		if($shop && is_array($shop))
		{
			$price_all = $shop['price'] * $num;
		}

		$price_old = 9999;
		$price_new = $price_old - $price_all;
		
		$tplVars['shop'] = $shop;
		$tplVars['num'] = $num;
		$tplVars['price_all'] = $price_all;
		$tplVars['price_old'] = $price_old;
		$tplVars['price_new'] = $price_new;

		$tplname = 'shop/buy_view.html';

		break;

	/*
	* 购买功能
	*/
	case 'buy_action':
		$shop_id = trim($IN['shop_id']);
		$num = trim($IN['num']);

		$shop = Prop::init('shop')->getOne($shop_id);
		
		if ($shop && is_array($shop))
		{
			$price_all = $shop['price'] * $num;
		}

		if ($shop['type'] == 'props')
		{
			$param = array(
						'uuid' => $uuid,
						'props_id' => $shop['type_id'],
						'num' => $num,
						);
			
			require_once (MODEL_SRC_ROOT.'Props.php');
			$props_module = new Props($uuid);

			$props = $props_module->getProps($shop['type_id']);
			if ($props)
			{
				$param['upid'] = $props['upid'];

				//$props_module->editProps($param, TRUE);
			}
			else
			{
				//$props_module->newProps($param);
			}
		}

		if($shop['is_gree'] === '0')
		{
			require_once (MODEL_SRC_ROOT.'Session.php');

			$session_module = new Session($uuid);
			//$session_module->updateCoin('-'.$price_all, TRUE);
		}
		elseif($shop['is_gree'] === '1')
		{
		}

		$tplname = 'shop/buy_action.html';

		break;

	default:
}

tpl::show($tplname);

/* End of file shop.php */
/* Location: /controllers/shop.php */