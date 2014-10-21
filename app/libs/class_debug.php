<?php
/**
* @Author :王涛 wanghaofu@163.com   
* @Modifications :Mon May 23 06:32:37 GMT 2011
* @File_Name class_debug.php
* @Purpose :   
*/
class debug
{
	static $start = '';
	static $tag = 1;
	static $allRunTime = 0 ;
	static $stepRunTime = 0;
	static $stepRunStat = false;
	static public function run($tag='', $step = false)
	{
		list($usec, $sec) = explode(" ", microtime());
		$currentTime = ((float)$usec + (float)$sec);
		if ( empty( self::$start ) ) //开始 0
		{
			echo '<br/> <b>'.$tag.'</b> #### runPoint:'.self::$tag.'<br/>';
			if( 'stepStart'  == $step )
			{
				self::$stepRunStat = true;
				echo 'StepRunStart****';
			}
			self::$start = $currentTime;  return;

		}
		//点结束状态
		//		echo  ' end Time '.self::$end;
		$runtime = $currentTime - self::$start;
//		if( self::$stepRunStat == true)
//		{
//			echo 'StepRun';
//		}
		echo  ' <b>runTimeLong</b> '.$runtime.'s <br/>';

		if ( true == self::$stepRunStat) self::$stepRunTime +=$runtime;
		

		if( 'stepEnd'  == $step ) //阶段性结束
		{
			echo "StepRunAllTime". self::$stepRunTime."<br/>" ;
			self::$stepRunTime = 0;
			self::$stepRunStat = false;
		}
		if( 'end' == $step)
		{
			echo "allRunTime" .self::$allRunTime."<br/>" ;
		}
		self::$allRunTime +=$runtime; //总时间增加
		self::$start =''; //开始空
		self::$tag++;
	}
	static function runStepStart($tag='')
	{
		self::run($tag, 'stepStart');
	}
	static function runStepEnd()
	{
		self::run('', 'stepEnd');
	}
	static function runEnd()
	{
		self::run('', 'end');
	}
}