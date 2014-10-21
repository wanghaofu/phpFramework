<?php

class Core_Dispatcher {

	/* 被调用的模块 */
	private $controller;
	private $ctrlerPrefix;
	private $action;
	
	public function __construct($modPrefix) {
		$this->ctrlerPrefix = $modPrefix;
	}
	
	public function getController($ctrlerName) {
		$controllerName = $this->ctrlerPrefix . '_' . $ctrlerName . '_Controller';
		try { 
			$this->controller = new $controllerName();
		}catch (Exception $e) {
			throw $e;
		}
		
		return $this->controller;
	}
	
	/**
	 * 
	 * @param unknown_type $ctrlerName
	 * @param unknown_type $actionName
	 * @param Core_Packet_In $packet
	 * @return unknown_type
	 */
	public function dispatch($ctrlerName, $actionName, &$packet)
	{
		$this->controller = $ctrlerName;
		$this->action = $actionName;
		
		

		//初始化系统日志
//		try{
//			$username = Core_User::getCurUsername();
//		}catch(Exception $e) {
//			$username = null;
//		}

//		if($username !== null) {
//			try{
//				$roleId = Game_Role::getCurRoleId(); 
//			}catch(Exception $e) {
//				$roleId = 0;
//			}
//
//			$logArr = array(
//				'username'=> $username,
//				'role_id' => $roleId,
//				'ctrlor'  => $this->controller,
//				'action'  => $this->action,
//			);
//		}
		
		
		
		return $out;
	}

	public function getControllerName()
	{
		return $this->controller;
	}

	public function getActionName()
	{
		return $this->action;
	}
}
?>