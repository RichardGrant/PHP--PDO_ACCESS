<?php
class globals_{
	//protected static & constants only.
	private static $sql_hosts = array(
		'localhost'
	);
	private static $sql_dbs = array(
		'nportal2'
	);
	private static $sql_accounts = array(
		array(
			'username'=>'root',
			'password'=>'PASSWORD'
		)
	);
	protected static $SQL_CONNECTION = array(
	);
	function __construct(){
		self::$SQL_CONNECTION = array(
			array(
				'host'=>self::$sql_hosts[0x0],
				'db'=>self::$sql_dbs[0x0],
				'username'=>self::$sql_accounts[0x0]['username'],
				'password'=>self::$sql_accounts[0x0]['password']
			)
		);
	}
}
?>