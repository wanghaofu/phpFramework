<?php

class user {
	var $hp;
	var $atk;
	var $def;
	var $level;
	function setHp($hp) {
		$this->hp = $hp;
	}
	function setAtk($atk) {
		$rand =rand(-20,20);
		$this->atk = $atk-$rand;
	}
	function setDef($def) {
		$this->def = $def;
	}
	function setLevel($level) {
		$this->level = $level;
	}

}
class combat {
	
	var $atk;
	var $def;
	var $level;
	function combat($userAtk, $userDef) {
//		$userAtk->atk = $this->getAtk ( $userAtk );
//		$userAtk->def = $this->getDef ( $userAtk );
//		$userAtk->hp = $this->getHp ( $userHp );
//		
//		$userDef->atk = $this->getAtk ( $userDef );
//		$userDef->def = $this->getDef ( $userDef );
//		$userDef->hp = $this->getHp ( $userDef );
		echo "<pre>";
		print_r ( $userAtk );
		print_r ( $userDef );
		$i=1;
		while ( $userAtk->hp >= 0 && $userDef->hp >= 0 ) {
			$rand = rand(-20,20);
			$atk = $userAtk->atk-$rand;
			
			$userDef->hp =max(0, $userDef->hp  - $atk); //对战公式 验证版 
			echo "npc is lost {$atk} hp is {$userDef->hp}\n";
			if ($userDef->hp <= 0) {
				echo "npc is die! \n";
				return;
			}
			
			$npcatk = $userDef->atk-$rand;
			$userAtk->hp = max(0,$userAtk->hp  - $npcatk); //对战公式
			echo "user is lost {$npcatk} hp is {$userAtk->hp}\n";
			if ($userAtk->hp <= 0) {
				echo "user is die! \n";
				return;
			}
				$i ++;
			echo "################# round end {$i} #####################\n";
		
//			if ($i >= 20) {
////				break;
//			}
		}
		
		echo "</pre>";
	
	}
	function getAtk($user) {
		return $atk = $user->hp + $user->atk + $user->level;
	}
	function getDef($user) {
		return $def = $user->def * $user->level * 1.2;
	}
	
	function getHp($user) {
		return $hp = $user->level * 1.3 + $user->hp;
	}

}

$user = new user ();
$user->setHp ( 500);
$user->setAtk (78 );
$user->setDef ( 78 );
$user->setLevel ( 36 );

$user2 = new user ();
$user2->setHp ( 500);
$user2->setAtk ( 35 );
$user2->setDef ( 78 );
$user2->setLevel ( 36 );

$combat = new combat ( $user, $user2 );





