<?php
if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

// 日期类
class Date
{
	var $year;
	var $month;
	var $day;
	var $hour;
	var $minute;
	var $second;
	var $weekDay;
	var $value;
	var $valueText;
	var $dateSlot;
	var $dateFormat = "Y/m/d H:i:s";

	// 设置周第一天 ( 0: 周日, 1: 周一 )
	var $weekFirstDay = 1;

	function clsDateTime ( $curTime = 0 )
	{
		if ( 0 == $curTime )
			$this->value = time ();
		else
			$this->value = $curTime;

		$this->valueText = date ( $this->dateFormat, $this->value );

		$dateStr = date ( "Y-n-j-G-i-s-w", $this->value );
		$dateArr = explode ( '-', $dateStr );

		$this->year = $dateArr[0];
		$this->month = $dateArr[1];
		$this->day = $dateArr[2];
		$this->hour = $dateArr[3];
		$this->minute = $dateArr[4];
		$this->second = $dateArr[5];
		$this->weekDay = $dateArr[6];
		$this->getWeekDay ();

		$this->dateSlot = array ( 
			'last' => array (
				'year' => mktime ( 0, 0, 0, 1, 1, $dateArr[0] - 1 ),
				'month' => mktime ( 0, 0, 0, $dateArr[1] - 1, 1, $dateArr[0] ),
				'day' => mktime ( 0, 0, 0, $dateArr[1], $dateArr[2] - 1, $dateArr[0] ),
				'week' => mktime ( 0, 0, 0, $dateArr[1], $dateArr[2] - $this->weekDay + $this->weekFirstDay  - ($this->weekDay<$this->weekFirstDay ? 7 : 0 ) - 7 , $dateArr[0]  ),
				),

			'current' => array (
				'year' => mktime ( 0, 0, 0, 1, 1, $dateArr[0] ),
				'month' => mktime ( 0, 0, 0, $dateArr[1], 1, $dateArr[0] ),
				'day' => mktime ( 0, 0, 0, $dateArr[1], $dateArr[2], $dateArr[0] ),
				'week' => mktime ( 0, 0, 0, $dateArr[1], $dateArr[2] - $this->weekDay + $this->weekFirstDay - ($this->weekDay<$this->weekFirstDay ? 7 : 0 ) , $dateArr[0]  ),
				),

			'next' => array (
				'year' => mktime ( 0, 0, 0, 1, 1, $dateArr[0] + 1 ),
				'month' => mktime ( 0, 0, 0, $dateArr[1] + 1, 1, $dateArr[0] ),
				'day' => mktime ( 0, 0, 0, $dateArr[1], $dateArr[2] +1, $dateArr[0] ),
				'week' => mktime ( 0, 0, 0, $dateArr[1], $dateArr[2] - $this->weekDay + $this->weekFirstDay - ($this->weekDay<$this->weekFirstDay ? 7 : 0 ) + 7, $dateArr[0]  ),
				),
			);
	}

	function getWeekDay ( $time = null )
	{
		if ( $time == null ) $time = $this->value;

		$idxWeekDay = date ( 'w', $time );
		$this->weekDayText = $this->weekDayTexts ( $idxWeekDay );

		return $this->weekDayText;
	}

	function weekDayTexts ( $idxWeekDay )
	{
		$weekdays = array (
			0 => '日',
			1 => '一',
			2 => '二',
			3 => '三',
			4 => '四',
			5 => '五',
			6 => '六'
		);

		return $weekdays[$idxWeekDay];
	}
}

?>