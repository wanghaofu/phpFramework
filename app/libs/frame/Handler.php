<?php
require_once PROJECT_ROOT . '/libs/frame/InteceptorsUtils.php';
require_once PROJECT_ROOT . '/libs/frame/Advice.php';
require_once PROJECT_ROOT . '/libs/frame/CommonUtils.php';
/**
 * Base Handler for all handler, it define some abstract method to explain the rpc
 * @author
 *
 */
class BaseHandler {
	var $handleData;
	var $call_name;
	//	var $data;
	var $control;
	var $action;
	var $params;
	
	protected $app_config;
	protected $debug = true;
	protected $_invoke_context;
	
	function __construct() {
		$this->parseRequest ();
	}
	
	/* (non-PHPdoc)o=role::list::o1,o2,o3,
	 * @see BaseHandler::parseRequest()
	 */
	function parseRequest() {
		global $IN;
		$pack = $IN ['o'];
		if ($IN ['o']) {
			$packArr = explode ( '::', $pack );
		}
		$this->setInvokeContextControl ( $packArr [0] );
		$this->setInvokeContextMethod ( $packArr [1] );
		$this->setInvokeContextParams ( $packArr [3] );
	}
	private function setInvokeContextControl($control) {
		$this->_invoke_context ['control'] = $control;
	}
	private function getInvokeContextControlName() {
		$this->_invoke_context ['control'];
	}
	
	private function setInvokeContextMethod($methodName) {
		$this->_invoke_context ['method'] = $methodName;
	}
	private function getInvokeContextMethodName() {
		return $this->_invoke_context ['method'];
	}
	
	private function setInvokeContextParams($params) {
		if(empty($params))
		{
			return;
		}
		$this->_invoke_context ['params'] = explode(',',$params);
	}
	private function getControlParams($params) {
		return $this->_invoke_context ['params'];
	}
	

	
	/* (non-PHPdoc)
     * @see IHandler::handle()
     */
	public function /*void*/ handle() {
		$handleClassName = $this->getInvokeContextControlName();
		if (class_exists ( $handleClassName )) {
			$handlerObj = new $handleClassName ();
		} else {
			$error_message = "class name $handleClassName is not exist pleast check method map or check you spell is right!";
			$this->setVisiterError ( $error_message );
			return;
		}
		
		$response = NULL;
		try {
			//            $this->checkVersion ();
			//			$this->parseRequest ();
			$loaded_handler_interceptor_chain = InteceptorsUtils::loadIntecptors ( $this, $this->_invoke_context ['method'] );
			$this->before ( $loaded_handler_interceptor_chain );
			//filter method inivoke
			$this->filterBeforeHandle ( $this );
			$this->doHandle ( $handlerObj );
			$this->after ( $loaded_handler_interceptor_chain );
		} catch ( Exception $e ) {
			$infoError = $this->onException ( $e );
			//业务错误信息
			out::setCurrentHandlerRunInfo ( 'errorData', $infoError );
		}
	
		//		 	$data = out::getData();
	//			return $data;
	}
	/* (non-PHPdoc)
	 * @see BaseHandler::handle()
	 */
	public function doHandle($object) {
		// executes the task on local object
		if (isset ( $this->_invoke_context ['params'] )) {
			$params = $this->_invoke_context ['params'];
		} else {
			$params = array ();
		}
		if (method_exists ( $object, $this->_invoke_context ['method'] )) {
			$result = call_user_func_array ( array ($object, $this->_invoke_context ['method'] ), $params );
		} else {
			snk::Exception ( 402 );
		}
		// finish invoke
		return $result;
	
	}
	/**
	 * invoke before the handler's method is invoked
	 * @param unknown_type $interceptors
	 */
	private final function before($interceptors) {
		if (! empty ( $interceptors ) && isset ( $this->_invoke_context )) {
			foreach ( $interceptors as $interceptor ) {
				if ($interceptor instanceof MethodBeforeAdvice) {
					$interceptor->before ( $this->_invoke_context ['method'], $this->_invoke_context ['params'], $this );
				}
			}
		}
	}
	/**
	 * invoke after the handler's method is invoked
	 * @param unknown_type $interceptors
	 */
	private final function after($interceptors) {
		if (! empty ( $interceptors ) && isset ( $this->_invoke_context )) {
			foreach ( $interceptors as $interceptor ) {
				if ($interceptor instanceof AfterReturningAdvice) {
					$interceptor->afterReturning ( $this->_invoke_context ['result'], $this->_invoke_context ['method'], $this->_invoke_context ['params'], $this );
				}
			}
		}
	}
	
	/**
	 * send a notify message to client
	 * @param unknown_type $property_name
	 * @param unknown_type $property_value
	 */
	public function sendNotify($property_name, $property_value) {
		if (! $this->attachment) {
			$this->attachment = array ();
		}
		if ($property_name && $property_value) {
			$this->attachment [$property_name] = $property_value;
		}
	}
	
	/* (non-PHPdoc)
	 * @see BaseHandler::getMethod()
	 */
	protected function getInvokeContext() {
		return $this->_invoke_context;
	}
	
	/* (non-PHPdoc)
	 * @see BaseHandler::filterHandle()
	 */
	protected function filterBeforeHandle($object) {
		$filter_method = '_filter_' . $this->_invoke_context ['method'];
		if (method_exists ( $object, $filter_method )) {
			// executes the filter method on local handler
			$result = @call_user_func_array ( array ($object, $filter_method ), $this->_invoke_context ['params'] );
			
			if ($result == true) {
				return $result;
			}
			BaseHandler::throwCommonException ( 401 );
		}
	
	}
	
	/* (non-PHPdoc)
	 * @see BaseHandler::doError()
	 */
	protected function onException($e) {
		
		//TODO add logger
		if (! empty ( $this->_invoke_context ['has_echo'] ) && $this->_invoke_context ['has_echo'] === true) {
			return;
		}
		if ($e instanceof CommonException) {
			$error_message = array ();
			$error_message ['cn'] = $e->getCn ();
			$error_message ['msg'] = $e->getMsg ();
			$error_response = array ('id' => $this->_invoke_context ['id'], 'result' => null, 'error' => $error_message );
		} else if ($e instanceof ErrorException) {
			$errno = $e->getSeverity ();
			switch ($errno) {
				case E_USER_ERROR :
					$cn = 501;
					break;
				
				case E_USER_WARNING :
					$cn = 502;
					break;
				
				case E_USER_NOTICE :
					$cn = 503;
					break;
				
				default :
					$cn = 500;
					break;
			}
			$error_message = ExceptionFactory::createErrorMessage ( $cn, $this->debug, $e );
			$error_response = array ('id' => $this->_invoke_context ['id'], 'result' => null, 'error' => $error_message );
		} else {
			$cn = 504;
			$error_message = ExceptionFactory::createErrorMessage ( $cn, $this->debug, $e );
			$error_response = array ('id' => $this->_invoke_context ['id'], 'result' => null, 'error' => $error_message );
		}
		
		if ($error_response) {
			return $error_response;
		}
	}

}
