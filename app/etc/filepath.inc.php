<?php
/** Application PROJECT_ROOT directory */
define('PROJECT_ROOT', dirname(dirname(__FILE__)));

/** include_path setting (adding "/app" and "/lib" directory to include_path) */
$lib = PROJECT_ROOT . "/libs";
$clz = PROJECT_ROOT . '/class';
$includePaths =  implode(PATH_SEPARATOR, array(PROJECT_ROOT, $lib, $clz)) . PATH_SEPARATOR . get_include_path();
set_include_path($includePaths.';C:/php5/PEAR');


## zendamf library set
set_include_path ( implode ( PATH_SEPARATOR, array (PROJECT_ROOT . '/lib/ZendAMF/library', get_include_path () ,$lib ) ) );




#### path config
define('LIB_COMMON' , $lib .'/common/'  );
define('LIB_IDATA' ,PROJECT_ROOT .'/libs/idata/' );

define('CLASS_SRC_ROOT',   $clz);
define('DATA_SRC_ROOT',   $clz . '/Data/');
define('MODEL_SRC_ROOT' , $clz .'/models/'  );
define('HANDLE_SRC_ROOT' ,PROJECT_ROOT .'/controllers/' );




