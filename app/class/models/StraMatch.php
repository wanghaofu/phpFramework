<?php
/**
 * @name 大战略玩家配对算法
 * $author chenliang
 * $date 2012-02
 */

class StraMatch extends Init 
{	
	/**
	 * 大战略玩家个人状态资源
	 */
	public $session = array();
	
	public function __construct( $session )
	{
		parent::__construct(0);
		$this->session = $session;
	}
	
	/**
	 * 部队匹配算法
	 */
	public function forceMatch()
	{
		/* 匹配 Start */
		$time = time();
		$matchResFlag = false;
		$uuids = array($this->uuid);
		$uLevel = $this->session['level'];
		$uForce = $this->session['force'];
		$uTime = $this->session['ctime'];
		$isLast = ($time - $uTime) >= 99999999 ? true : false;//40秒之后自动由系统匹配
		if( $res = $this->_dbMatch( $uuids, $uLevel, $uLevel, $uForce ) ) {
			$resCnt = count($res);
			if( $resCnt == 2 ) {//匹配成功
				$uuids = array_merge($uuids, array_keys($res));
				$matchResFlag = true;
			} elseif($resCnt == 1) {//匹配未完成,扩大匹配范围
				$uuids = array_merge($uuids, array_keys($res));
				$res_repeat = $this->_dbMatch( $uuids, $uLevel, $res[ $uuids[1] ]['level'], $res[ $uuids[1] ]['force'], 1 );
				if( count($res_repeat) == 1 ) {//二次匹配成功
					$uuids = array_merge($uuids, array_keys($res_repeat));
					$matchResFlag = true;
				}
			}
		}
		if( !$matchResFlag ) {//匹配失败
			if( !$isLast ) {//匹配未超时
				return false;
			} else {
				//匹配超时,开启系统匹配
				if( !$res ) $res = array();
				$comRes = $this->_matchByCom( 2-count($res) );
				$res = array_merge($res, $comRes);
				$uuids = array_merge($uuids, array_keys($res));
			}
		}
		/* 匹配 End */
		/*******************************************************************/
		/* 配对 Start */
		$_stra_team = new StraTeam();
		$force_serial_num = $_stra_team->teamAdd( $uuids );
		foreach ($res as $rs) {
			$uLevel += $rs['level'];
		}
		$ave_level = $uLevel / 3;
		$this->_stra_udb->set('ave_level', $ave_level);//计算部队平均等级
		$this->_stra_udb->set('force_serial_num', $force_serial_num);//生成派系系列号
		$this->_stra_udb->dataUpdate('stra_session',"uuid in (".implode(',', $uuids).")");//执行
		/* 配对 End */
		/*******************************************************************/
		//return
		$this->session['ave_level'] = $ave_level;
		$this->session['force_serial_num'] = $force_serial_num;
		return true;
	}
	
	/**
	 * 派系匹配算法
	 */
	public function straMatch()
	{
		/* 匹配 Start */
		$time = time();
		$forceIds = array($this->session['force_serial_num']);
		$force = $this->session['force'];
		$ave_level = $this->session['ave_level'];
		$force_serial_num = $this->session['force_serial_num'];
		$uTime = $this->session['ctime'];
		$isLast = ($time - $uTime) >= 99999999 ? true : false;//20秒之后自动由系统匹配
		//查找匹配的敌方队伍
		$where[] = "`force_serial_num` not in ($force_serial_num,0)";	//已加入其他编组
		$where[] = "`force`<>$force";									//与我方不同派系
		$where[] = "`stra_serial_num`=0";								//还未进入副本
		$where[] = "`ave_level`<($ave_level+5)";						//部队平均等级±5
		$where[] = "`ave_level`>($ave_level-5)";						//部队平均等级±5
		$sql = "SELECT force_serial_num FROM stra_session WHERE ".implode(' AND ', $where)." ORDER BY RAND() LIMIT 1";
		$res = $this->_stra_udb->getRows($sql);
		if( $res ) {//匹配成功
			$forceIds[] = $res[0]['force_serial_num'];
		} else {//匹配失败
			if( !$isLast ) {//匹配未超时
				return false;
			} else {//匹配超时,开启系统匹配
				$comRes = $this->_sysStraMatch();
				$forceIds[] = $comRes;
			}
		}
		/* 匹配 End */
		/*******************************************************************/
		/* 配对 Start */
		$_stra_team = new StraTeam();
		$stra_serial_num = $_stra_team->teamAdd( $forceIds );//生成地图系列号
		$this->_stra_udb->set('stra_serial_num', $stra_serial_num);
		$this->_stra_udb->dataUpdate('stra_session',"force_serial_num in (".implode(',', $forceIds).")");//执行
		/* 配对 End */
		/*******************************************************************/
		//return
		$this->session['stra_serial_num'] = $stra_serial_num;
		return true;
	}
	
	/**
	 * db执行
	 */
	private function _dbMatch( $uuids, $minLevel, $maxLevel, $force, $limit = 2 )
	{
		$where[] = "`uuid` not in (".implode(',', $uuids).")";
		$where[] = "`force`=$force";
		$where[] = "`force_serial_num`=0";
		$where[] = "`stra_serial_num`=0";
		$where[] = "`com_flag`=0";
		$where[] = "`level`<($maxLevel+5)";
		$where[] = "`level`>($minLevel-5)";
		$sql = "SELECT * FROM stra_session WHERE ".implode(' AND ', $where)." ORDER BY RAND() LIMIT $limit";
		if($res = $this->_stra_udb->getRows($sql)) {
			foreach ($res as $key => $rs) {
				$res[ $rs['uuid'] ] = $rs;
				unset($res[$key]);
			}
		}
		return $res;
	}
	
	/**
	 * 匹配系统玩家
	 * @param $cnt 个数
	 * @return $uuids
	 */
	private function _sysForceMatch( $cnt )
	{
		/**
		 * ...
		 * 生成非固定的uuid
		 */
		return $uuidRes;
	}
	
	/**
	 * 匹配系统部队
	 * @param $cnt 个数
	 * @return $uuids
	 */
	private function _sysStraMatch()
	{
		/**
		 * ...
		 * 生成非固定的uuid
		 * 返回一个派系系列号
		 */
		return $force_serial_num;
	}
}
?>