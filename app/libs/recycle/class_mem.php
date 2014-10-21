<?php
class mem
{
	var $mem;
	var $key;
	public function __construct( $key ,$memConfig)
	{
		$this->mem = new Memcache();
		if ( empty($memConfig) )
		{
			global $storeMcConfig ;
		
			$memConfig = $storeMcConfig; 
		}
		if ( empty($memConfig) )
		{
			throw new Exception('memConfig is not set');
		}
		
		$this->mem->connect($memConfig['host'], $memConfig['port'] );
		$this->key = $key;

	}
	//gengxin
	function setData($data)
	{
		return $this->mem->set($this->key, $data, MEMCACHE_COMPRESSED, 28800);
	}
	function addData($data, $time = 28800)
	{
		return $this->mem->add($this->key,$data,false, $time);
	}
	function getData()
	{
		return $this->mem->get($this->key);
	}
	function setKey($key)
	{
		if ( empty($key ))
		{
			throw new Exception('Key is not null!');
		}
		$this->key = $key;
	}
}
?>