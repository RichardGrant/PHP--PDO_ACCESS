<?PHP
/*
	Developer: Richard Grant
	Code-genius.com
*/
require_once("globals.php");

class database_ extends globals_{
	public $connect = null;
	
	function __construct($memory){
		parent::__construct();
		$this->connect = new PDO('mysql:host=' . self::$SQL_CONNECTION[$memory]['host'] . ';dbname=' . self::$SQL_CONNECTION[$memory]['db'], self::$SQL_CONNECTION[$memory]['username'], self::$SQL_CONNECTION[$memory]['password']);
	}
	private static function values_parse($values = null, $optional=array()){
		$args = array(
			'spacer'=>(isset($optional['spacer']))? $optional['spacer'] : ', ',
			'advanced'=>(isset($optional['advanced']))? $optional['advanced']: false
		);
		$vars = array(
			'key?:keyStr'=>'',
			'keyStr'=>'',
			':keyStr'=>'',
			'params'=>array()
		);
		if($values != null){
			if($args['advanced'] == false){
				foreach($values as $key => $value){
					$vars['keyStr'] .= '`' . $key . "`" . $args['spacer'];
					$vars[':keyStr'] .= ':' . $key . $args['spacer'];
					$vars['key?:keyStr'] .= "`" . $key . "`=:" . $key . $args['spacer'];
					$vars['params'][":" . $key] = $value;
				}
			}else{
				foreach($values as $key => $value){
					switch(strtolower($value[1])){
						case 'between':{
							$vars['key?:keyStr'] .= $value[0] . "` " . $value[1] . " :b1_" . $value[0] .  " AND" . " :b2_" . $value[0] .  $args['spacer'];
							$vars['keyStr'] .= '`' . $value[0] . "`" . $args['spacer'];
							$vars[':keyStr'] .= ':' . $value[0] . $args['spacer'];
							$vars['params'][":b1_" . $value[0] ] = $value[2];
							$vars['params'][":b2_" . $value[0] ] = $value[3];
							break;
						};
						case 'md5ed':{
							$md5_str = "";
							foreach ($value[0] as $md5_key => $md5_val) {
								$md5_str .= $md5_val;
							}
							$md5_str = md5($md5_str);
							$vars['key?:keyStr'] .= "md5(" . $md5_str . ") = :" . $value[2] .  $args['spacer'];
							$vars['keyStr'] .= '`' . $md5_str . "`" . $args['spacer'];
							$vars[':keyStr'] .= ':' . $md5_str . $args['spacer'];
							$vars['params'][":" . $value[0] ] = $value[2];
							break;
						};
						default:{
							$vars['key?:keyStr'] .= "`" . $value[0] . "`" . $value[1] . ":" . $value[0] . $args['spacer'];
							$vars['keyStr'] .= '`' . $value[0] . "`" . $args['spacer'];
							$vars[':keyStr'] .= ':' . $value[0] . $args['spacer'];
							$vars['params'][":" . $value[0] ] = $value[2];
							break;
						}
					}
				}
			}
			$vars['key?:keyStr'] = preg_replace('/' . $args['spacer'] . '$/', '', $vars['key?:keyStr']);
			$vars['keyStr'] = preg_replace('/' . $args['spacer'] . '$/', '', $vars['keyStr']);
			$vars[':keyStr'] = preg_replace('/' . $args['spacer'] . '$/', '', $vars[':keyStr']);
		}
		return $vars;
	}
	private function bind_type($val){
		switch(gettype($val)){
			case "boolean":{
				return PDO::PARAM_BOOL;
				break;
			};
			case "integer":{
				return PDO::PARAM_INT;
				break;
			};
			case "string":{
				return PDO::PARAM_STR;
				break;
			};
			default:{
				return PDO::PARAM_STR;
				break;
			}
		}
	}
	private function bind_value($sql_statement, $params1 = array(), $params2 = array()){
		$sql_prep = $this->connect->prepare($sql_statement);
		foreach ($params1 as $key => $value) {
			$sql_prep->bindValue($key, $value, $this->bind_type($value));
		}
		foreach ($params2 as $key => $value) {
			$sql_prep->bindValue($key, $value, $this->bind_type($value));
		}
		return $sql_prep;
	}
	public function insert($table, $values, $conditions = null){
		$vars = array(
			'valuesParse'=>array(),
			'condParse'=>array(),
			'condStr'=>''
		);
		$vars['valuesParse'] = self::values_parse($values, array('spacer'=>', ', 'advanced'=>false));
		$vars['condParse'] = self::values_parse($conditions, array('spacer'=>' AND ', 'advanced'=>false));
		$vars['condStr'] = "WHERE (" . $vars['condParse']['key?:keyStr'] . ")";
		$sql = "INSERT INTO ". $table ." (" . implode(',', array_keys($values)) . ") VALUES (" . $vars['valuesParse'][':keyStr'] . ") " . (($conditions != null)? $vars['condStr']: "");
		echo $sql;
		/*$sql_prep = $this->connect->prepare($sql);
		$sql_prep = $this->bind_value($sql, $vars['valuesParse']['params'], $vars['condParse']['params']);
		return $sql_prep->execute();*/
	}
	public function update($table, $values, $conditions = null){
		$vars = array(
			'valuesParse'=>array(),
			'condParse'=>array(),
			'condStr'=>''
		);
		$vars['valuesParse'] = self::values_parse($values);
		$vars['condParse'] = self::values_parse($conditions,
			array(
				'spacer'=>' AND '
			)
		);
		out($vars);
		$vars['condStr'] = "WHERE (" . $vars['condParse']['key?:keyStr'] . ")";
		$sql = "UPDATE " . $table . " SET " . $vars['valuesParse']['key?:keyStr'] . " " . (($conditions != null)? $vars['condStr']: "");
		echo $sql;
		/*$sql_prep = $this->bind_value($sql, $vars['valuesParse']['params'], $vars['condParse']['params']);
		$sql_prep->execute();
		return ($sql_prep->rowCount()) ? true : false;*/
	}
	public function remove($table, $conditions = null,  $optional=array()){
		$args = array(
			'advanced'=>(isset($optional['advanced']))? $optional['advanced'] : false
		);
		$vars = array(
			'condParse'=>array(),
			'condStr'=>''
		);
		$vars['condParse'] = self::values_parse($conditions, 
			array(
				'spacer'=>' AND ',
				'advanced'=>$args['advanced']
			)
		);
		$vars['condStr'] = " WHERE (" . $vars['condParse']['key?:keyStr'] . ")";
		$sql = "DELETE FROM ". $table . (( $conditions != null)? $vars['condStr']: "");
		echo $sql;
		/*$sql_prep = $this->bind_value($sql, $vars['condParse']['params']);
		return $sql_prep->execute();
		*/
		
	}
	public function getall($table, $values, $conditions = null, $optional=array()){
		$args = array(
			'limit'=>(isset($optional['limit']))? $optional['limit'] : null,
			'ascdesc'=>(isset($optional['ascdesc']))? $optional['ascdesc'] : null,
			'advanced'=>(isset($optional['advanced']))? $optional['advanced'] : false
		);
		$vars = array(
			'valuesParse'=>array(),
			'condParse'=>array(),
			'condStr'=>''
		);

		$vars['condParse'] = self::values_parse($conditions, 
			array(
				'spacer'=>' AND ',
				'advanced'=>$args['advanced']
			)
		);
		$vars['valuesParse'] = self::values_parse($values, 
			array(
				'advanced'=>false
			)
		);
		$vars['condStr'] = "WHERE (" . $vars['condParse']['key?:keyStr'] . ")";
		$sql = "SELECT " . implode(',', $values) . " FROM " . $table . " " . (($conditions != null)? $vars['condStr']: "") . (($args['ascdesc'] != null)? " ORDER BY " . $args['ascdesc'][0] . " " . $args['ascdesc'][1] : "") . (($args['limit'] != null)? " LIMIT " . $args['limit']: "");
		echo $sql;
		$sql_prep = $this->connect->prepare($sql);
		foreach ($vars['condParse']['params'] as $key => $value) {
			$sql_prep->bindValue($key, $value, $this->bind_type($value));
		}

		$sql_prep->execute();
		return $result = $sql_prep->fetchAll(PDO::FETCH_ASSOC);
	}
	public function client_ip(){
		if (getenv('HTTP_CLIENT_IP'))
			return getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			return getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			return getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			return getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			return getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			return getenv('REMOTE_ADDR');
		return false;
	}

}
$db = new database_(0x0);
$db->sql_builder();



function out($v){
	echo "<pre>";
	print_r($v);
	echo "</pre>";
}
?>