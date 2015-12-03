<?PHP
/*
	Developer: Richard Grant
	Released to Infinisys, inc. on 04.14.2015
*/
require_once("htaccess.php");

class globals_ extends htaccess_{
	function __contruct(){
		parent::__construct();
	}
	function GET_DB_accesspoints(){
		$points = Array(
			"_portal"=>Array(
				"host_1" => $this->env_comb("host", 1),
				"database_1"=> $this->env_comb("database", 1),
				"credentials" => Array(
					"fullaccess"=> Array(
						"user_1"=> $this->env_comb("user_fullaccess", 1),
						"password_1"=> $this->env_comb("password_fullaccess", 1)
					),
					"readonly"=> Array(
						"user_1"=> $this->env_comb("user_readonly", 1),
						"password_1"=> $this->env_comb("password_readonly", 1)
					)
				)
			)
		);
		return $points;
	}
	private function env_comb($type, $num){
		return $this->getenv_("_portal_" . $type . "_" . $num);
	}
}
?>