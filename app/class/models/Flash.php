<?php
/**
 * @name FLASH数据传输协议模块
 * @author chenliang $2012-02
 */

class Flash
{
	static public function expt( $array )
	{	
		$i = 0;
		$battleId = $array['battle_id'];
		$uInfo = $array['uInfo'];
		$uPropses = $array['uPropses'];
		$uArmy = $array['uArmy'];
		$pSoldier = $array['pSoldier'];
		
		$playerRenewPropCount = isset($uPropses['101']['props_count'])?$uPropses['101']['props_count']:0;
		$playerRelivePropCount = isset($uPropses['102']['props_count'])?$uPropses['102']['props_count']:0;
		$playerKillPropCount = isset($uPropses['103']['props_count'])?$uPropses['103']['props_count']:0;
		$arr[] = "msg_type=127";																//报文头
		$arr[] = "battleId=$battleId";															//作战ID
		$arr[] = "repairPrice=50";//TEST DATA													//修理费用
		$arr[] = "playerNickname={$uInfo['nickname']}";											//玩家昵称
		$arr[] = "playerMoney={$uInfo['coin']}";												//玩家军资金
		$arr[] = "playerHP={$uInfo['hp']}";														//玩家HP
		$arr[] = "playerRenewPropCount={$playerRenewPropCount}";								//恢复道具数
		$arr[] = "playerRelivePropCount={$playerRelivePropCount}";								//复活道具数
		$arr[] = "playerKillPropCount={$playerKillPropCount}";									//卫星道具数
		$arr[] = "playerTotalDamage={$uArmy['attack']}";										//部队攻击力
		$arr[] = "playerTotalDefence={$uArmy['defense']}";										//部队防御力
		foreach ($uArmy['member'] as $soldier) {
			$defence = $soldier['defense'] * $soldier['weapon_count_limit'];
			$arr[] = "soldier{$i}_id={$soldier['id']}";											//士官编号
			$arr[] = "soldier{$i}_BaseDamage={$soldier['attack']}";								//士官攻击值
			$arr[] = "soldier{$i}_Defence={$defence}";											//士官防御力
			$arr[] = "soldier{$i}_RemainWeaponCount={$soldier['weapon_count']}";				//士官当前武器数
			$arr[] = "soldier{$i}_MaxWeaponCount={$soldier['weapon_count_limit']}";				//士官最大武器数
			$i++;
		}
		$defence = $pSoldier['defense'] * $pSoldier['weapon_count_limit'];
		$arr[] = "boss_BaseDamage={$pSoldier['attack']}";										//boss攻击值
		$arr[] = "boss_Defence={$defence}";														//boss防御值
		$arr[] = "boss_RemainWeaponCount={$pSoldier['weapon_count_limit']}";					//boss当前武器数
		$arr[] = "boss_MaxWeaponCount={$pSoldier['weapon_count_limit']}";						//boss最大武器数
		$arr[] = "boss_HP=2000";//TEST DATA
		return implode('&', $arr);
	}
	
	static public function impt( $array )
	{
		$arr = array();
		$arr['battleId'] = $array['battleId'];							//作战ID
		$arr['coin'] = $array['playerMoney'];							//玩家剩余军资金
		$arr['bResult'] = $array['battle_result'];						//战斗结果
		$arr['props'] = array(
			'101'	=>	$array['playerRenewPropCount'],					//剩余恢复道具数
			'102'	=>	$array['playerRelivePropCount'],				//剩余复活道具数
			'103'	=>	$array['playerKillPropCount']					//剩余卫星道具数
		);
		return $arr;
	}
	
	static public function _expt( $array )
	{
		self::_encode( $array );
		$json = urldecode(json_encode($array));
		$json = str_replace('"','',$json);
		return $json;
	}
	
	/**
	 * @name 数据封包逻辑
	 * 根据客户端约定的格式
	 * @access private
	 * @param $array
	 */
	static private function _encode( &$array )
	{
		foreach ($array as &$arr) {
			if(is_array($arr)) {
				self::_encode($arr);
			} else {
				$arr = urlencode($arr);
			}
		}
	}
	
	/**
	 * @name 数据解包逻辑
	 * 根据客户端约定的格式
	 * @access private
	 * @param $array
	 */
	static private function _decode( &$array )
	{
		//...预留
	}
}
?>