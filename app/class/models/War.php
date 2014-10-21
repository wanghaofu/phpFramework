<?php
/**
 * @name 战斗模块
 * @access public
 * $author chenliang
 * $date 2012-02
 */

class War extends Init 
{
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * Boss战斗结果
	 * @param 战斗结果统计
	 */
	public function bossWar( $params )
	{
		/* 计算内容：
		 * 1.军资金消耗  2.道具消耗  3.输赢 */
		$params = Flash::impt( $params );
		$battleId = $params['battleId'];
		$coin = $params['coin'];
		$props = $params['props'];
		$bResult = $params['bResult'];
		//消耗军资金
		$_ss = new Session();
		$_ss->updateCoin( $coin );
		/**
		 * 消耗道具,预留...
		 */
		if($bResult == 1) {//Boss战胜利
			$pBattle = Prop::init('battle')->getOne( $battleId );
			$_battle = new Battle();
			//更新当前作战状态
			$_battle->doBattle( $battleId, 2 );
			//激活新作战区域
			$_battle->newBattle( $pBattle['next_battle_id'] );
		}
		//Insert Record...
	}
	
	/**
	 * PVP战斗结果
	 * @param $army_id	我方部队ID
	 * @param $f_uuid	地方玩家ID
	 * @return $pvpResult 战斗输赢 0:输 1:赢
	 */
	public function pvpWar( $army_id, $f_uuid )
	{
		//获取我方部队信息
		global $user;
		$uArmy = $user->getUserArmies( $army_id );
		//获取地方部队信息
		$f_user = new User( $f_uuid );
		$f_uArmy = $f_user->getUserArmies(1);
		//计算双方部队的实际攻防
		$_army = new Army();
		$_army->filterArmyData( $uArmy, $f_uArmy );
		//战斗结算,扣除消耗的武器数
		$weaponCostPercent = $this->_pvp( $uArmy['reality_attack'], $f_uArmy['reality_defense'] );
		foreach ($uArmy['member'] as $soldier) {
			$weaponCnt = $soldier['weapon_count'] - $soldier['weapon_count_limit'] * $weaponCostPercent;
			$weaponCnt = $weaponCnt > 0 ? $weaponCnt : 1;
			$weaponCnt = $weaponCostPercent == 1 ? 0 : $weaponCnt;
			$weaponCnt = round($weaponCnt);
			$_soldier = new Soldier();
			$_soldier->updateSoldier( array('weapon_count'=>$weaponCnt), array('id'=>$soldier['id']) );
		}
		//RETURN
		$pvpResult = $weaponCostPercent == 1 ? 0 : 1;
		return $pvpResult;
	}
	
	/**
	 * 战斗履历信息
	 */
	public function warRecord()
	{
		/**
		 * 需要返回的消息：
		 * 【基本信息】势力,等级,行动值,配属成本,友军数,士官数,经验值,军资金,友军PT,开始日,何日目,连发的日数,
		 * 【作战】任务达成,PVP攻击战时胜败,PVP防御战时胜败,PVP胜败合计,消费行动值累计,破壊した兵器数,破壊された兵器数,倒したﾎﾞｽの数,
		 * 【大战略】势力胜利回数,势力失败回数,1位を獲得した回数,2位になった回数,3位になった回数,4位になった回数,5位になった回数,6位になった回数,地雷で撃退した回数,地雷で撃退された回数,地雷を撤去した回数,PVPﾊﾞﾄﾙ進軍時勝敗,PVPﾊﾞﾄﾙ防衛時勝敗,PVPﾊﾞﾄﾙ勝敗合計,消費した行動値累計HEX総占拠数,破壊した兵器数,破壊された兵器数,
		 * 【作战&大战略】PVP进攻战胜败,PVP防御战胜败,PVP胜败合计,消费行动值累计,破坏的总兵器数,破壊された総兵器数
		 * 【士官】累计士官枚数,合成次数,合成使用的军资金,除隊回数,勋章获得数
		 * 【部队】每个部队的总攻防,史上最大的总攻防
		 * 【其他】礼炮使用次数,ﾌﾟﾚｾﾞﾝﾄした,ﾌﾟﾚｾﾞﾝﾄされた,入隊ｶﾞﾁｬ回数,Sﾁｹｯﾄｶﾞﾁｬ回数,Gﾁｹｯﾄｶﾞﾁｬ回数,S入隊ｶﾞﾁｬ回数,G入隊ｶﾞﾁｬ回数,
		 */
		global $user;
		/* 返回用户平台信息... */
		$uSession = $user->getUserSession();
		/* 返回友军数... */
		$uSoldiers = $user->getUserSoldiers();
		$uRecord = $user->getUserRecord();
		/**
		 * array_merge()...
		 */
		return $uRecord;
	}
	
	/**
	 * PVP战斗算法
	 * @param $attack	我方攻击力
	 * @param $defense	敌方防御力
	 * @return $int 武器数要减去的百分比
	 */
	private function _pvp( $attack, $defense )
	{
		$diff = $attack - $defense;
		if( $diff <= 0 ) {
			$int = 100;
		} else {
			$percent = $diff / $attack * 100;
			if( $percent >= 50 ) {
				$int = 25;
			} else if( $percent >= 25 ) {
				$int = 50;
			} else {
				$int = 75;
			}
		}
		$int = $int / 100;
		return $int;
	}
}
?>