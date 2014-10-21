<?php
chdir ( '../' );
include ('./class/global.php');
class marine {
	public $blood = 50;
	public $kills = 10;
	static $attackNumber = 10; // 攻击力的数字 所有对象的方法
	static $num;
	// 构造函数
	function __construct() {
		// 增加总人口的代码
		self::$num ++;
	}
	
	function attack($enemy) {
		// attack code
		$enemy->blood -= self::$attackNumber;
	}
	
	function upgrade() {
		self::$attacknum ++;
	}
	
	// 析构函数
	function __destruct() {
		// 减少总人口的代码
		self::$num --;
	}

}

// 建筑类
class building {
	function fly() {
		// 建筑飞行的代码
	}
}
// 兵营类
class marineBuilding extends building {
	function createMarine() {
		// 制造机枪兵的代码
	}
}
// 坦克房类
class tankBuilding extends building {
	function createTank() {
		// 制造坦克的代码
	}
}

// class
// for( $i=0 ;$i<=100;$i++)
// {
// $m[$i] = new marine;
// $m[$i] ->attack($i-3);

// }
// Multiple annotations found at this line:
// - Write occurrence of '$dog'
// - syntax error, unexpected '$dog', expecting
// 'identifier'

// 进化的框架类，它是个抽象类
abstract class evolution {
	// 框架方法，由它来实施各个步骤，用final禁止子类覆盖
	final public function process($troop) {
		$egg = $this->becomeEgg ( $troop );
		$this->waitEgg ( $egg );
		return $this->becomeNew ( $egg );
	}
	abstract public function becomeEgg($troop);
	abstract public function waitEgg($egg);
	abstract public function becomeNew($egg);
}
// 为了简单说明，这里用空中卫士（天蟹）的进化类来演示，地刺等的处理方法类似
//天蟹的进化类继承抽象进化类
class GuardianEvolution extends evolution {
		//实现生成一个蛋
		public function becomeEgg($troop)
		{
		//销毁飞龙，返回一个蛋的对象的代码
		}
		//等待蛋孵化
		public function waitEgg($troop)
		{
		//等待几十秒钟的代码
		}
	//孵化后产生新部队
	public function becomeNew(($troop)
	{
			//销毁蛋，返回一个天蟹
	}
}
	//新建一个天蟹进化的对象
	


$e1 = new GuardianEvolution ();
// 让它调用父类的进化框架函数，自动完成三个步骤
$e1->process ( $sds );
?>

echo "
<pre>";
// de($m);
echo "</pre>
"; ?>
