<?php
$xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string($xmldata);
header("Content-Type:text/xml;charset=UTF-8");

class Login{
	private $name;
	private $password;
	private $id;
	private $result;
	public $xml;
	
	function __construct($data) {
		$this->name = $data['name'];
		$this->password = $data['password'];
	}
	
	private function search_user() {
		$sql = "SELECT `id` FROM `users_infomation` WHERE `name` = '$this->name' AND `password` = '$this->password'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$this->id = $row['id'];
		}
		if (!$this->id) {
			$this->result = "user_name or password wrong!";
			$this->id = 0;
		} else {
			$this->result = "landing success!";
		}
	}
	
	private function create_xml() {
		$this->xml = "<?xml version='1.0' encoding='UTF-8'?><root>";
		$this->xml .= "<result>$this->result</result>";
		$this->xml .= "<id>$this->id</id>";
		$this->xml .= "</root>";
	}
	
	public function work() {
		$this->search_user();
		$this->create_xml();
	}
}

function main($data) {
	$login = new Login($data);
	$login->work();
	echo $login->xml;
}

main($data);
