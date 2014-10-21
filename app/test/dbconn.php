<?php
/* Connect to an ODBC database using driver invocation */
$dsn = 'mysql:host=10.0.7.93;port=3306;';
$user = 'stradev';
$password = 'stradev';
for($i = 1; $i <=3; $i ++) {
	try {
		$dbh = new PDO ( $dsn, $user, $password );
	} catch ( PDOException $e ) {
		echo 'Connection failed: ' . $e->getMessage ();
	}
}
var_dump ( $dbh );

?>