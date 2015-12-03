<?PHP
/*
	Developer: Richard Grant
	Code-genius.com
*/
require_once("globals.php");

class database_ extends globals_{
	public $connect = null;
	
	function __construct($norestriction){
		parent::__construct();
		$account =array();
		if($norestriction){
			$account[0] = $this->GET_DB_accesspoints()["_portal"]["credentials"]["fullaccess"]["user_1"];
			$account[1] = $this->GET_DB_accesspoints()["_portal"]["credentials"]["fullaccess"]["password_1"]; 
		}else{
			$account[0] = $this->GET_DB_accesspoints()["_portal"]["credentials"]["readonly"]["user_1"];
			$account[1] = $this->GET_DB_accesspoints()["_portal"]["credentials"]["readonly"]["password_1"]; 
		}
		$this->connect = new PDO('mysql:host=' .  $this->GET_DB_accesspoints()["_portal"]['host_1'] . ';dbname=' . $this->GET_DB_accesspoints()["_portal"]["database_1"], $account[0], $account[1]);
	}
	public function insert($table, $values, $conditions = null){
		$params_str = "";
		$values_str = "";
		$params = array();
		foreach($values as $key => $value){
			$values_str .= '`' . $key . "`, ";
			$params_str .= ":" . $key . ", ";
			$params[":" . $key] = $value;
		}
		$cond_str = "";
		$hascond = false;
		if($conditions != null){
			$hascond = true;
			foreach($conditions as $key => $value){
				$cond_str .= $key . "='" . $value . "' AND ";
			}
			$cond_str = rtrim($cond_str, " AND ");
		}

		$params_str = rtrim($params_str, ", ");
		$values_str = rtrim($values_str, ", ");
		$cond_str = "WHERE (" . $cond_str . ")";
		$sql = "INSERT INTO ". $table ." (" . $values_str . ") VALUES (" . $params_str . ") " . (($hascond)? $cond_str: "");
		$sql_prep = $this->connect->prepare($sql);
		foreach ($params as $key => $value) {
			$sql_prep->bindValue($key, $value, PDO::PARAM_STR);
		}
		return $sql_prep->execute();
	}
	public function update($table, $values, $conditions = null){
		$values_str = "";
		$params = array();
		foreach($values as $key => $value){
			$values_str .= "`" . $key . "`= :" . $key .  ", ";
			$params[":" . $key] = $value;
		}
		$cond_str = "";
		$hascond = false;
		if($conditions != null){
			$hascond = true;
			foreach($conditions as $key => $value){
				$cond_str .= "`" . $key . "`='" . $value . "' AND ";
			}
			$cond_str = rtrim($cond_str, " AND ");
		}
		$values_str = rtrim($values_str, ", ");
		$cond_str = "WHERE (" . $cond_str . ")";
		$sql = "UPDATE " . $table . " SET " . $values_str . " " . (($hascond)? $cond_str: "");
		$sql_prep = $this->connect->prepare($sql);
		foreach ($params as $key => $value) {
			$sql_prep->bindValue($key, $value, PDO::PARAM_STR);
		}
		$sql_prep->execute();
		return ($sql_prep->rowCount()) ? true : false;
	}
	public function remove($table, $conditions = null){
		$cond_str = "";
		$params = array();
		$hascond = false;
		if($conditions != null){
			$hascond = true;
			foreach($conditions as $key => $value){
				$cond_str .= "`" . $key . "`= :" . $key .  " AND ";
				$params[":" . $key] = $value;
			}
			$cond_str = rtrim($cond_str, " AND ");
		}
		$cond_str = " WHERE (" . $cond_str . ")";
		$sql = "DELETE FROM ". $table . (($hascond)? $cond_str: "");
		$sql_prep = $this->connect->prepare($sql);
		foreach ($params as $key => $value) {
			$sql_prep->bindValue($key, $value, PDO::PARAM_STR);
		}
		return $sql_prep->execute();
	}


	public function getall($table, $values, $conditions = null, $limit = null, $ascdesc = null){
		$values_str = "";
		foreach($values as $key => $value){
			$values_str .= $value . ", ";
		}
		$cond_str = "";
		$params = array();
		$hascond = false;
		if($conditions != null){
			$hascond = true;
			foreach($conditions as $key => $value){
				//$cond_str .= $key . "='" . $value . "' AND ";
				$cond_str .= "`" . $key . "`= :" . $key .  " AND ";
				$params[":" . $key] = $value;
			}
			$cond_str = rtrim($cond_str, " AND ");
		}
		$values_str = rtrim($values_str, ", ");
		$cond_str = " WHERE (" . $cond_str . ")";


		$orderby = "";
		$hasorder = false;
		if($ascdesc != null){
			$hasorder = true;
			foreach($ascdesc as $key => $value){
				$orderby = " ORDER BY " . $value . " " . $key;
				break;
			}
		}


		$sql = "SELECT " . $values_str . " FROM " . $table . " " . (($hascond)? $cond_str: "") . (($hasorder)? $orderby: "") . (($limit)? " LIMIT " . $limit: "");
		//echo $sql;
		$sql_prep = $this->connect->prepare($sql);
		foreach ($params as $key => $value) {
			$sql_prep->bindValue($key, $value, PDO::PARAM_STR);
		}
		$sql_prep->execute();
		return $result = $sql_prep->fetchAll(PDO::FETCH_ASSOC);

	}
	public function getall_advanced($table, $values, $conditions = null, $limit = null, $ascdesc = null){
		$values_str = "";
		foreach($values as $key => $value){
			$values_str .= $value . ", ";
		}
		$cond_str = "";
		$params = array();
		$hascond = false;
		if($conditions != null){
			$hascond = true;
			foreach($conditions as $key => $value){
				//$cond_str .= $value[0] . "" . $value[1] . "'" . $value[2] . "' AND ";
				if(strtolower($value[1]) == 'between'){
					$cond_str .= "`" . $value[0] . "` " . $value[1] . " :b1_" . $value[0] .  " AND" . " :b2_" . $value[0] .  " AND ";
					$params[":b1_" . $value[0] ] = $value[2];
					$params[":b2_" . $value[0] ] = $value[3];
				}else{
					$cond_str .= "`" . $value[0] . "` " . $value[1] . " :" . $value[0] .  " AND ";
					$params[":" . $value[0] ] = $value[2];
				}
			}
			$cond_str = rtrim($cond_str, " AND ");
		}
		$values_str = rtrim($values_str, ", ");
		$cond_str = "WHERE (" . $cond_str . ")";


		$orderby = "";
		$hasorder = false;
		if($ascdesc != null){
			$hasorder = true;
			foreach($ascdesc as $key => $value){
				$orderby = " ORDER BY " . $value . " " . $key;
				break;
			}
		}


		$sql = "SELECT " . $values_str . " FROM " . $table . " " . (($hascond)? $cond_str: "") . (($hasorder)? $orderby: "") . (($limit)? " LIMIT " . $limit: "");
		//echo $sql;
		$sql_prep = $this->connect->prepare($sql);
		foreach ($params as $key => $value) {
			$sql_prep->bindValue($key, $value, PDO::PARAM_STR);
		}
		$sql_prep->execute();
		return $result = $sql_prep->fetchAll(PDO::FETCH_ASSOC);

	}
	public function truncate($table){
		$sql = "Truncate table " . $table;
		$sql_prep = $this->connect->prepare($sql);
		return $sql_prep->execute();
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
?>