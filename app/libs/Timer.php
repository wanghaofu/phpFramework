<?php
/**
 * @Author :王涛 wanghaofu@163.com   
 * @Modifications :Mon May 23 06:32:37 GMT 2011
 * @File_Name class_debug.php
 * @Purpose :   
 */
class Timer {
	static $start = '';
	
	static $allRunTime = 0;
	static $stepRunTime = 0;
	static $stepRunStat = false;
	
	static $tag = null;
	static $timer=array();
	static private function run($tag = '', $step = false) {
		$timer = array();
		$tag ? $timer['tag'] = $tag : '';
		
		
		list ( $usec, $sec ) = explode ( " ", microtime () );
		$currentTime = (( float ) $usec + ( float ) $sec);
		$timer['time'] = $currentTime;
		
		//开始单词计算
//		self::$timer[]

		
		
		
			echo '<div style="background-color:#cccccc;padding:10px 10px;font-size:12px"><b>Timer start </b>' . self::$tag . ' <b>Timer ID:</b> ' ;
			self::$start = $currentTime;
			return;
		
		
		//点结束状态
		if(!empty(self::$timer ))
		{
			end(self::$timer);
//			$lastTimer = current( self::$timer );
			$lastTimerArr = each(self::$timer);
			$lastTimerKey = $lastTimer['key'];
			$lastTimer = $lastTimer['value'];
			 
			$currentKey = array_pop($keys);
		}
	
		if( !empty($lastTimer) )
		{
			$runtime = $currentTime - $lastTimer['time'];
			self::$timer[$lastTimer['key']]['runtime'] = $runtime;
		self::$allRunTime += $runtime;
		echo '<span style="color:#ff000;"> <b>Timer </b>' . self::$tag . '<b> Ttimes Count</b> <span style="font-size:12px">' . $runtime . 's</span></span></div>';
		}
		//阶段性结束
		if ('stepEnd' == $step) {
			echo "<StepRunAllTime  " . self::$stepRunTime . "<br/>";
			
			self::$stepRunTime = 0;
			self::$stepRunStat = false;
		}
		if ('end' == $step) {
			echo "<div style='text-align:center;'><b>Page runing Times </b>" . self::$allRunTime . "</div>";
		}
		self::$allRunTime += $runtime; //总时间增加
		self::$start = ''; //开始空
		self::$tag = null;
	}
	static public function start($tag = '') {
		self::run ( $tag, 'stepStart' );
	}
	static public function end() {
		self::run ( '', 'stepEnd' );
	}
	static public function pageEnd() {
		self::run ( '', 'end' );
	}
}