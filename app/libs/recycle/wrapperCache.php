<?php
/*
 * Name:	wrapperCache
 * URL:		http://www.admpub.com/blog/
 * Version:	v0.1
 * Date:	29/10/2010
 * Author:	Chema Garrido
 * License: GPL v3
 * Notes:	wrapper cache for fileCache, memcache, APC, Xcache and eaccelerator
 */

/////////////////////class file cache


class wrapperCache {
	private $cache_params; //extra params for external caches like path or connection option memcached
	public $cache_expire; //seconds that the cache expires
	private $cache_type; //type of cache to use
	private $cache_external; //external instance of cache, can be fileCache or memcache
	private static $instance; //Instance of this class
	

	// Always returns only one instance
	public static function GetInstance($type = 'auto', $exp_time = 3600, $params = 'cache/') {
		if (! isset ( self::$instance )) { //doesn't exists the isntance
			self::$instance = new self ( $type, $exp_time, $params ); //goes to the constructor
		}
		return self::$instance;
	}
	
	//cache constructor, optional expiring time and cache path
	private function __construct($type, $exp_time, $params) {
		$this->cache_expire = $exp_time;
		$this->cache_params = $params;
		$this->setCacheType ( $type );
	}
	
	public function __destruct() {
		unset ( $this->cache_external );
	}
	
	// Prevent users to clone the instance
	public function __clone() {
		$this->cacheError ( 'Clone is not allowed.' );
	}
	
	//deletes cache from folder
	public function clearCache() {
		switch ($this->cache_type) {
			case 'eaccelerator' :
				@eaccelerator_clean ();
				@eaccelerator_clear ();
				break;
			
			case 'apc' :
				apc_clear_cache ( 'user' );
				break;
			
			case 'xcache' :
				xcache_clear_cache ( XC_TYPE_VAR, 0 );
				break;
			
			case 'memcache' :
				@$this->cache_external->flush ();
				break;
			
			case 'filecache' :
				$this->cache_external->deleteCache ();
				break;
		}
	}
	
	//writes or reads the cache
	public function cache($key, $value = '', $ttl = '') {
		if ($value != '') { //wants to write
			if ($ttl == '')
				$ttl = $this->cache_expire;
			$this->put ( $key, $value, $ttl );
		} else
			return $this->get ( $key ); //reading value
	}
	
	//creates new cache files with the given data, $key== name of the cache, data the info/values to store
	private function put($key, $data, $ttl = '') {
		if ($ttl == '')
			$ttl = $this->cache_expire;
		
		switch ($this->cache_type) {
			case 'eaccelerator' :
				eaccelerator_put ( $key, serialize ( $data ), $ttl );
				break;
			
			case 'apc' :
				apc_store ( $key, $data, $ttl );
				break;
			
			case 'xcache' :
				xcache_set ( $key, serialize ( $data ), $ttl );
				break;
			
			case 'memcache' :
				$data = serialize ( $data );
				if (! $this->cache_external->replace ( $key, $data, false, $ttl ))
					$this->cache_external->set ( $key, $data, false, $ttl );
				break;
			
			case 'filecache' :
				$this->cache_external->cache ( $key, $data );
				break;
		}
	}
	
	//returns cache for the given key
	private function get($key) {
		switch ($this->cache_type) {
			case 'eaccelerator' :
				$data = @unserialize ( eaccelerator_get ( $key ) );
				break;
			
			case 'apc' :
				$data = apc_fetch ( $key );
				break;
			
			case 'xcache' :
				$data = @unserialize ( xcache_get ( $key ) );
				break;
			
			case 'memcache' :
				$data = @unserialize ( $this->cache_external->get ( $key ) );
				break;
			
			case 'filecache' :
				$data = $this->cache_external->cache ( $key );
				break;
		}
		/*echo '<br />--returnning data for key:'.$key;
		var_dump($data);*/
		return $data;
	}
	
	//delete key from cache
	public function delete($key) {
		switch ($this->cache_type) {
			case 'eaccelerator' :
				eaccelerator_rm ( $key );
				break;
			
			case 'apc' :
				apc_delete ( $key );
				break;
			
			case 'xcache' :
				xcache_unset ( $key );
				break;
			
			case 'memcache' :
				$this->cache_external->delete ( $key );
				break;
			
			case 'filecache' :
				$this->cache_external->delete ( $key );
				break;
		}
	
	}
	// Overloading for the Application variables and automatically cached
	public function __set($name, $value) {
		$this->put ( $name, $value, $this->cache_expire );
	}
	
	public function __get($name) {
		return $this->get ( $name );
	}
	
	public function __isset($key) { //echo "Is '$name' set?\n"
		if ($this->get ( $key ) !== false)
			return true;
		else
			return false;
	}
	
