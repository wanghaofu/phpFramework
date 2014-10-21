<?php
class Check {
	static function has_static_method($class_method)
	{
		$methodInfo = explode('::',$class_method);
		return self::_has_static_method($methodInfo[0],  $methodInfo[1]);
		
	}
	static function _has_static_method($className, $methodName) {
		$ref = new ReflectionClass ( $className );
		if ($ref->hasMethod ( $methodName ) and $ref->getMethod ( $methodName )->isStatic ()) {
			return true;
		}else
		{
			return false;
		}
	}
}
?>