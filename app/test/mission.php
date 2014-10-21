<?php
class mission
{
	var $status;
	var $open;
	var $close;
	var $finish;
	var $ss = function() {return 'xx';}

	var $config = array(
		"open" =>"you mission is open please click here close mission!\n",
		"close" => "you misssion is close tag is close \n",
		"finish" => array("you mission is finish\n",
							"reward you three coin  coin:level*3\n"
							)
	);
	var $mission =array(
		'mission_id'=>'',
		'mission_active_check_point'=>'
		user.level >30,
		npc(32).status = 0
		
		',
		'mission_reward' =>'coin:50;prop:32:5',
		'next_mission' =>32
	);
	

	
	function openMission()
	{
		echo $this->config["open"];
	}
	function closeMission()
	{
		echo $this->config["close"];

	}
	function finishMission()
	{
		echo print_r($this->config["finish"]);
	}
	
}
echo "<pre>";
$user["level"] =30;
$npc["hp"] =20;
$mission = new mission;
$mission->openMission();

if( $user["level"] >=20 )
{
$mission->closeMission();
}
if( $npc["hp"] <=30)
{
$mission->finishMission();
}
echo "</pre>";
