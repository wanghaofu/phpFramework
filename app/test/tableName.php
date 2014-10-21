<?php

$sql = "select * from myname where name=xx'asdf' select * from asdf";
$pattern = '/(?![\'\"][\w\s]*)(?:update|into|from)\s+([\w]+)\s*(?![\w\s]*[\'\"])/usi';
// $replacement = "\$1 {$dbName}.\${2}{$tableIdx}  ";

$tableName = preg_match($pattern,$sql,$matches);

var_dump($matches);