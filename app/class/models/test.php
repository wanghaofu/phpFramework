<?php 
$rules = array('a','b','c','d');
$array = array(
	'No8'=>array('a'=>9,'b'=>43,'c'=>465,'d'=>7826),
	'No6'=>array('a'=>7,'b'=>32,'c'=>432,'d'=>4658),
	'No2'=>array('a'=>7,'b'=>32,'c'=>432,'d'=>4158),
	'No9'=>array('a'=>8,'b'=>97,'c'=>865,'d'=>1342),
);
foreach ($array as $key=>$arr) {
	if(!$ids) $ids = array_keys($arr);
	foreach ($arr as $k=>$ar) {
		${$k}[] = $ar;
	}
	$ids[] = $key;
}
$new[] = $ids;
foreach ($rules as $rule) {
	$new[] = $$rule;
}
echo "<pre>";
print_r($tplVars);
echo "</pre>";
exit;
function _sort()
{
	
}
function _check($array)
{
	$return = array();
	return $return;
}
?>