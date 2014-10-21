<?php

/******************************************************************

Name: 游戏 Session 操作函数库

Update Log:


/******************************************************************/

if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}
class lib_session
{
	// 保存用户 Session
	static public function save_user_session ( $sessionData )
	{
		$sessionData['app_user_id'] = intval ( $sessionData['app_user_id'] );

		$session = array (
		'userName' => $sessionData['userName'],
		'nickName' => $sessionData['nickName'],
		'email' => $sessionData['email'],
		'user_id' => $sessionData['user_id'],
		'code' => _make_session_code ( array ( $sessionData['username'], $sessionData['nickname'], $sessionData['email'], $sessionData['app_user_id'] ) ),
		);

		_save_session ( COOKIE_NAME, serialize ( $session ), USE_COOKIE );
	}

	// 读取用户 Session
	static public function load_user_session ()
	{
		$sessionData = unserialize ( _load_session ( COOKIE_NAME, USE_COOKIE ) );
		return $sessionData;
	}

	// 删除用户 Session
	static public function delete_user_session ()
	{
		_delete_session ( COOKIE_NAME, USE_COOKIE );
		_delete_session ( COOKIE_NAME . '_AT', USE_COOKIE );
	}

	// 检查登陆状态
	function check_login ( $alert = true )
	{
		global $gUser, $gUserId, $tpl, $lang, $gDate, $gCache;

		$chk = false;

		if ( $gUserId > 0 )
		{
			if ( !$gUser || !$gUser->UserInfo )
			{
				$gUser->init ( $gUserId );
				$gUser->getUserInfo ();
			}

			$UserBase = $gUser->cache->getData ( 'User_base' );

			if ( !$UserBase['User_id'] )
			{
				$chk = false;
				if ( $alert && isset ( $tpl ) )
				{
					$message = $lang->show ( 'not_login' );
					show_dialog ( $message, "window.location = '/';" );
				}
			}
			elseif ( $UserBase['disabled_time'] > $gDate->value )
			{
				$config = $gCache->getData ( 'config' );
				$message = $lang->show ( 'User_is_disabled', array ( $UserBase['User_name'], make_date ( $UserBase['disabled_time'], "Y/m/d H:i" ) ) );
				show_dialog ( $message, "window.location = '{$config['system_url']}';" );
			}
			else
			{
				$chk = true;
			}
		}

		return $chk;
	}
}
?>