<?php
/**
 * @name 士官特殊技能模块
 * @author chenliang $2012-02
 */

class Skill extends Init 
{	
	//初始化
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * 按一定几率触发士官的特殊技能
	 * @param $uSoldiers 用户士官资源
	 * @param $isEnemy 是否为敌方 default 0:我方 1:敌方
	 */
	public function skillsTrigger( $uSoldiers, $isEnemy = 0 )
	{
		/**
		 * 规则注释...
		 */
		$rule = array(0=>array('1&1','2&2'), 1=>array('1&2','2&1'));
		$uSkills = array();
		foreach ($uSoldiers as $sid => $soldier) {
			if( $soldier['skill_id'] ) {//该士官拥有特殊技能
				$skillRule = $soldier['skill_obj_way'].'&'.$soldier['skill_obj_effect'];
				if( in_array($skillRule, $rule[ $isEnemy ]) ) {//特殊技能在可被触发范围内
					$rand = rand(0, 100);
					if( $rand < $soldier['skill_launch_rate'] ) {//特殊技能被命中几率而触发
						$uSkills[ $skillRule ][] = $soldier;
					}
				}
			}
		}
		return $uSkills;
	}
	
	/**
	 * 按特殊技能ID给部队成员加以特殊效果
	 * @param $uSoldiers 部队成员资源
	 * @param $skill 特殊技能资源
	 */
	public function skillEffect( &$uSoldiers, $uSkill )
	{
		$eff = $uSkill[0]['skill_obj_effect']==1 ? 'attack' : 'defense';
		$uSoldiersByNation = array();
		$uSoldiersByWeapon = array();
		foreach ($uSoldiers as $sid => $soldier) {
			$uSoldiersByNation[ $soldier['nation'] ][] = &$uSoldiers[ $sid ];
			$uSoldiersByWeapon[ $soldier['weapon_id'] ][] = &$uSoldiers[ $sid ];
		}
		foreach ($uSkill as $skill) {
			$value = $skill['skill_effect_val'] + ($skill['skill_level'] - 1) * $skill['skill_effect_val_add'];
			if( !empty($skill['nation']) ) {
				$this->_resetSoldier( $uSoldiersByNation[ $skill['skill_obj_nation'] ], $eff, $value );
				if( empty($skill['skill_obj_weapon']) ) continue;
			}
			if( !empty($skill['skill_obj_weapon']) ) {
				$this->_resetSoldier( $uSoldiersByWeapon[ $skill['skill_obj_weapon'] ], $eff, $value );
				continue;
			}
			$this->_resetSoldier( $uSoldiers, $eff, $value );
		}
	}
	
	/**
	 * 重新调整部队攻防信息
	 */
	public function resetArmy( &$army )
	{
		foreach ($army['member'] as $soldier) {
			$army['reality_attack'] = $army['reality_attack'] + $soldier['attack'] * $soldier['weapon_count'];
			$army['reality_defense'] = $army['reality_defense'] + $soldier['defense'] * $soldier['weapon_count_limit'];
		}
	}
	
	/**
	 * 重新调整士官攻防信息
	 * @param $uSoldiers 士官资源
	 * @param $effect 特殊技能效果(攻防)
	 * @param $value 特殊技能效果值
	 */
	private function _resetSoldier( &$uSoldiers, $effect, $value )
	{
		if( !empty($uSoldiers) ) {
			foreach ($uSoldiers as &$soldier) {
				$soldier[ $effect ] = round($soldier[ $effect ] * (1 + $value / 100));
			}
		}
	}
}
?>