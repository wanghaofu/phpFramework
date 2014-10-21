<?php

class InteceptorsUtils {
    
    static $HANDLER_CONFIG_PATH = 'etc/handler.ini.php';
    
    /**
     * Load interceptors for the $obj from the config file
     * @param Object $obj
     * @param string $method_name
     * @return multitype:
     */
    public final static function /*array*/ loadIntecptors($obj, $method_name) {
        //load the interceptors from the config file
        global $handler_config;
        require_once InteceptorsUtils::$HANDLER_CONFIG_PATH;
        
        $_interceptor_chain = array();
        //init the global interceptors
        if (! empty($handler_config['_handler_interceptor']['*'])) {
            $hit_intercptors = $handler_config['_handler_interceptor']['*'];
            $_interceptor_chain = InteceptorsUtils::initInterceptor($handler_config['_interceptors'], $hit_intercptors, $method_name, $_interceptor_chain);
        }
        
        //init the handler's interceptors
        $handlerName = get_class($obj);
        if (! empty($handler_config['_handler_interceptor'][$handlerName])) {
            $hit_intercpts2 = $handler_config['_handler_interceptor'][$handlerName];
            $_interceptor_chain = InteceptorsUtils::initInterceptor($handler_config['_interceptors'], $hit_intercpts2, $method_name, $_interceptor_chain);
        }
        return $_interceptor_chain;
    }
    
    /**
     * init the interceptors
     * @param unknown_type $_interceptors
     * @param unknown_type $intercptList
     * @param unknown_type $method_name
     * @param unknown_type $_interceptor_chain
     * @return multitype:unknown 
     */
    private final static function /*object*/ initInterceptor($_interceptors, $intercptList, $method_name, $_interceptor_chain) {
        if (! empty($intercptList)) {
            foreach ( $intercptList as $md => $intercptor_config_array ) {
                if ('*' === $md || $method_name === $md) {
                    foreach ( $intercptor_config_array as $inter ) {
                        if (!isset($_interceptor_chain[$inter]) && ! empty($_interceptors[$inter])) {
                            $intercpStr = explode('_', $_interceptors[$inter]);
                            $index = count($intercpStr);
                            if ($index > 1) {
                                $interceptorPath = '';
                                for($j = 0; $j < $index - 1; $j ++) {
                                    if ($j == 0) {
                                        $interceptorPath = $intercpStr[$j];
                                    } else if ($j != $index - 2) {
                                        $interceptorPath = $interceptorPath . '/' . $intercpStr[$j];
                                    } else {
                                        $interceptorPath = $interceptorPath . '/' . $intercpStr[$j] . '.php';
                                    }
                                }
                                require_once $interceptorPath;
                                $interceptor =  new $intercpStr[$index - 1]();
                                $_interceptor_chain[$inter] = $interceptor;
                            }
                        }
                    }
                }
            }
        }
        
        return $_interceptor_chain;
    }

}

