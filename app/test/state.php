<?php
//坦克状态的接口
interface TankState
{
	//坦克的攻击方法
	public function attack();
}

//坦克普通状态
class TankState_Tank implements TankState
{
	//坦克的攻击方法
	public function attack()
	{
		//这里简单的输出当前状态
		echo "普通状态\n";
	}
}
//坦克架起来的状态
class TankState_Siege implements TankState
{
	//坦克的攻击方法
	public function attack()
	{
		//这里简单的输出当前状态
		echo "架起来了";
	}
}
//坦克类
class Tank
{
	//状态
	public $state;
	//坦克的攻击方法
	public function __construct()
	{
		//新造出来的坦克当然是普通状态
		$this->state = new TankState_Tank();
	}
	//设置状态的方法，假设参数为玩家点击的键盘
	public function setState($key)
	{
		//如果按了s
		if($key = 's')
		{
			$this->state = new TankState_Siege();
		}
		//如果按了t
		elseif($key = 't')
		{
			$this->state = new TankState_Tank();
		}
	}
	//坦克的攻击方法
	public function attack()
	{
		//由当前状态自己来处理攻击
		$this->state->attack();
	}
}
echo '<pre>';
//新造一辆坦克
$tank = new Tank();
//假设正好有个敌人路过，坦克就以普通模式攻击了
$tank->attack();
//架起坦克
$tank->setState('s');
//坦克再次攻击，这次是架起模式
$tank->attack();
echo '</pre>';
?>
