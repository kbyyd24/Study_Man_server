<?php
include_once '../tools/DBconnecter.php';

// $xmldata = file_get_contents('php://input');
$data = (array)simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?><root><name>kbyyd24</name><password>123456</password></root>");
// header("Content-Type:text/xml;charset=UTF-8");

class Signin{
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
		$sql = "SELECT `name` FROM `users_infomation` WHERE `name`='$this->name'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$data = $row['name'];
		}
		if (!$data) {
			$this->save_user();
		} else {
			$this->result = "This account is existed!";
			$this->id = 0;
		}
	}
	
	private function save_user() {
		$sql = "INSERT INTO `users_infomation` (`name`, `password`) values('$this->name', '$this->password')";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$conn->exec($sql);
		$sql = "SELECT `id` FROM `users_infomation` WHERE `name`='$this->name'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$this->id = $row['id'];
		}
		$this->result = "sign up success!";
	}
	
	private function judge_base() {
		$sql = "SELECT `userId` FROM `users_information` WHERE `name`='$this->name'";
		$conn = new PDO(DBconnecter::HOST, DBconnecter::USER, DBconnecter::PASSWORD);
		$DBdata = $conn->query($sql);
		while($row = $DBdata->fetch()){
			$id = $row['userId'];
		}
		if (empty($id)) {
			$this->result = "register fail";
		} else {
			$this->result = "register success";
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
		$this->judge_base();
		$this->create_xml();
	}
}

function main($data) {
	$sign = new Signin($data);
	$sign->work();
	echo $sign->xml;
}
main($data);