	public function __unset($name) { //echo "Unsetting '$name'\n";
		$this->delete ( $name );
	}
	//end overloads
	

	public function getCacheType() {
		return $this->$this->cache_type;
	}
	
	//sets the cache if its installed if not triggers error
	public function setCacheType($type) {
		$this->cache_type = strtolower ( $type );
		
		switch ($this->cache_type) {
			case 'eaccelerator' :
				if (function_exists ( 'eaccelerator_get' ))
					$this->cache_type = 'eaccelerator';
				else
					$this->cacheError ( 'eaccelerator not found' );
				break;
			
			case 'apc' :
				if (function_exists ( 'apc_fetch' ))
					$this->cache_type = 'apc';
				else
					$this->cacheError ( 'APC not found' );
				break;
			
			case 'xcache' :
				if (function_exists ( 'xcache_get' ))
					$this->cache_type = 'xcache';
				else
					$this->cacheError ( 'Xcache not found' );
				break;
			
			case 'memcache' :
				if (class_exists ( 'Memcache' ))
					$this->init_memcache ();
				else
					$this->cacheError ( 'memcache not found' );
				break;
			
			case 'filecache' :
				if (class_exists ( 'fileCache' ))
					$this->init_filecache ();
				else
					$this->cacheError ( 'fileCache not found' );
				break;
			
			case 'auto' : //try to auto select a cache system
				if (function_exists ( 'eaccelerator_get' ))
					$this->cache_type = 'eaccelerator';
				elseif (function_exists ( 'apc_fetch' ))
					$this->cache_type = 'apc';
				elseif (function_exists ( 'xcache_get' ))
					$this->cache_type = 'xcache';
				elseif (class_exists ( 'Memcache' ))
					$this->init_memcache ();
				elseif (class_exists ( 'fileCache' ))
					$this->init_filecache ();
				else
					$this->cacheError ( 'not any compatible cache was found' );
				break;
			
			default : //not any cache selected or wrong one selected
				if (isset ( $type ))
					$msg = 'Unrecognized cache type selected <b>' . $type . '</b>';
				else
					$msg = 'Not any cache type selected';
				$this->cacheError ( $msg );
				break;
		}
	}
	
