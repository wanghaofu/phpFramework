<?php
//中介者
class Mediator
{
	//存放科技建筑的数量，为了简单说明，用静态属性，其实也可以让各个对象来处理
	public static $techBuilding;
	//根据参数$techBuildingName代表的建筑名称，返回是否存在相应的科技建筑，为了简单说明，用静态属性
	public static function isTechAllow ($techBuildingName)
	{
		//如果科技建筑数量大于零，就返回true，否则返回false
		return self::$techBuilding[$techBuildingName]>0;
	}
	//一旦科技建筑造好了或者被摧毁，调用这个方法，参数$techBuildingName代表建筑名称，$add为布尔值，true表示增加（建造），false代表减少（摧毁）
	public static function changeTech ($techBuildingName, $add)
	{
		!isset(self::$techBuilding[$techBuildingName]) ? self::$techBuilding[$techBuildingName] = 0 :self::$techBuilding[$techBuildingName];
		//建造
		if ($add)
		{
			//增加数量
			self::$techBuilding[$techBuildingName]++;
		}
		else
		{
			//减少数量
			self::$techBuilding[$techBuildingName]--;
		}
	}
}

//科技站类
class ScienceFacility
{
	//构造方法
	public function __construct()
	{
		Mediator::changeTech('ScienceFacility', true);
	}
	//析构方法
	public function  __destruct()
	{
		Mediator::changeTech('ScienceFacility', false);
	}
}
//飞机场类
class Starport
{
	//制造科技球的方法
	public function createScienceVessel ()
	{
		//询问中介者，决定是否能制造科技球
		echo Mediator::isTechAllow('ScienceFacility')?'可以制造科技球':'不能制造科技球';
	}
}
//造一个科技站
$scienceFacility1 = new ScienceFacility();
//再造一个科技站
$scienceFacility2 = new ScienceFacility();
//造一个飞机场
$starport = new Starport();
//建造科技球，结果是能够
$starport->createScienceVessel();
//一个科技站被摧毁
unset($scienceFacility1);
//这时建造科技球，结果是能够，因为还有一个科技站
$starport->createScienceVessel();
//另一个科技站被摧毁
unset($scienceFacility2);
//这时建造科技球，结果是不行
$starport->createScienceVessel();
?>
