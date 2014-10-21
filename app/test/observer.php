<?php
// 抽象的结盟类
abstract class abstractAlly {
	// 放置观察者的集合，这里以简单的数组来直观演示
	# Import method
	public $oberserverCollection;
	// 增加观察者的方法，参数为观察者（也是玩家）的名称
	public function addOberserver($oberserverName) {
		# Important method 
		$this->oberserverCollection [] = new oberserver ( $oberserverName );
	}
	// 将被攻击的电脑的名字通知各个观察者
	public function notify($beAttackedPlayerName) {
		// 把观察者的集合循环
		foreach ( $this->oberserverCollection as $oberserver ) {
			// 调用各个观察者的救援函数，参数为被攻击的电脑的名字，if用来排除被攻击的电脑的观察者
			if ($oberserver->name != $beAttackedPlayerName)
				## Import line 
				$oberserver->help ( $beAttackedPlayerName ); 
		}
	}
	abstract public function beAttacked($beAttackedPlayer);
}
// 具体的结盟类
class Ally extends abstractAlly {
	// 构造函数，将所有电脑玩家的名称的数组作为参数
	public function __construct($allPlayerName) {
		// 把所有电脑玩家的数组循环
		foreach ( $allPlayerName as $playerName ) {
			// 增加观察者，参数为各个电脑玩家的名称
			$this->addOberserver ( $playerName );
		}
	}
	// 将被攻击的电脑的名字通知各个观察者
	public function beAttacked($beAttackedPlayerName) {
		// 调用各个观察者的救援函数，参数为被攻击的电脑的名字，if用来排除被攻击的电脑的观察者
		$this->notify ( $beAttackedPlayerName );
	}
}
// 观察者的接口
interface Ioberserver {
	// 定义规范救援方法
	function help($beAttackedPlayer);
}
//具体的观察者类
class oberserver implements Ioberserver 
{
	//观察者（也是玩家）对象的名字
	public $name;
	    //构造函数，参数为观察者（也是玩家）的名称
	public function __construct($name)
	{
	    $this->name = $name;
	}
	//观察者进行救援的方法
	public function help($beAttackedPlayerName)
	{
		// 这里简单的输出，谁去救谁，最后加一个换行，便于显示
		echo $this->name . " help " . $beAttackedPlayerName . "<br>";
	}
	public function beAttacked($beAttackedPlayer){
	}
}
//假设我一对三，两家虫族，一家神族



$allComputePlayer = array (
		'Zerg1',
		'Protoss2',
		'Zerg2' 
);
// 新建电脑结盟
$Ally = new Ally ( $allComputePlayer );
// 假设我进攻了第二个虫族
$Ally->beAttacked ( 'Zerg2' );

?>