	private function init_memcache() { //get instance of the memcache class
		if (is_array ( $this->cache_params )) {
			$this->cache_type = 'memcache';
			$this->cache_external = new Memcache ();
			foreach ( $this->cache_params as $server ) {
				$server ['port'] = isset ( $server ['port'] ) ? ( int ) $server ['port'] : ini_get ( 'memcache.default_port' );
				$server ['persistent'] = isset ( $server ['persistent'] ) ? ( bool ) $server ['persistent'] : true;
				$this->cache_external->addServer ( $server ['host'], $server ['port'], $server ['persistent'] );
			}
		} else
			$this->cacheError ( 'memcache needs an array, example: 
    				wrapperCache::GetInstance(\'memcache\',30,array(array(\'host\'=>\'localhost\')));' );
	}
	
	private function init_filecache() { //get instance of the filecache class
		$this->cache_type = 'filecache';
		$this->cache_external = fileCache::GetInstance ( $this->cache_expire, $this->cache_params );
	}
	
	public function getAvailableCache($return_format = 'html') { //returns the available cache
		$avCaches = array ();
		$avCaches [] = array ('eaccelerator', function_exists ( 'eaccelerator_get' ) );
		$avCaches [] = array ('apc', function_exists ( 'apc_fetch' ) );
		$avCaches [] = array ('xcache', function_exists ( 'xcache_get' ) );
		$avCaches [] = array ('memcache', class_exists ( 'Memcache' ) );
		$avCaches [] = array ('fileCache', class_exists ( 'fileCache' ) );
		
		if ($return_format == 'html') {
			$ret = '<ul>';
			foreach ( $avCaches as $c ) {
				$ret .= '<li>' . $c [0] . ' - ';
				if ($c [1])
					$ret .= 'Found/Compatible';
				else
					$ret .= 'Not Found/Incompatible';
				$ret .= '</ll>';
			}
			return $ret . '</ul>';
		} else
			return $avCaches;
	}
	
	private function cacheError($msg) { //triggers error
		trigger_error ( '<br /><b>wrapperCache error</b>: ' . $msg . '<br />If you want you can try with \'auto\' for auto select a compatible cache. 
	        		<br />Or choose a supported cache from list:' . $this->getAvailableCache (), E_USER_ERROR );
	}
}
?>基于文件的缓存处理类：

<?php
/*
 * Name:	fileCache
 * URL:		http:/www.admpub.com/
 * Version:	v1.1
 * Date:	21/10/2010
 * Author:	Chema Garrido
 * License: GPL v3
 * Notes:	fileCache class, caches variables in standalone files if value is too long or uses unique file for small ones
 */

/////////////////////class file cache


class fileCache {
	private $cache_path; //path for the cache
	private $cache_expire; //seconds that the cache expires
	private $application = array (); //application object like in ASP
	private $application_file; //file for the application object
	private $application_write = false; //if application write is true means there was changes and we need to write the app file
	private $debug = false; //no debug by default
	private $log = array (); //log for the debug system
	private $start_time = 0; //application start time
	private static $content_size = 64; //this is the max size can be used in APP cache if bigger writes independent file
	private static $instance; //Instance of this class
	

	// Always returns only one instance
	public static function GetInstance($exp_time = 3600, $path = 'cache/') {
		if (! isset ( self::$instance )) { //doesn't exists the isntance
			self::$instance = new self ( $exp_time, $path ); //goes to the constructor
		}
		return self::$instance;
	}
	
	//cache constructor, optional expiring time and cache path
	private function __construct($exp_time, $path) {
		$this->start_time = microtime ( true ); //time starts
		$this->cache_expire = $exp_time;
		if (! is_writable ( $path ))
			trigger_error ( 'Path not writable:' . $path );
		else
			$this->cache_path = $path;
		$this->APP_start (); //starting application cache
	}
	
	public function __destruct() {
		$this->addLog ( 'destruct' );
		$this->APP_write (); //on destruct we write if needed
		$this->returnDebug ();
	}
	
	// Prevent users to clone the instance
	public function __clone() {
		trigger_error ( 'Clone is not allowed.', E_USER_ERROR );
	}
	
	//deletes cache from folder
	public function deleteCache($older_than = '') {
		$this->addLog ( 'delete cache' );
		if (! is_numeric ( $older_than ))
			$older_than = 0;
		$files = scandir ( $this->cache_path );
		foreach ( $files as $file ) {
			if (strlen ( $file ) > 2 && time () > (filemtime ( $this->cache_path . $file ) + $older_than)) {
				unlink ( $this->cache_path . $file ); //echo "<br />-".$file; 
				$this->addLog ( 'delete cache file:' . $this->cache_path . $file );
			}
		}
	
	}
	
	//writes or reads the cache
	public function cache($key, $value = '') {
		if ($value != '') { //wants to write
			if (strlen ( serialize ( $value ) ) > 64) { //write independent file it's a big result
				$this->addLog ( 'cache function write in file key:' . $key );
				$this->put ( $key, $value );
			} else {
				$this->addLog ( 'cache function write in APP key:' . $key );
				$this->APP ( $key, $value ); //write in the APP cache
			}
		} else { //reading value
			if ($this->APP ( $key ) != null) {
				$this->addLog ( 'cache function read APP key:' . $key );
				return $this->APP ( $key ); //returns from app cache
			} else {
				$this->addLog ( 'cache function read file key:' . $key );
				return $this->get ( $key ); //returns from file cache
			}
		}
	}
	
	//deletes a key from cache
	public function delete($name) {
		if ($this->APP ( $name ) != null) { //unset the APP var
			$this->addLog ( 'unset APP key:' . $name );
			unset ( $this->application [md5 ( $name )] );
			$this->application_write = true; //says that we have changes to later save the APP
		} elseif (file_exists ( $this->fileName ( $name ) )) { //unlink filename
			$this->addLog ( 'unset File key:' . $name );
			unlink ( $this->fileName ( $name ) );
		}
	}
	
	// Overloading for the variables and automatically cached
	public function __set($name, $value) {
		$this->cache ( $name, $value );
	}
	
	public function __get($name) {
		return $this->cache ( $name );
	}
	
	public function __isset($name) { //echo "Is '$name' set?\n"
		$this->addLog ( 'isset key:' . $name );
		$value = $this->cache ( $name );
		return isset ( $value );
	}
	
	public function __unset($name) { //echo "Unsetting '$name'\n";
		$this->delete ( $name );
	}
	//end overloads
	

	//////////Cache for files individually///////////////////
	

	//creates new cache files with the given data, $key== name of the cache, data the info/values to store
	private function put($key, $data) {
		if ($this->get ( $key ) != $data) { //only write if it's different
			$values = serialize ( $data );
			$filename = $this->fileName ( $key );
			$file = fopen ( $filename, 'w' );
			if ($file) { //able to create the file
				$this->addLog ( 'writting key: ' . $key . ' file: ' . $filename );
				fwrite ( $file, $values );
				fclose ( $file );
			} else
				$this->addLog ( 'unable to write key: ' . $key . ' file: ' . $filename );
		} //end if different
	}
	
	//returns cache for the given key
	private function get($key) {
		$filename = $this->fileName ( $key );
		if (! file_exists ( $filename ) || ! is_readable ( $filename )) { //can't read the cache
			$this->addLog ( 'can\'t read key: ' . $key . ' file: ' . $filename );
			return null;
		}
		
		if (time () < (filemtime ( $filename ) + $this->cache_expire)) { //cache for the key not expired
			$file = fopen ( $filename, 'r' ); // read data file
			if ($file) { //able to open the file
				$data = fread ( $file, filesize ( $filename ) );
				fclose ( $file );
				$this->addLog ( 'reading key: ' . $key . ' file: ' . $filename );
				return unserialize ( $data ); //return the values
			} else {
				$this->addLog ( 'unable to read key: ' . $key . ' file: ' . $filename );
				return null;
			}
		} else {
			$this->addLog ( 'expired key: ' . $key . ' file: ' . $filename );
			unlink ( $filename );
			return null; //was expired you need to create new
		}
	}
	
	//returns the filename for the cache
	private function fileName($key) {
		return $this->cache_path . md5 ( $key );
	}
	//////////END Cache for files individually///////////////////
	

	//////////Cache for APP variables///////////////////
	

	//load variables from the file
	private function APP_start($app_file = 'application') {
		$this->application_file = $app_file;
		
		if (file_exists ( $this->cache_path . $this->application_file )) { // if data file exists, load the cached variables
			//erase the cache every X minutes
			$app_time = filemtime ( $this->cache_path . $this->application_file ) + $this->cache_expire;
			if (time () > $app_time) {
				$this->addLog ( 'deleting APP file: ' . $this->cache_path . $this->application_file );
				unlink ( $this->cache_path . $this->application_file ); //erase the cache
			} else { //not expired
				$filesize = filesize ( $this->cache_path . $this->application_file );
				if ($filesize > 0) {
					$file = fopen ( $this->cache_path . $this->application_file, 'r' ); // read data file
					if ($file) {
						$this->addLog ( 'reading APP file: ' . $this->cache_path . $this->application_file );
						$data = fread ( $file, $filesize );
						fclose ( $file );
						$this->application = unserialize ( $data ); // build application variables from data file
					} //en if file could open
				} //end if file size
			

			}
		} else { //if the file does not exist we create it
			$this->addLog ( 'creating APP file: ' . $this->cache_path . $this->application_file );
			fopen ( $this->cache_path . $this->application_file, 'w' );
		}
	
	}
	
	// write application data to file
	private function APP_write() {
		if ($this->application_write) {
			$data = serialize ( $this->application );
			$file = fopen ( $this->cache_path . $this->application_file, 'w' );
			if ($file) {
				$this->addLog ( 'writting APP file: ' . $this->cache_path . $this->application_file );
				fwrite ( $file, $data );
				fclose ( $file );
			}
		}
	}
	
	//returns the value form APP cache or stores it
	private function APP($var, $value = '') {
		if ($value != '') { //wants to write
			

			if (is_array ( $this->application )) {
				if (array_key_exists ( md5 ( $var ), $this->application )) { //exist the value in the APP
					$write = false; //we don't need to wirte
					if ($this->application [md5 ( $var )] != $value)
						$write = true; //but exists and is different then we write
				} else
					$write = true; //not set we write!
			} else
				$write = false;
			
			if ($write) {
				$this->addLog ( 'writting APP key:' . $var );
				$this->application [md5 ( $var )] = $value;
				$this->application_write = true; //says that we have changes to later save the APP
			}
		} else { //reading	
			if (! is_array ( $this->application ) || ! array_key_exists ( md5 ( $var ), $this->application )) {
				$this->addLog ( 'nothing found for APP key:' . $var );
				return null; //nothing found not in array
			} else {
				$this->addLog ( 'reading APP key:' . $var );
				return $this->application [md5 ( $var )]; //return value
			}
		}
	}
	//////////End Cache for APP variables///////////////////
	

	////DEBUG
	//sets debug on or off
	public function setDebug($state) {
		$this->debug = ( bool ) $state;
	}
	
	public function returnDebug($type = 'HTML') {
		if ($this->debug) {
			switch ($type) {
				case 'array' :
					return $this->log;
					break;
				case 'HTML' : //returns debug as HTML
					echo '<ol>';
					foreach ( $this->log as $key => $value ) { //loop in the log var
						echo '<li>' . $value . '</li>';
					}
					echo '</ol>';
					break;
			}
		} else
			return false;
	}
	
	//add debug log
	public function addLog($value) {
		if ($this->debug) { //only if debug enabled
			array_push ( $this->log, round ( (microtime ( true ) - $this->start_time), 5 ) . 's - ' . $value );
		}
	}
}
?>
