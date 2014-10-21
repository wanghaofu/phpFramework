<?php
/******************************************************************

Name: 缓存初始化操作
Author: 王涛 ( wangtao )

******************************************************************/

if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

if ( !isset ( $db ) )
{
	$db = null;
}



// 按时间生成缓存目录
function cache_by_date ()
{
	global $gCache;

	$config = $gCache->getData ( 'config' );
	if ( !$config['cache_by_date'] ) return '';
	$cachePath = date ( $config['cache_by_date'] );
	return $cachePath;
}


// 初始化缓存类
function cache_class_init ()
{
	global $gCache;
	$db = stra::db('stra');
	$gCache = new cacheData ( CACHE_PATH, $db, $GLOBALS['syncMcConfig'], $GLOBALS['storeMcConfig'] );
}

// 初始化 config, setting 等缓存
function cache_init_general ()
{
	$cacheVars = array (
	// 系统参数配置
		'config' => array (
			'selectFrom' => 's_config',
			'keyField' => 'name',
			'valueField' => 'value',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'setting' => array (
			'selectFrom' => 's_setting',
			'keyField' => 'name',
			'valueField' => 'value',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'indexrule' => array (
			'selectFrom' => 't_indexrule',
			'keyField' => 'fieldvalue',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'lotto' => array (
			'selectFrom' => 't_lotto',
			'keyField' => 'lotto_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'soldier' => array (
			'selectFrom' => 't_soldier',
			'keyField' => 'soldier_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'medal' => array (
			'selectFrom' => 't_medal',
			'keyField' => 'medal_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'area' => array (
			'selectFrom' => 't_area',
			'keyField' => 'area_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'battle' => array (
			'selectFrom' => 'r_battle',
			'keyField' => 'battle_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'mission' => array (
			'selectFrom' => 'r_mission',
			'keyField' => 'mission_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'props' => array (
			'selectFrom' => 't_props',
			'keyField' => 'props_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'weapon' => array (
			'selectFrom' => 't_weapon',
			'keyField' => 'weapon_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'nation' => array (
			'selectFrom' => 't_nation',
			'keyField' => 'nation_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'skill' => array (
			'selectFrom' => 't_skill',
			'keyField' => 'skill_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'strategy' => array (
			'selectFrom' => 't_strategy',
			'keyField' => 'stra_map_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
		'shop' => array (
			'selectFrom' => 't_shop',
			'keyField' => 'shop_id',
			'lifeTime' => -1,
			'noSync' => true,
		),
	);

	while ( list ( $key, $item ) = @each ( $cacheVars ) )
	{
		$GLOBALS['gCache']->addData ( $key, $item );
	}
}
// 初始化道具缓存
function cache_init_items ()
{
	$cacheVars = array (
	// 树类 ( 按ID索引 )
	'prop_trees' => array (
	'selectFrom' => "prop_trees",
	'keyField' => 'id',
	'lifeTime' => 0,
	'noSync' => true,
	),
	);

	while ( list ( $key, $item ) = @each ( $cacheVars ) )
	{
		$GLOBALS['gCache']->addData ( $key, $item );
	}
}



// 初始化物品行为配置
function cache_init_prop_action ()
{
	$cacheVars = array (
	// 动物 ( 按ID索引 )
	'animals_actionlist' => array (
		'selectFrom' => "animals_actionlist",
		'keyField' => 'action_id',
		'lifeTime' => 0,
		'noSync' => true,
	),
	

	);
	while ( list ( $key, $item ) = @each ( $cacheVars ) )
	{
		$GLOBALS['gCache']->addData ( $key, $item );
	}
}

cache_class_init (); //init global cache

cache_init_general ();

stra::initcache( $gCache);
?>